<?php

namespace App\Services;

use App\Models\Prescription;
use App\Models\Prescriber;
use App\Models\User;
use App\Models\Product;
use App\Models\PrescriptionAuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrescriptionVerificationService
{
    /**
     * Verify a prescription comprehensively.
     */
    public function verifyPrescription(Prescription $prescription): array
    {
        $issues = [];
        $warnings = [];
        $checks = [];

        try {
            // 1. Prescriber verification
            $prescriberCheck = $this->verifyPrescriber($prescription);
            $checks['prescriber'] = $prescriberCheck;
            if (!$prescriberCheck['valid']) {
                $issues = array_merge($issues, $prescriberCheck['issues']);
            }

            // 2. Patient verification
            $patientCheck = $this->verifyPatient($prescription);
            $checks['patient'] = $patientCheck;
            if (!$patientCheck['valid']) {
                $issues = array_merge($issues, $patientCheck['issues']);
            }

            // 3. Medication verification
            $medicationCheck = $this->verifyMedication($prescription);
            $checks['medication'] = $medicationCheck;
            if (!$medicationCheck['valid']) {
                $issues = array_merge($issues, $medicationCheck['issues']);
            }
            if (!empty($medicationCheck['warnings'])) {
                $warnings = array_merge($warnings, $medicationCheck['warnings']);
            }

            // 4. Controlled substance verification
            if ($prescription->isControlledSubstance()) {
                $controlledCheck = $this->verifyControlledSubstance($prescription);
                $checks['controlled_substance'] = $controlledCheck;
                if (!$controlledCheck['valid']) {
                    $issues = array_merge($issues, $controlledCheck['issues']);
                }
            }

            // 5. Prescription validity checks
            $validityCheck = $this->verifyPrescriptionValidity($prescription);
            $checks['validity'] = $validityCheck;
            if (!$validityCheck['valid']) {
                $issues = array_merge($issues, $validityCheck['issues']);
            }

            // 6. Drug interaction checks
            $interactionCheck = $this->checkDrugInteractions($prescription);
            $checks['interactions'] = $interactionCheck;
            if (!empty($interactionCheck['warnings'])) {
                $warnings = array_merge($warnings, $interactionCheck['warnings']);
            }

            // 7. Allergy checks
            $allergyCheck = $this->checkAllergies($prescription);
            $checks['allergies'] = $allergyCheck;
            if (!$allergyCheck['valid']) {
                $issues = array_merge($issues, $allergyCheck['issues']);
            }

            // 8. Duplicate therapy checks
            $duplicateCheck = $this->checkDuplicateTherapy($prescription);
            $checks['duplicate_therapy'] = $duplicateCheck;
            if (!empty($duplicateCheck['warnings'])) {
                $warnings = array_merge($warnings, $duplicateCheck['warnings']);
            }

            // Store verification results
            $prescription->update([
                'compliance_checks' => $checks,
                'alerts' => array_merge($issues, $warnings),
            ]);

            return [
                'success' => empty($issues),
                'issues' => $issues,
                'warnings' => $warnings,
                'checks' => $checks,
            ];

        } catch (\Exception $e) {
            Log::error('Prescription verification failed', [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'issues' => ['System error during verification'],
                'warnings' => [],
                'checks' => [],
            ];
        }
    }

    /**
     * Verify prescriber credentials and authorization.
     */
    public function verifyPrescriber(Prescription $prescription): array
    {
        $prescriber = $prescription->prescriber;
        $issues = [];
        $checks = [];

        if (!$prescriber) {
            return [
                'valid' => false,
                'issues' => ['Prescriber not found'],
                'checks' => [],
            ];
        }

        // Check if prescriber is active and verified
        $checks['is_active'] = $prescriber->is_active;
        $checks['verification_status'] = $prescriber->verification_status;
        $checks['can_prescribe'] = $prescriber->canPrescribe();

        if (!$prescriber->is_active) {
            $issues[] = 'Prescriber is not active';
        }

        if (!$prescriber->isVerified()) {
            $issues[] = 'Prescriber is not verified';
        }

        if (!$prescriber->canPrescribe()) {
            $issues[] = 'Prescriber is not authorized to prescribe';
        }

        // Verify NPI number
        $npiCheck = $this->validateNpiNumber($prescriber->npi_number);
        $checks['npi_valid'] = $npiCheck;
        if (!$npiCheck) {
            $issues[] = 'Invalid NPI number';
        }

        // Check license expiry
        $licenseValid = $prescriber->hasValidLicense();
        $checks['license_valid'] = $licenseValid;
        if (!$licenseValid) {
            $issues[] = 'Prescriber license has expired or is invalid';
        }

        // For controlled substances, verify DEA registration
        if ($prescription->isControlledSubstance()) {
            $deaValid = $prescriber->hasValidDea();
            $checks['dea_valid'] = $deaValid;
            
            if (!$deaValid) {
                $issues[] = 'Invalid or expired DEA registration for controlled substances';
            } else {
                // Verify DEA number format
                $deaFormatValid = $this->validateDeaNumber($prescriber->dea_number);
                $checks['dea_format_valid'] = $deaFormatValid;
                if (!$deaFormatValid) {
                    $issues[] = 'Invalid DEA number format';
                }

                // Check DEA schedule authorization
                $scheduleAuth = $prescriber->canPrescribeSchedule($prescription->controlled_substance_schedule);
                $checks['schedule_authorized'] = $scheduleAuth;
                if (!$scheduleAuth) {
                    $issues[] = "Prescriber not authorized for Schedule {$prescription->controlled_substance_schedule} substances";
                }
            }
        }

        // Check for compliance flags
        if ($prescriber->compliance_flags && count($prescriber->compliance_flags) > 0) {
            $checks['compliance_flags'] = $prescriber->compliance_flags;
            $issues[] = 'Prescriber has compliance flags requiring review';
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'checks' => $checks,
        ];
    }

    /**
     * Verify patient information.
     */
    public function verifyPatient(Prescription $prescription): array
    {
        $patient = $prescription->patient;
        $issues = [];
        $checks = [];

        if (!$patient) {
            return [
                'valid' => false,
                'issues' => ['Patient not found'],
                'checks' => [],
            ];
        }

        // Check patient status
        $checks['is_active'] = $patient->is_active;
        if (!$patient->is_active) {
            $issues[] = 'Patient account is not active';
        }

        // Verify patient information matches prescription
        $checks['name_match'] = $patient->name === $prescription->patient_name;
        if ($patient->name !== $prescription->patient_name) {
            $issues[] = 'Patient name does not match prescription';
        }

        $checks['dob_match'] = $patient->date_of_birth?->isSameDay($prescription->patient_dob);
        if ($patient->date_of_birth && !$patient->date_of_birth->isSameDay($prescription->patient_dob)) {
            $issues[] = 'Patient date of birth does not match prescription';
        }

        // Check for allergies if available
        if ($patient->profile && $patient->profile->allergies) {
            $checks['has_allergies'] = true;
            $checks['allergies'] = $patient->profile->allergies;
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'checks' => $checks,
        ];
    }

    /**
     * Verify medication information.
     */
    public function verifyMedication(Prescription $prescription): array
    {
        $issues = [];
        $warnings = [];
        $checks = [];

        // Check if medication exists in product catalog
        if ($prescription->product_id) {
            $product = $prescription->product;
            $checks['product_found'] = !!$product;
            
            if ($product) {
                $checks['product_active'] = $product->is_active;
                if (!$product->is_active) {
                    $issues[] = 'Medication is not available';
                }

                // Verify NDC number if provided
                if ($prescription->ndc_number && $product->ndc_number !== $prescription->ndc_number) {
                    $warnings[] = 'NDC number mismatch between prescription and product catalog';
                }

                // Check strength matching
                if ($prescription->strength !== $product->strength) {
                    $warnings[] = 'Strength mismatch between prescription and product catalog';
                }
            }
        } else {
            $checks['product_found'] = false;
            $warnings[] = 'Medication not found in product catalog - manual verification required';
        }

        // Validate dosage and quantity
        if ($prescription->quantity_prescribed <= 0) {
            $issues[] = 'Invalid quantity prescribed';
        }

        if ($prescription->days_supply <= 0) {
            $issues[] = 'Invalid days supply';
        }

        // Check for reasonable dosing
        $reasonableQuantity = $this->validateReasonableQuantity($prescription);
        $checks['reasonable_quantity'] = $reasonableQuantity;
        if (!$reasonableQuantity) {
            $warnings[] = 'Unusual quantity prescribed - review recommended';
        }

        // Check directions for use
        $checks['has_directions'] = !empty($prescription->directions_for_use);
        if (empty($prescription->directions_for_use)) {
            $issues[] = 'Directions for use are required';
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'warnings' => $warnings,
            'checks' => $checks,
        ];
    }

    /**
     * Verify controlled substance specific requirements.
     */
    public function verifyControlledSubstance(Prescription $prescription): array
    {
        $issues = [];
        $checks = [];

        $schedule = $prescription->controlled_substance_schedule;
        $checks['schedule'] = $schedule;

        // Schedule II substances have additional requirements
        if ($schedule === Prescription::SCHEDULE_II) {
            // No refills allowed for Schedule II
            if ($prescription->refills_authorized > 0) {
                $issues[] = 'Schedule II substances cannot have refills';
            }

            // Require DEA form number for some Schedule II substances
            if (empty($prescription->dea_form_number) && $this->requiresDeaForm($prescription)) {
                $issues[] = 'DEA form number required for this Schedule II substance';
            }

            // 90-day supply limit for Schedule II
            if ($prescription->days_supply > 90) {
                $issues[] = 'Schedule II substances limited to 90-day supply';
            }
        }

        // Check refill limits for other schedules
        $maxRefills = $this->getMaxRefillsForSchedule($schedule);
        $checks['max_refills_allowed'] = $maxRefills;
        
        if ($prescription->refills_authorized > $maxRefills) {
            $issues[] = "Schedule {$schedule} substances limited to {$maxRefills} refills";
        }

        // Check prescription age for controlled substances
        $prescriptionAge = now()->diffInDays($prescription->date_written);
        $maxAge = $this->getMaxAgeForSchedule($schedule);
        $checks['prescription_age_days'] = $prescriptionAge;
        $checks['max_age_allowed'] = $maxAge;
        
        if ($prescriptionAge > $maxAge) {
            $issues[] = "Prescription is too old for Schedule {$schedule} substances (max {$maxAge} days)";
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'checks' => $checks,
        ];
    }

    /**
     * Verify prescription validity and expiration.
     */
    public function verifyPrescriptionValidity(Prescription $prescription): array
    {
        $issues = [];
        $checks = [];

        // Check if prescription has expired
        $isExpired = $prescription->isExpired();
        $checks['is_expired'] = $isExpired;
        
        if ($isExpired) {
            $issues[] = 'Prescription has expired';
        }

        // Check date written is not in the future
        $dateWritten = $prescription->date_written;
        $checks['date_written_valid'] = $dateWritten->isPast() || $dateWritten->isToday();
        
        if ($dateWritten->isFuture()) {
            $issues[] = 'Prescription date cannot be in the future';
        }

        // Check if prescription is too old
        $daysOld = now()->diffInDays($dateWritten);
        $maxAge = $prescription->isControlledSubstance() ? 
            $this->getMaxAgeForSchedule($prescription->controlled_substance_schedule) : 365;
        
        $checks['days_old'] = $daysOld;
        $checks['max_age'] = $maxAge;
        
        if ($daysOld > $maxAge) {
            $issues[] = "Prescription is too old ({$daysOld} days, max {$maxAge} days)";
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'checks' => $checks,
        ];
    }

    /**
     * Check for drug interactions.
     */
    public function checkDrugInteractions(Prescription $prescription): array
    {
        $warnings = [];
        $checks = [];

        try {
            // Get patient's current medications
            $currentMedications = $this->getPatientCurrentMedications($prescription->patient_id);
            $checks['current_medications_count'] = count($currentMedications);

            if (count($currentMedications) > 0) {
                // Check interactions with current medications
                $interactions = $this->findDrugInteractions($prescription->medication_name, $currentMedications);
                $checks['interactions_found'] = count($interactions);
                
                foreach ($interactions as $interaction) {
                    $warnings[] = "Drug interaction: {$interaction['description']} (Severity: {$interaction['severity']})";
                }
            }

            // Check for duplicate therapy
            $duplicates = $this->findDuplicateTherapy($prescription, $currentMedications);
            $checks['duplicate_therapy_found'] = count($duplicates);
            
            foreach ($duplicates as $duplicate) {
                $warnings[] = "Duplicate therapy: {$duplicate}";
            }

        } catch (\Exception $e) {
            Log::error('Drug interaction check failed', [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage(),
            ]);
            
            $warnings[] = 'Unable to verify drug interactions - manual review required';
        }

        return [
            'warnings' => $warnings,
            'checks' => $checks,
        ];
    }

    /**
     * Check for patient allergies.
     */
    public function checkAllergies(Prescription $prescription): array
    {
        $issues = [];
        $checks = [];

        try {
            $patient = $prescription->patient;
            if ($patient->profile && $patient->profile->allergies) {
                $allergies = $patient->profile->allergies;
                $checks['patient_allergies'] = $allergies;

                // Check if prescribed medication matches any allergies
                foreach ($allergies as $allergy) {
                    if ($this->medicationMatchesAllergy($prescription->medication_name, $allergy)) {
                        $issues[] = "Patient allergic to {$allergy['allergen']} - verify medication safety";
                    }
                }
            } else {
                $checks['patient_allergies'] = 'No allergies on file';
            }

        } catch (\Exception $e) {
            Log::error('Allergy check failed', [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage(),
            ]);
            
            $issues[] = 'Unable to verify allergies - manual review required';
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'checks' => $checks,
        ];
    }

    /**
     * Check for duplicate therapy.
     */
    public function checkDuplicateTherapy(Prescription $prescription): array
    {
        $warnings = [];
        $checks = [];

        try {
            $currentMedications = $this->getPatientCurrentMedications($prescription->patient_id);
            
            $duplicates = $this->findDuplicateTherapy($prescription, $currentMedications);
            $checks['duplicate_medications'] = $duplicates;

            foreach ($duplicates as $duplicate) {
                $warnings[] = "Possible duplicate therapy: {$duplicate}";
            }

        } catch (\Exception $e) {
            Log::error('Duplicate therapy check failed', [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'warnings' => $warnings,
            'checks' => $checks,
        ];
    }

    /**
     * Auto-verify e-script prescriptions.
     */
    public function autoVerifyEScript(Prescription $prescription): bool
    {
        try {
            // E-scripts have digital signatures and are pre-validated
            $verificationResult = $this->verifyPrescription($prescription);

            // Only auto-verify if no critical issues found
            if ($verificationResult['success'] && empty($verificationResult['issues'])) {
                $prescription->completeReviewAsVerified('Auto-verified e-script');
                
                PrescriptionAuditLog::logVerified($prescription, 'Auto-verified e-script');
                
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('E-script auto-verification failed', [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Validate NPI number using Luhn algorithm.
     */
    private function validateNpiNumber(string $npi): bool
    {
        if (!preg_match('/^\d{10}$/', $npi)) {
            return false;
        }

        $sum = 0;
        $alternate = false;

        for ($i = strlen($npi) - 1; $i >= 0; $i--) {
            $digit = intval($npi[$i]);

            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit = ($digit % 10) + 1;
                }
            }

            $sum += $digit;
            $alternate = !$alternate;
        }

        return ($sum % 10) === 0;
    }

    /**
     * Validate DEA number format and checksum.
     */
    private function validateDeaNumber(string $dea): bool
    {
        if (!preg_match('/^[A-Z]{2}\d{7}$/', $dea)) {
            return false;
        }

        $firstLetter = substr($dea, 0, 1);
        if (!in_array($firstLetter, ['A', 'B', 'F', 'M'])) {
            return false;
        }

        $numbers = substr($dea, 2, 7);
        $sum1 = intval($numbers[0]) + intval($numbers[2]) + intval($numbers[4]);
        $sum2 = intval($numbers[1]) + intval($numbers[3]) + intval($numbers[5]);
        $checksum = ($sum1 + 2 * $sum2) % 10;

        return $checksum === intval($numbers[6]);
    }

    /**
     * Validate if quantity is reasonable for the medication.
     */
    private function validateReasonableQuantity(Prescription $prescription): bool
    {
        $quantity = $prescription->quantity_prescribed;
        $daysSupply = $prescription->days_supply;

        // Check for unusually large quantities
        if ($quantity > 1000) {
            return false;
        }

        // Check for unusually long supply
        if ($daysSupply > 365) {
            return false;
        }

        // Calculate daily dose
        $dailyDose = $quantity / $daysSupply;
        
        // Check for unusually high daily doses
        if ($dailyDose > 50) {
            return false;
        }

        return true;
    }

    /**
     * Check if DEA form is required for this medication.
     */
    private function requiresDeaForm(Prescription $prescription): bool
    {
        // Implementation would check against specific medications that require DEA forms
        // For now, return false as most Schedule II substances don't require special forms
        return false;
    }

    /**
     * Get maximum allowed refills for controlled substance schedule.
     */
    private function getMaxRefillsForSchedule(string $schedule): int
    {
        return match ($schedule) {
            Prescription::SCHEDULE_I => 0,  // No prescriptions allowed
            Prescription::SCHEDULE_II => 0, // No refills
            Prescription::SCHEDULE_III, Prescription::SCHEDULE_IV => 5,
            Prescription::SCHEDULE_V => 11, // Some states allow more
            default => 11, // Non-controlled substances
        };
    }

    /**
     * Get maximum age in days for controlled substance schedule.
     */
    private function getMaxAgeForSchedule(string $schedule): int
    {
        return match ($schedule) {
            Prescription::SCHEDULE_II => 30,   // 30 days
            Prescription::SCHEDULE_III, Prescription::SCHEDULE_IV => 180, // 6 months
            Prescription::SCHEDULE_V => 365,   // 1 year
            default => 365, // Non-controlled substances
        };
    }

    /**
     * Get patient's current active medications.
     */
    private function getPatientCurrentMedications(int $patientId): array
    {
        $prescriptions = Prescription::where('patient_id', $patientId)
            ->where('processing_status', Prescription::PROCESSING_DISPENSED)
            ->where('date_dispensed', '>=', now()->subDays(90))
            ->whereHas('refills', '>', 0)
            ->get(['medication_name', 'generic_name', 'ndc_number']);

        return $prescriptions->map(function ($prescription) {
            return [
                'name' => $prescription->medication_name,
                'generic' => $prescription->generic_name,
                'ndc' => $prescription->ndc_number,
            ];
        })->toArray();
    }

    /**
     * Find drug interactions using external API or internal database.
     */
    private function findDrugInteractions(string $newMedication, array $currentMedications): array
    {
        // This would integrate with a drug interaction database
        // For now, return empty array - implement with real drug interaction API
        return [];
    }

    /**
     * Find duplicate therapy based on medication classes.
     */
    private function findDuplicateTherapy(Prescription $prescription, array $currentMedications): array
    {
        $duplicates = [];
        
        foreach ($currentMedications as $medication) {
            if (strtolower($medication['name']) === strtolower($prescription->medication_name)) {
                $duplicates[] = $medication['name'];
            }
        }
        
        return $duplicates;
    }

    /**
     * Check if medication matches patient allergy.
     */
    private function medicationMatchesAllergy(string $medication, array $allergy): bool
    {
        $allergen = strtolower($allergy['allergen'] ?? '');
        $medicationLower = strtolower($medication);
        
        return str_contains($medicationLower, $allergen) || str_contains($allergen, $medicationLower);
    }
}