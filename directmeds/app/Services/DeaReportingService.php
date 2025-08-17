<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\ComplianceReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class DeaReportingService
{
    /**
     * Controlled substance schedules
     */
    public const SCHEDULE_I = 'CI';
    public const SCHEDULE_II = 'CII';
    public const SCHEDULE_III = 'CIII';
    public const SCHEDULE_IV = 'CIV';
    public const SCHEDULE_V = 'CV';

    /**
     * Generate controlled substance report for DEA compliance.
     */
    public function generateControlledSubstanceReport(Carbon $startDate, Carbon $endDate): array
    {
        $controlledSubstanceLogs = AuditLog::controlledSubstance()
            ->withinDateRange($startDate, $endDate)
            ->with(['user'])
            ->get();

        $prescriptions = Prescription::where('is_controlled_substance', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['patient', 'prescriber', 'product'])
            ->get();

        return [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'days' => $startDate->diffInDays($endDate) + 1,
            ],
            'overview' => $this->generateOverviewMetrics($controlledSubstanceLogs, $prescriptions),
            'schedule_analysis' => $this->analyzeBySchedule($prescriptions),
            'dispensing_patterns' => $this->analyzeDispensingPatterns($prescriptions),
            'inventory_tracking' => $this->analyzeInventoryTracking($prescriptions),
            'prescriber_analysis' => $this->analyzePrescriberPatterns($prescriptions),
            'patient_analysis' => $this->analyzePatientPatterns($prescriptions),
            'audit_trail' => $this->analyzeAuditTrail($controlledSubstanceLogs),
            'compliance_violations' => $this->identifyDeaViolations($prescriptions, $controlledSubstanceLogs),
            'recommendations' => $this->generateDeaRecommendations($prescriptions, $controlledSubstanceLogs),
        ];
    }

    /**
     * Generate overview metrics for controlled substances.
     */
    private function generateOverviewMetrics(Collection $auditLogs, Collection $prescriptions): array
    {
        return [
            'total_controlled_prescriptions' => $prescriptions->count(),
            'total_dispensed' => $prescriptions->where('status', 'dispensed')->count(),
            'total_audit_events' => $auditLogs->count(),
            'unique_patients' => $prescriptions->pluck('patient_id')->unique()->count(),
            'unique_prescribers' => $prescriptions->pluck('prescriber_id')->unique()->count(),
            'schedule_breakdown' => $prescriptions->groupBy('controlled_substance_schedule')->map->count(),
            'total_quantity_dispensed' => $prescriptions->where('status', 'dispensed')->sum('quantity_dispensed'),
            'returns_and_reversals' => $prescriptions->where('status', 'returned')->count(),
            'rejected_prescriptions' => $prescriptions->where('status', 'rejected')->count(),
            'prescription_value' => $prescriptions->where('status', 'dispensed')->sum('total_price'),
        ];
    }

    /**
     * Analyze controlled substances by schedule.
     */
    private function analyzeBySchedule(Collection $prescriptions): array
    {
        $scheduleData = [];

        foreach ([self::SCHEDULE_II, self::SCHEDULE_III, self::SCHEDULE_IV, self::SCHEDULE_V] as $schedule) {
            $schedulePrescriptions = $prescriptions->where('controlled_substance_schedule', $schedule);
            
            $scheduleData[$schedule] = [
                'total_prescriptions' => $schedulePrescriptions->count(),
                'dispensed_prescriptions' => $schedulePrescriptions->where('status', 'dispensed')->count(),
                'total_quantity' => $schedulePrescriptions->sum('quantity_dispensed'),
                'unique_patients' => $schedulePrescriptions->pluck('patient_id')->unique()->count(),
                'unique_prescribers' => $schedulePrescriptions->pluck('prescriber_id')->unique()->count(),
                'top_medications' => $schedulePrescriptions->groupBy('medication_name')
                    ->map->count()
                    ->sortByDesc()
                    ->take(5)
                    ->toArray(),
                'average_quantity_per_prescription' => $schedulePrescriptions->avg('quantity_dispensed'),
                'refill_analysis' => [
                    'total_refills' => $schedulePrescriptions->sum('refills_dispensed'),
                    'avg_refills_per_prescription' => $schedulePrescriptions->avg('refills_dispensed'),
                    'max_refills_single_prescription' => $schedulePrescriptions->max('refills_dispensed'),
                ],
            ];
        }

        return $scheduleData;
    }

    /**
     * Analyze dispensing patterns.
     */
    private function analyzeDispensingPatterns(Collection $prescriptions): array
    {
        $dispensedPrescriptions = $prescriptions->where('status', 'dispensed');

        return [
            'daily_dispensing' => $dispensedPrescriptions->groupBy(function ($prescription) {
                return $prescription->dispensed_at?->format('Y-m-d') ?? 'unknown';
            })->map->count()->sortKeys()->toArray(),
            
            'hourly_dispensing' => $dispensedPrescriptions->groupBy(function ($prescription) {
                return $prescription->dispensed_at?->hour ?? 'unknown';
            })->map->count()->sortKeys()->toArray(),
            
            'day_of_week_dispensing' => $dispensedPrescriptions->groupBy(function ($prescription) {
                return $prescription->dispensed_at?->dayOfWeek ?? 'unknown';
            })->map(function ($group, $day) {
                return [
                    'day' => $day !== 'unknown' ? Carbon::create()->dayOfWeek($day)->format('l') : 'Unknown',
                    'count' => $group->count()
                ];
            })->values()->toArray(),
            
            'pharmacist_dispensing' => $dispensedPrescriptions->groupBy('dispensing_pharmacist_id')
                ->map(function ($group, $pharmacistId) {
                    $pharmacist = User::find($pharmacistId);
                    return [
                        'pharmacist_id' => $pharmacistId,
                        'pharmacist_name' => $pharmacist?->name ?? 'Unknown',
                        'prescriptions_dispensed' => $group->count(),
                        'total_quantity_dispensed' => $group->sum('quantity_dispensed'),
                        'schedule_breakdown' => $group->groupBy('controlled_substance_schedule')->map->count(),
                    ];
                })->sortByDesc('prescriptions_dispensed')->values()->toArray(),
            
            'early_refill_patterns' => $this->analyzeEarlyRefills($dispensedPrescriptions),
            'large_quantity_dispensing' => $this->analyzeLargeQuantityDispensing($dispensedPrescriptions),
        ];
    }

    /**
     * Analyze inventory tracking.
     */
    private function analyzeInventoryTracking(Collection $prescriptions): array
    {
        $inventoryData = [];

        // Group by medication for inventory analysis
        $medicationGroups = $prescriptions->where('status', 'dispensed')
            ->groupBy('medication_name');

        foreach ($medicationGroups as $medication => $prescriptionGroup) {
            $inventoryData[$medication] = [
                'total_dispensed_quantity' => $prescriptionGroup->sum('quantity_dispensed'),
                'number_of_dispensings' => $prescriptionGroup->count(),
                'lot_numbers_used' => $prescriptionGroup->pluck('lot_number')->unique()->filter()->count(),
                'ndc_numbers' => $prescriptionGroup->pluck('ndc_dispensed')->unique()->filter()->toArray(),
                'average_dispensing_quantity' => $prescriptionGroup->avg('quantity_dispensed'),
                'controlled_schedule' => $prescriptionGroup->first()?->controlled_substance_schedule,
                'acquisition_cost' => $prescriptionGroup->sum('acquisition_cost'),
                'last_dispensed' => $prescriptionGroup->max('dispensed_at'),
            ];
        }

        return [
            'medication_inventory' => collect($inventoryData)
                ->sortByDesc('total_dispensed_quantity')
                ->take(20)
                ->toArray(),
            'inventory_turnover_analysis' => $this->analyzeInventoryTurnover($medicationGroups),
            'lot_number_tracking' => $this->analyzeLotNumberTracking($prescriptions),
            'wastage_and_returns' => $this->analyzeWastageAndReturns($prescriptions),
        ];
    }

    /**
     * Analyze prescriber patterns.
     */
    private function analyzePrescriberPatterns(Collection $prescriptions): array
    {
        $prescriberAnalysis = $prescriptions->groupBy('prescriber_id')
            ->map(function ($group, $prescriberId) {
                $prescriber = $group->first()?->prescriber;
                return [
                    'prescriber_id' => $prescriberId,
                    'prescriber_name' => $prescriber?->name ?? 'Unknown',
                    'dea_number' => $prescriber?->dea_number,
                    'specialty' => $prescriber?->specialty,
                    'total_controlled_prescriptions' => $group->count(),
                    'dispensed_prescriptions' => $group->where('status', 'dispensed')->count(),
                    'total_quantity_prescribed' => $group->sum('quantity_prescribed'),
                    'schedule_breakdown' => $group->groupBy('controlled_substance_schedule')->map->count(),
                    'top_medications' => $group->groupBy('medication_name')
                        ->map->count()
                        ->sortByDesc()
                        ->take(5)
                        ->toArray(),
                    'unique_patients' => $group->pluck('patient_id')->unique()->count(),
                    'average_quantity_per_prescription' => $group->avg('quantity_prescribed'),
                    'prescribing_frequency' => $this->calculatePrescribingFrequency($group),
                ];
            })->sortByDesc('total_controlled_prescriptions');

        return [
            'top_prescribers' => $prescriberAnalysis->take(20)->values()->toArray(),
            'high_volume_prescribers' => $prescriberAnalysis
                ->where('total_controlled_prescriptions', '>', 100)
                ->values()
                ->toArray(),
            'unusual_prescribing_patterns' => $this->identifyUnusualPrescribingPatterns($prescriberAnalysis),
            'prescriber_compliance_issues' => $this->identifyPrescriberComplianceIssues($prescriptions),
        ];
    }

    /**
     * Analyze patient patterns.
     */
    private function analyzePatientPatterns(Collection $prescriptions): array
    {
        $patientAnalysis = $prescriptions->groupBy('patient_id')
            ->map(function ($group, $patientId) {
                $patient = $group->first()?->patient;
                return [
                    'patient_id' => $patientId,
                    'patient_name' => $patient?->name ?? 'Unknown',
                    'total_controlled_prescriptions' => $group->count(),
                    'dispensed_prescriptions' => $group->where('status', 'dispensed')->count(),
                    'total_quantity_received' => $group->where('status', 'dispensed')->sum('quantity_dispensed'),
                    'schedule_breakdown' => $group->groupBy('controlled_substance_schedule')->map->count(),
                    'medications_received' => $group->pluck('medication_name')->unique()->toArray(),
                    'unique_prescribers' => $group->pluck('prescriber_id')->unique()->count(),
                    'prescription_frequency' => $this->calculatePatientFrequency($group),
                    'early_refill_count' => $this->countEarlyRefills($group),
                    'overlapping_prescriptions' => $this->identifyOverlappingPrescriptions($group),
                ];
            })->sortByDesc('total_controlled_prescriptions');

        return [
            'top_patients' => $patientAnalysis->take(50)->values()->toArray(),
            'high_utilization_patients' => $patientAnalysis
                ->where('total_controlled_prescriptions', '>', 20)
                ->values()
                ->toArray(),
            'multiple_prescriber_patients' => $patientAnalysis
                ->where('unique_prescribers', '>', 3)
                ->sortByDesc('unique_prescribers')
                ->values()
                ->toArray(),
            'early_refill_patients' => $patientAnalysis
                ->where('early_refill_count', '>', 0)
                ->sortByDesc('early_refill_count')
                ->values()
                ->toArray(),
        ];
    }

    /**
     * Analyze audit trail for controlled substances.
     */
    private function analyzeAuditTrail(Collection $auditLogs): array
    {
        return [
            'total_audit_events' => $auditLogs->count(),
            'events_by_type' => $auditLogs->groupBy('event_type')->map->count()->sortByDesc()->toArray(),
            'events_by_user' => $auditLogs->groupBy('user_id')->map(function ($group, $userId) {
                $user = $group->first()?->user;
                return [
                    'user_id' => $userId,
                    'user_name' => $user?->name ?? 'Unknown',
                    'user_type' => $user?->user_type,
                    'event_count' => $group->count(),
                    'event_types' => $group->groupBy('event_type')->map->count()->toArray(),
                ];
            })->sortByDesc('event_count')->take(10)->values()->toArray(),
            'failed_access_attempts' => $auditLogs->where('access_granted', false)->count(),
            'after_hours_activity' => $auditLogs->filter(function ($log) {
                $hour = $log->created_at->hour;
                return $hour < 7 || $hour > 19;
            })->count(),
            'weekend_activity' => $auditLogs->filter(function ($log) {
                return $log->created_at->isWeekend();
            })->count(),
        ];
    }

    /**
     * Identify DEA compliance violations.
     */
    private function identifyDeaViolations(Collection $prescriptions, Collection $auditLogs): array
    {
        $violations = [];

        // Check for missing DEA numbers
        $missingDeaNumbers = $prescriptions->filter(function ($prescription) {
            return empty($prescription->prescriber?->dea_number);
        });

        if ($missingDeaNumbers->count() > 0) {
            $violations[] = [
                'type' => 'missing_dea_numbers',
                'severity' => 'critical',
                'description' => 'Controlled substance prescriptions from prescribers without valid DEA numbers',
                'count' => $missingDeaNumbers->count(),
                'prescriber_ids' => $missingDeaNumbers->pluck('prescriber_id')->unique()->toArray(),
            ];
        }

        // Check for excessive early refills
        $earlyRefills = $prescriptions->filter(function ($prescription) {
            return $this->isEarlyRefill($prescription);
        });

        if ($earlyRefills->count() > ($prescriptions->count() * 0.05)) { // More than 5%
            $violations[] = [
                'type' => 'excessive_early_refills',
                'severity' => 'high',
                'description' => 'Unusually high rate of early refills for controlled substances',
                'count' => $earlyRefills->count(),
                'percentage' => round(($earlyRefills->count() / $prescriptions->count()) * 100, 2),
            ];
        }

        // Check for missing lot number tracking
        $missingLotNumbers = $prescriptions->where('status', 'dispensed')
            ->filter(function ($prescription) {
                return empty($prescription->lot_number);
            });

        if ($missingLotNumbers->count() > 0) {
            $violations[] = [
                'type' => 'missing_lot_numbers',
                'severity' => 'high',
                'description' => 'Dispensed controlled substances without proper lot number tracking',
                'count' => $missingLotNumbers->count(),
            ];
        }

        // Check for unusual dispensing quantities
        $unusualQuantities = $prescriptions->filter(function ($prescription) {
            return $this->isUnusualQuantity($prescription);
        });

        if ($unusualQuantities->count() > 0) {
            $violations[] = [
                'type' => 'unusual_dispensing_quantities',
                'severity' => 'medium',
                'description' => 'Prescriptions with unusually large dispensing quantities',
                'count' => $unusualQuantities->count(),
                'details' => $unusualQuantities->map(function ($prescription) {
                    return [
                        'prescription_id' => $prescription->id,
                        'medication' => $prescription->medication_name,
                        'quantity' => $prescription->quantity_dispensed,
                        'patient_id' => $prescription->patient_id,
                    ];
                })->toArray(),
            ];
        }

        // Check for patients with multiple prescribers
        $multiplePrescriberPatients = $prescriptions->groupBy('patient_id')
            ->filter(function ($group) {
                return $group->pluck('prescriber_id')->unique()->count() > 5;
            });

        if ($multiplePrescriberPatients->count() > 0) {
            $violations[] = [
                'type' => 'multiple_prescriber_patients',
                'severity' => 'medium',
                'description' => 'Patients receiving controlled substances from multiple prescribers',
                'count' => $multiplePrescriberPatients->count(),
                'patient_details' => $multiplePrescriberPatients->map(function ($group, $patientId) {
                    return [
                        'patient_id' => $patientId,
                        'prescriber_count' => $group->pluck('prescriber_id')->unique()->count(),
                        'prescription_count' => $group->count(),
                    ];
                })->values()->toArray(),
            ];
        }

        return $violations;
    }

    /**
     * Generate DEA-specific recommendations.
     */
    private function generateDeaRecommendations(Collection $prescriptions, Collection $auditLogs): array
    {
        $recommendations = [];

        // Audit trail completeness
        $auditCoverage = ($auditLogs->count() / max($prescriptions->count(), 1)) * 100;
        if ($auditCoverage < 95) {
            $recommendations[] = [
                'priority' => 'high',
                'category' => 'audit_trail',
                'title' => 'Incomplete Audit Trail Coverage',
                'description' => "Only {$auditCoverage}% of controlled substance transactions have complete audit trails.",
                'action_items' => [
                    'Review audit logging implementation',
                    'Ensure all controlled substance events are logged',
                    'Implement automated audit trail verification',
                ],
            ];
        }

        // Inventory tracking
        $missingLotNumbers = $prescriptions->where('status', 'dispensed')
            ->filter(function ($prescription) {
                return empty($prescription->lot_number);
            })->count();

        if ($missingLotNumbers > 0) {
            $recommendations[] = [
                'priority' => 'critical',
                'category' => 'inventory_tracking',
                'title' => 'Missing Lot Number Tracking',
                'description' => "{$missingLotNumbers} dispensed controlled substances lack proper lot number tracking.",
                'action_items' => [
                    'Implement mandatory lot number entry for dispensing',
                    'Review inventory management procedures',
                    'Train staff on proper documentation requirements',
                ],
            ];
        }

        // Early refill monitoring
        $earlyRefillRate = ($prescriptions->filter(function ($prescription) {
            return $this->isEarlyRefill($prescription);
        })->count() / max($prescriptions->count(), 1)) * 100;

        if ($earlyRefillRate > 3) {
            $recommendations[] = [
                'priority' => 'medium',
                'category' => 'refill_monitoring',
                'title' => 'High Early Refill Rate',
                'description' => "Early refill rate of {$earlyRefillRate}% exceeds recommended threshold.",
                'action_items' => [
                    'Implement stricter early refill policies',
                    'Review patient counseling procedures',
                    'Consider automated early refill alerts',
                ],
            ];
        }

        // Prescriber validation
        $invalidPrescribers = $prescriptions->filter(function ($prescription) {
            return empty($prescription->prescriber?->dea_number);
        })->pluck('prescriber_id')->unique()->count();

        if ($invalidPrescribers > 0) {
            $recommendations[] = [
                'priority' => 'critical',
                'category' => 'prescriber_validation',
                'title' => 'Invalid Prescriber DEA Numbers',
                'description' => "{$invalidPrescribers} prescribers lack valid DEA numbers for controlled substances.",
                'action_items' => [
                    'Validate all prescriber DEA numbers',
                    'Implement automated DEA number verification',
                    'Block prescriptions from invalid prescribers',
                ],
            ];
        }

        return $recommendations;
    }

    /**
     * Helper methods for analysis
     */

    private function analyzeEarlyRefills(Collection $prescriptions): array
    {
        $earlyRefills = $prescriptions->filter(function ($prescription) {
            return $this->isEarlyRefill($prescription);
        });

        return [
            'total_early_refills' => $earlyRefills->count(),
            'early_refill_rate' => round(($earlyRefills->count() / max($prescriptions->count(), 1)) * 100, 2),
            'patients_with_early_refills' => $earlyRefills->pluck('patient_id')->unique()->count(),
            'medications_with_early_refills' => $earlyRefills->groupBy('medication_name')
                ->map->count()
                ->sortByDesc()
                ->toArray(),
        ];
    }

    private function analyzeLargeQuantityDispensing(Collection $prescriptions): array
    {
        $largeQuantities = $prescriptions->filter(function ($prescription) {
            return $this->isUnusualQuantity($prescription);
        });

        return [
            'large_quantity_dispensings' => $largeQuantities->count(),
            'medications_with_large_quantities' => $largeQuantities->groupBy('medication_name')
                ->map(function ($group) {
                    return [
                        'count' => $group->count(),
                        'max_quantity' => $group->max('quantity_dispensed'),
                        'avg_quantity' => $group->avg('quantity_dispensed'),
                    ];
                })->toArray(),
        ];
    }

    private function analyzeInventoryTurnover(Collection $medicationGroups): array
    {
        $turnoverData = [];

        foreach ($medicationGroups as $medication => $prescriptions) {
            $totalDispensed = $prescriptions->sum('quantity_dispensed');
            $dispensingDays = $prescriptions->first()?->created_at
                ->diffInDays($prescriptions->last()?->created_at ?: now()) ?: 1;
            
            $turnoverData[$medication] = [
                'total_dispensed' => $totalDispensed,
                'dispensing_period_days' => $dispensingDays,
                'daily_average' => round($totalDispensed / $dispensingDays, 2),
                'turnover_rate' => round($totalDispensed / max($dispensingDays, 1), 2),
            ];
        }

        return $turnoverData;
    }

    private function analyzeLotNumberTracking(Collection $prescriptions): array
    {
        $dispensed = $prescriptions->where('status', 'dispensed');
        
        return [
            'total_dispensed' => $dispensed->count(),
            'with_lot_numbers' => $dispensed->whereNotNull('lot_number')->count(),
            'missing_lot_numbers' => $dispensed->whereNull('lot_number')->count(),
            'lot_number_compliance_rate' => round(
                ($dispensed->whereNotNull('lot_number')->count() / max($dispensed->count(), 1)) * 100,
                2
            ),
            'unique_lot_numbers' => $dispensed->pluck('lot_number')->unique()->filter()->count(),
        ];
    }

    private function analyzeWastageAndReturns(Collection $prescriptions): array
    {
        return [
            'returned_prescriptions' => $prescriptions->where('status', 'returned')->count(),
            'wasted_prescriptions' => $prescriptions->where('status', 'wasted')->count(),
            'total_returned_quantity' => $prescriptions->where('status', 'returned')->sum('quantity_dispensed'),
            'return_rate' => round(
                ($prescriptions->where('status', 'returned')->count() / max($prescriptions->count(), 1)) * 100,
                2
            ),
        ];
    }

    private function calculatePrescribingFrequency(Collection $prescriptions): array
    {
        $dates = $prescriptions->pluck('created_at')->map(function ($date) {
            return $date->format('Y-m-d');
        })->unique();

        return [
            'prescribing_days' => $dates->count(),
            'prescriptions_per_day' => round($prescriptions->count() / max($dates->count(), 1), 2),
            'most_active_day' => $prescriptions->groupBy(function ($p) {
                return $p->created_at->format('Y-m-d');
            })->sortByDesc(function ($group) {
                return $group->count();
            })->keys()->first(),
        ];
    }

    private function calculatePatientFrequency(Collection $prescriptions): array
    {
        $dates = $prescriptions->pluck('dispensed_at')->filter()->map(function ($date) {
            return $date->format('Y-m-d');
        })->unique();

        return [
            'active_days' => $dates->count(),
            'prescriptions_per_active_day' => round($prescriptions->count() / max($dates->count(), 1), 2),
            'average_days_between_prescriptions' => $dates->count() > 1 ? 
                round($prescriptions->first()?->dispensed_at?->diffInDays($prescriptions->last()?->dispensed_at) / max($dates->count() - 1, 1), 1) : 0,
        ];
    }

    private function countEarlyRefills(Collection $prescriptions): int
    {
        return $prescriptions->filter(function ($prescription) {
            return $this->isEarlyRefill($prescription);
        })->count();
    }

    private function identifyOverlappingPrescriptions(Collection $prescriptions): int
    {
        // This would require more complex logic to check for overlapping prescription periods
        // For now, return a simple count based on medication overlaps
        $medications = $prescriptions->groupBy('medication_name');
        $overlaps = 0;
        
        foreach ($medications as $prescriptionGroup) {
            if ($prescriptionGroup->count() > 1) {
                // Check for overlapping dispensing dates
                $sorted = $prescriptionGroup->sortBy('dispensed_at');
                // Implementation would check for actual date overlaps
                $overlaps += max(0, $sorted->count() - 1);
            }
        }
        
        return $overlaps;
    }

    private function identifyUnusualPrescribingPatterns(Collection $prescriberAnalysis): array
    {
        return $prescriberAnalysis->filter(function ($prescriber) {
            return $prescriber['total_controlled_prescriptions'] > 200 || // High volume
                   $prescriber['average_quantity_per_prescription'] > 100 || // Large quantities
                   $prescriber['unique_patients'] < 10; // Limited patient base
        })->values()->toArray();
    }

    private function identifyPrescriberComplianceIssues(Collection $prescriptions): array
    {
        $issues = [];

        $prescriberGroups = $prescriptions->groupBy('prescriber_id');

        foreach ($prescriberGroups as $prescriberId => $prescriptionGroup) {
            $prescriber = $prescriptionGroup->first()?->prescriber;
            $issueFlags = [];

            if (empty($prescriber?->dea_number)) {
                $issueFlags[] = 'missing_dea_number';
            }

            if ($prescriptionGroup->filter(function ($p) { return $this->isEarlyRefill($p); })->count() > 5) {
                $issueFlags[] = 'excessive_early_refills';
            }

            if ($prescriptionGroup->avg('quantity_prescribed') > 120) {
                $issueFlags[] = 'high_average_quantities';
            }

            if (count($issueFlags) > 0) {
                $issues[] = [
                    'prescriber_id' => $prescriberId,
                    'prescriber_name' => $prescriber?->name ?? 'Unknown',
                    'dea_number' => $prescriber?->dea_number,
                    'issues' => $issueFlags,
                    'prescription_count' => $prescriptionGroup->count(),
                ];
            }
        }

        return $issues;
    }

    private function isEarlyRefill(Prescription $prescription): bool
    {
        if (!$prescription->last_dispensed_at || !$prescription->days_supply) {
            return false;
        }

        $expectedRefillDate = $prescription->last_dispensed_at->addDays($prescription->days_supply * 0.8); // 80% rule
        return $prescription->dispensed_at && $prescription->dispensed_at->lt($expectedRefillDate);
    }

    private function isUnusualQuantity(Prescription $prescription): bool
    {
        // Define thresholds based on controlled substance schedule
        $thresholds = [
            self::SCHEDULE_II => 90,   // 90-day supply max
            self::SCHEDULE_III => 120,  // 120-day supply max
            self::SCHEDULE_IV => 120,   // 120-day supply max
            self::SCHEDULE_V => 180,    // 180-day supply max
        ];

        $threshold = $thresholds[$prescription->controlled_substance_schedule] ?? 120;
        return $prescription->quantity_dispensed > $threshold;
    }
}