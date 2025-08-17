<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\Prescriber;
use App\Models\PrescriptionAuditLog;
use App\Services\PrescriptionVerificationService;
use App\Services\PrescriptionUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PrescriptionController extends Controller
{
    protected $verificationService;
    protected $uploadService;

    public function __construct(
        PrescriptionVerificationService $verificationService,
        PrescriptionUploadService $uploadService
    ) {
        $this->verificationService = $verificationService;
        $this->uploadService = $uploadService;
        
        // Apply middleware
        $this->middleware('auth');
        $this->middleware('check.user.status');
        $this->middleware('require.hipaa.acknowledgment');
    }

    /**
     * Display a listing of prescriptions.
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $query = Prescription::with([
            'patient:id,name,email',
            'prescriber:id,first_name,last_name,npi_number',
            'product:id,name,brand_name,ndc_number',
            'reviewingPharmacist:id,name',
            'dispensingPharmacist:id,name'
        ]);

        // Filter by user role
        if ($user->isPatient()) {
            $query->where('patient_id', $user->id);
        } elseif ($user->isPharmacist()) {
            // Pharmacists can see all prescriptions
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }

        if ($request->filled('processing_status')) {
            $query->where('processing_status', $request->processing_status);
        }

        if ($request->filled('controlled_substance')) {
            $query->where('is_controlled_substance', $request->boolean('controlled_substance'));
        }

        if ($request->filled('prescriber_id')) {
            $query->where('prescriber_id', $request->prescriber_id);
        }

        if ($request->filled('patient_id') && $user->isPharmacist()) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_written', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_written', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('prescription_number', 'like', "%{$search}%")
                  ->orWhere('medication_name', 'like', "%{$search}%")
                  ->orWhere('patient_name', 'like', "%{$search}%")
                  ->orWhere('prescriber_name', 'like', "%{$search}%");
            });
        }

        // Sort by priority and date
        $query->orderBy('priority_level', 'asc')
              ->orderBy('date_received', 'desc');

        $prescriptions = $query->paginate($request->input('per_page', 15));

        return response()->json($prescriptions);
    }

    /**
     * Store a newly created prescription.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:users,id',
            'prescriber_id' => 'required|exists:prescribers,id',
            'medication_name' => 'required|string|max:255',
            'strength' => 'required|string|max:100',
            'dosage_form' => 'required|string|max:100',
            'quantity_prescribed' => 'required|numeric|min:0.001',
            'quantity_unit' => 'required|string|max:50',
            'days_supply' => 'required|numeric|min:1',
            'directions_for_use' => 'required|string',
            'refills_authorized' => 'required|integer|min:0|max:11',
            'date_written' => 'required|date|before_or_equal:today',
            'controlled_substance_schedule' => ['required', Rule::in(Prescription::getControlledSubstanceSchedules())],
            'upload_method' => 'required|in:upload,fax,escript,phone',
            'uploaded_files' => 'sometimes|array',
            'uploaded_files.*' => 'file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Verify prescriber can prescribe this medication
            $prescriber = Prescriber::findOrFail($request->prescriber_id);
            if (!$prescriber->canPrescribe()) {
                return response()->json(['error' => 'Prescriber is not authorized to prescribe'], 422);
            }

            // Check controlled substance authorization
            $isControlled = $request->controlled_substance_schedule !== Prescription::SCHEDULE_N;
            if ($isControlled && !$prescriber->canPrescribeControlledSubstances()) {
                return response()->json(['error' => 'Prescriber is not authorized to prescribe controlled substances'], 422);
            }

            if ($isControlled && !$prescriber->canPrescribeSchedule($request->controlled_substance_schedule)) {
                return response()->json(['error' => 'Prescriber is not authorized to prescribe this controlled substance schedule'], 422);
            }

            // Handle file uploads
            $uploadedFiles = [];
            if ($request->hasFile('uploaded_files')) {
                $uploadedFiles = $this->uploadService->handlePrescriptionFiles(
                    $request->file('uploaded_files'),
                    $request->patient_id
                );
            }

            // Create prescription
            $prescriptionData = $request->only([
                'patient_id', 'prescriber_id', 'medication_name', 'strength',
                'dosage_form', 'quantity_prescribed', 'quantity_unit', 'days_supply',
                'directions_for_use', 'refills_authorized', 'date_written',
                'controlled_substance_schedule', 'upload_method'
            ]);

            // Set additional data
            $prescriptionData['patient_name'] = $request->user()->name ?? 'Unknown';
            $prescriptionData['patient_dob'] = $request->user()->date_of_birth;
            $prescriptionData['prescriber_name'] = $prescriber->display_name;
            $prescriptionData['prescriber_npi'] = $prescriber->npi_number;
            $prescriptionData['prescriber_dea'] = $prescriber->dea_number;
            $prescriptionData['is_controlled_substance'] = $isControlled;
            $prescriptionData['refills_remaining'] = $request->refills_authorized;
            $prescriptionData['uploaded_files'] = $uploadedFiles;
            $prescriptionData['date_received'] = now();
            $prescriptionData['upload_notes'] = $request->upload_notes;

            // Set priority based on controlled substance and urgency
            $prescriptionData['priority_level'] = $isControlled ? 
                Prescription::PRIORITY_HIGH : 
                Prescription::PRIORITY_NORMAL;

            $prescription = Prescription::create($prescriptionData);

            // Create audit log
            PrescriptionAuditLog::logCreated($prescription, [
                'upload_method' => $request->upload_method,
                'files_count' => count($uploadedFiles),
            ]);

            // Trigger verification process
            if ($request->upload_method === 'escript') {
                // E-scripts can be automatically verified
                $this->verificationService->autoVerifyEScript($prescription);
            }

            DB::commit();

            $prescription->load([
                'patient:id,name,email',
                'prescriber:id,first_name,last_name,npi_number',
                'product:id,name,brand_name'
            ]);

            return response()->json([
                'message' => 'Prescription created successfully',
                'prescription' => $prescription
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create prescription: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified prescription.
     */
    public function show(Prescription $prescription): JsonResponse
    {
        $user = auth()->user();

        // Check authorization
        if ($user->isPatient() && $prescription->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $prescription->load([
            'patient:id,name,email,phone',
            'prescriber:id,first_name,last_name,npi_number,dea_number,practice_name',
            'product:id,name,brand_name,ndc_number',
            'reviewingPharmacist:id,name',
            'dispensingPharmacist:id,name',
            'consultationPharmacist:id,name',
            'originalPrescription:id,prescription_number',
            'refills:id,prescription_number,date_dispensed,quantity_dispensed',
            'auditLogs' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        return response()->json($prescription);
    }

    /**
     * Update the specified prescription.
     */
    public function update(Request $request, Prescription $prescription): JsonResponse
    {
        $user = auth()->user();

        // Only pharmacists can update prescriptions
        if (!$user->isPharmacist()) {
            return response()->json(['error' => 'Only pharmacists can update prescriptions'], 403);
        }

        // Prevent updates to dispensed prescriptions
        if ($prescription->isDispensed()) {
            return response()->json(['error' => 'Cannot update dispensed prescription'], 422);
        }

        $validator = Validator::make($request->all(), [
            'pharmacist_notes' => 'sometimes|string',
            'priority_level' => 'sometimes|integer|min:1|max:5',
            'requires_consultation' => 'sometimes|boolean',
            'legal_notes' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $oldValues = $prescription->toArray();

            $prescription->update($request->only([
                'pharmacist_notes',
                'priority_level',
                'requires_consultation',
                'legal_notes'
            ]));

            // Create audit log
            PrescriptionAuditLog::logUpdated($prescription, $oldValues);

            return response()->json([
                'message' => 'Prescription updated successfully',
                'prescription' => $prescription
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update prescription: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Start prescription review.
     */
    public function startReview(Prescription $prescription): JsonResponse
    {
        $user = auth()->user();

        if (!$user->isPharmacist()) {
            return response()->json(['error' => 'Only pharmacists can review prescriptions'], 403);
        }

        if (!$prescription->isPending()) {
            return response()->json(['error' => 'Prescription is not pending review'], 422);
        }

        try {
            $prescription->startReview($user->id);

            PrescriptionAuditLog::createLog(
                $prescription->id,
                PrescriptionAuditLog::ACTION_REVIEWED,
                "Prescription review started by {$user->name}"
            );

            return response()->json([
                'message' => 'Prescription review started',
                'prescription' => $prescription
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to start review: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Complete prescription verification.
     */
    public function verify(Request $request, Prescription $prescription): JsonResponse
    {
        $user = auth()->user();

        if (!$user->isPharmacist()) {
            return response()->json(['error' => 'Only pharmacists can verify prescriptions'], 403);
        }

        if (!$prescription->isInReview() && !$prescription->isPending()) {
            return response()->json(['error' => 'Prescription is not available for verification'], 422);
        }

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:verify,reject,hold',
            'notes' => 'required_if:action,reject,hold|string',
            'drug_interactions' => 'sometimes|array',
            'allergy_checks' => 'sometimes|array',
            'clinical_reviews' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Run verification checks
            $verificationResult = $this->verificationService->verifyPrescription($prescription);

            if (!$verificationResult['success'] && $request->action === 'verify') {
                return response()->json([
                    'error' => 'Verification failed',
                    'issues' => $verificationResult['issues']
                ], 422);
            }

            // Update prescription based on action
            switch ($request->action) {
                case 'verify':
                    $prescription->completeReviewAsVerified($request->notes);
                    PrescriptionAuditLog::logVerified($prescription, $request->notes);
                    break;

                case 'reject':
                    $prescription->completeReviewAsRejected($request->notes);
                    PrescriptionAuditLog::logRejected($prescription, $request->notes);
                    break;

                case 'hold':
                    $prescription->putOnHold($request->notes);
                    PrescriptionAuditLog::createLog(
                        $prescription->id,
                        PrescriptionAuditLog::ACTION_ON_HOLD,
                        "Prescription put on hold: {$request->notes}"
                    );
                    break;
            }

            // Store additional review data
            if ($request->filled('drug_interactions')) {
                $prescription->update(['drug_interaction_checks' => $request->drug_interactions]);
            }

            if ($request->filled('allergy_checks')) {
                $prescription->update(['allergy_checks' => $request->allergy_checks]);
            }

            if ($request->filled('clinical_reviews')) {
                $prescription->update(['clinical_reviews' => $request->clinical_reviews]);
            }

            DB::commit();

            return response()->json([
                'message' => "Prescription {$request->action}ed successfully",
                'prescription' => $prescription
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Verification failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Dispense prescription.
     */
    public function dispense(Request $request, Prescription $prescription): JsonResponse
    {
        $user = auth()->user();

        if (!$user->isPharmacist()) {
            return response()->json(['error' => 'Only pharmacists can dispense prescriptions'], 403);
        }

        if (!$prescription->isVerified()) {
            return response()->json(['error' => 'Prescription must be verified before dispensing'], 422);
        }

        $validator = Validator::make($request->all(), [
            'quantity_dispensed' => 'required|numeric|min:0.001',
            'lot_number' => 'required|string|max:100',
            'expiration_date_dispensed' => 'required|date|after:today',
            'manufacturer_dispensed' => 'required|string|max:255',
            'ndc_dispensed' => 'required|string|max:20',
            'consultation_completed' => 'sometimes|boolean',
            'consultation_notes' => 'required_if:consultation_completed,true|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Check if consultation is required
            if ($prescription->requiresConsultation() && !$request->boolean('consultation_completed')) {
                return response()->json(['error' => 'Patient consultation required before dispensing'], 422);
            }

            // Mark as dispensed
            $dispensingData = $request->only([
                'quantity_dispensed',
                'lot_number',
                'expiration_date_dispensed',
                'manufacturer_dispensed',
                'ndc_dispensed'
            ]);

            $prescription->markAsDispensed($dispensingData);

            // Handle consultation if completed
            if ($request->boolean('consultation_completed')) {
                $prescription->update([
                    'consultation_completed' => true,
                    'consultation_completed_at' => now(),
                    'consultation_pharmacist_id' => $user->id,
                ]);

                PrescriptionAuditLog::logConsultation($prescription, [
                    'notes' => $request->consultation_notes,
                ]);
            }

            // Create audit log
            PrescriptionAuditLog::logDispensed($prescription, $dispensingData);

            DB::commit();

            return response()->json([
                'message' => 'Prescription dispensed successfully',
                'prescription' => $prescription
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to dispense prescription: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create a refill prescription.
     */
    public function refill(Request $request, Prescription $prescription): JsonResponse
    {
        $user = auth()->user();

        // Patients can request refills, pharmacists can process them
        if ($user->isPatient() && $prescription->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$prescription->canBeRefilled()) {
            return response()->json(['error' => 'Prescription cannot be refilled'], 422);
        }

        try {
            DB::beginTransaction();

            // Create refill prescription
            $refillData = $prescription->toArray();
            
            // Remove fields that shouldn't be copied
            unset($refillData['id'], $refillData['prescription_number'], $refillData['created_at'], 
                  $refillData['updated_at'], $refillData['deleted_at']);

            // Set refill-specific data
            $refillData['is_refill'] = true;
            $refillData['original_prescription_id'] = $prescription->id;
            $refillData['date_received'] = now();
            $refillData['verification_status'] = Prescription::VERIFICATION_PENDING;
            $refillData['processing_status'] = Prescription::PROCESSING_RECEIVED;
            $refillData['reviewing_pharmacist_id'] = null;
            $refillData['dispensing_pharmacist_id'] = null;
            $refillData['review_started_at'] = null;
            $refillData['review_completed_at'] = null;
            $refillData['date_dispensed'] = null;

            $refill = Prescription::create($refillData);

            // Create audit logs
            PrescriptionAuditLog::logCreated($refill, ['refill_of' => $prescription->prescription_number]);
            PrescriptionAuditLog::createLog(
                $prescription->id,
                PrescriptionAuditLog::ACTION_REFILLED,
                "Refill requested - new prescription: {$refill->prescription_number}"
            );

            DB::commit();

            return response()->json([
                'message' => 'Refill created successfully',
                'refill' => $refill
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create refill: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get prescription audit trail.
     */
    public function auditTrail(Prescription $prescription): JsonResponse
    {
        $user = auth()->user();

        // Check authorization
        if ($user->isPatient() && $prescription->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $auditLogs = $prescription->auditLogs()
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($auditLogs);
    }

    /**
     * Get prescriptions requiring review.
     */
    public function requiresReview(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user->isPharmacist()) {
            return response()->json(['error' => 'Only pharmacists can access this endpoint'], 403);
        }

        $prescriptions = Prescription::requiringReview()
            ->with([
                'patient:id,name,email',
                'prescriber:id,first_name,last_name,npi_number',
                'product:id,name,brand_name'
            ])
            ->orderBy('priority_level', 'asc')
            ->orderBy('date_received', 'asc')
            ->paginate($request->input('per_page', 15));

        return response()->json($prescriptions);
    }

    /**
     * Get controlled substance prescriptions.
     */
    public function controlledSubstances(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!$user->isPharmacist()) {
            return response()->json(['error' => 'Only pharmacists can access controlled substances'], 403);
        }

        $prescriptions = Prescription::controlledSubstances()
            ->with([
                'patient:id,name,email',
                'prescriber:id,first_name,last_name,npi_number,dea_number',
                'dispensingPharmacist:id,name'
            ])
            ->orderBy('controlled_substance_schedule', 'asc')
            ->orderBy('date_received', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json($prescriptions);
    }

    /**
     * Download prescription files.
     */
    public function downloadFile(Prescription $prescription, string $filename): Response
    {
        $user = auth()->user();

        // Check authorization
        if ($user->isPatient() && $prescription->patient_id !== $user->id) {
            abort(403);
        }

        $uploadedFiles = $prescription->uploaded_files ?? [];
        $file = collect($uploadedFiles)->firstWhere('filename', $filename);

        if (!$file) {
            abort(404);
        }

        if (!Storage::exists($file['path'])) {
            abort(404);
        }

        return Storage::download($file['path'], $file['original_name']);
    }

    /**
     * Cancel prescription.
     */
    public function cancel(Request $request, Prescription $prescription): JsonResponse
    {
        $user = auth()->user();

        // Only pharmacists or the patient can cancel
        if (!$user->isPharmacist() && $prescription->patient_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($prescription->isDispensed()) {
            return response()->json(['error' => 'Cannot cancel dispensed prescription'], 422);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $prescription->update([
                'verification_status' => Prescription::VERIFICATION_CANCELLED,
                'pharmacist_notes' => $request->reason,
            ]);

            PrescriptionAuditLog::createLog(
                $prescription->id,
                PrescriptionAuditLog::ACTION_CANCELLED,
                "Prescription cancelled by {$user->name}: {$request->reason}"
            );

            return response()->json([
                'message' => 'Prescription cancelled successfully',
                'prescription' => $prescription
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to cancel prescription: ' . $e->getMessage()], 500);
        }
    }
}