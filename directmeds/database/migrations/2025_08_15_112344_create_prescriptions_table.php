<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            
            // Prescription Identifiers
            $table->string('prescription_number')->unique(); // Internal prescription number
            $table->string('rx_number')->nullable(); // Original Rx number from prescriber
            $table->string('original_prescription_id')->nullable(); // For refills
            
            // Patient Information
            $table->unsignedBigInteger('patient_id');
            $table->string('patient_name'); // Store for verification
            $table->date('patient_dob'); // Store for verification
            $table->text('patient_address')->nullable();
            
            // Prescriber Information
            $table->unsignedBigInteger('prescriber_id');
            $table->string('prescriber_name'); // Store for verification
            $table->string('prescriber_npi'); // Store for verification
            $table->string('prescriber_dea')->nullable(); // Store for controlled substances
            
            // Medication Information
            $table->unsignedBigInteger('product_id')->nullable(); // Reference to products table
            $table->string('medication_name'); // As written on prescription
            $table->string('generic_name')->nullable(); // Generic equivalent
            $table->string('ndc_number')->nullable(); // National Drug Code
            $table->string('strength'); // e.g., "10mg", "250mg/5ml"
            $table->string('dosage_form'); // tablet, capsule, liquid, etc.
            $table->string('route_of_administration')->nullable(); // oral, topical, injection, etc.
            
            // Prescription Details
            $table->decimal('quantity_prescribed', 10, 3); // Quantity prescribed
            $table->string('quantity_unit')->default('each'); // each, ml, g, etc.
            $table->decimal('days_supply', 5, 1); // Days supply
            $table->text('directions_for_use'); // Sig - directions for use
            $table->text('indication')->nullable(); // What it's for
            
            // Refill Information
            $table->integer('refills_authorized')->default(0);
            $table->integer('refills_remaining')->default(0);
            $table->integer('refills_used')->default(0);
            $table->boolean('is_refill')->default(false);
            
            // Controlled Substance Information
            $table->enum('controlled_substance_schedule', ['I', 'II', 'III', 'IV', 'V', 'N'])->default('N'); // N = Not controlled
            $table->boolean('is_controlled_substance')->default(false);
            $table->string('dea_form_number')->nullable(); // For Schedule II substances
            
            // Prescription Dates
            $table->date('date_written');
            $table->date('date_received')->nullable();
            $table->date('date_filled')->nullable();
            $table->date('date_dispensed')->nullable();
            $table->date('expiration_date')->nullable(); // When prescription expires
            $table->date('discard_after_date')->nullable(); // "Discard after" date
            
            // Upload Information
            $table->json('uploaded_files')->nullable(); // Array of uploaded file paths/metadata
            $table->string('upload_method')->nullable(); // 'upload', 'fax', 'escript', 'phone'
            $table->text('upload_notes')->nullable();
            
            // Verification Status
            $table->enum('verification_status', [
                'pending',           // Just uploaded/received
                'in_review',        // Being reviewed by pharmacist
                'verified',         // Verified and ready to fill
                'rejected',         // Rejected by pharmacist
                'on_hold',          // On hold pending clarification
                'expired',          // Prescription has expired
                'cancelled'         // Cancelled by prescriber/patient
            ])->default('pending');
            
            // Processing Status
            $table->enum('processing_status', [
                'received',         // Prescription received
                'in_queue',         // In filling queue
                'filling',          // Being filled
                'ready',            // Ready for pickup/shipping
                'dispensed',        // Dispensed to patient
                'returned',         // Returned to stock
                'transferred'       // Transferred to another pharmacy
            ])->default('received');
            
            // Pharmacist Review
            $table->unsignedBigInteger('reviewing_pharmacist_id')->nullable();
            $table->timestamp('review_started_at')->nullable();
            $table->timestamp('review_completed_at')->nullable();
            $table->text('pharmacist_notes')->nullable();
            $table->json('drug_interaction_checks')->nullable();
            $table->json('allergy_checks')->nullable();
            $table->json('clinical_reviews')->nullable();
            
            // Dispensing Information
            $table->unsignedBigInteger('dispensing_pharmacist_id')->nullable();
            $table->decimal('quantity_dispensed', 10, 3)->nullable();
            $table->string('lot_number')->nullable();
            $table->date('expiration_date_dispensed')->nullable();
            $table->string('manufacturer_dispensed')->nullable();
            $table->string('ndc_dispensed')->nullable();
            
            // Insurance and Billing
            $table->json('insurance_information')->nullable();
            $table->decimal('copay_amount', 8, 2)->nullable();
            $table->decimal('total_cost', 8, 2)->nullable();
            $table->decimal('insurance_paid', 8, 2)->nullable();
            $table->decimal('patient_paid', 8, 2)->nullable();
            $table->string('insurance_claim_number')->nullable();
            
            // Transfer Information
            $table->unsignedBigInteger('transferred_from_pharmacy')->nullable();
            $table->unsignedBigInteger('transferred_to_pharmacy')->nullable();
            $table->date('transfer_date')->nullable();
            $table->text('transfer_reason')->nullable();
            
            // Compliance and Legal
            $table->boolean('requires_consultation')->default(false);
            $table->boolean('consultation_completed')->default(false);
            $table->timestamp('consultation_completed_at')->nullable();
            $table->unsignedBigInteger('consultation_pharmacist_id')->nullable();
            $table->json('compliance_checks')->nullable(); // Array of compliance verifications
            $table->text('legal_notes')->nullable();
            
            // System Fields
            $table->boolean('is_active')->default(true);
            $table->integer('priority_level')->default(3); // 1-5, 1 being highest priority
            $table->json('flags')->nullable(); // Array of system flags
            $table->json('alerts')->nullable(); // Array of alerts for pharmacist
            
            // Audit Trail
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['prescription_number']);
            $table->index(['patient_id']);
            $table->index(['prescriber_id']);
            $table->index(['product_id']);
            $table->index(['verification_status']);
            $table->index(['processing_status']);
            $table->index(['is_controlled_substance']);
            $table->index(['controlled_substance_schedule']);
            $table->index(['date_written']);
            $table->index(['date_received']);
            $table->index(['expiration_date']);
            $table->index(['reviewing_pharmacist_id']);
            $table->index(['dispensing_pharmacist_id']);
            $table->index(['priority_level']);
            $table->index(['is_active']);
            $table->index(['created_at']);
            
            // Composite indexes for common queries
            $table->index(['patient_id', 'verification_status']);
            $table->index(['prescriber_id', 'date_written']);
            $table->index(['verification_status', 'processing_status']);
            $table->index(['is_controlled_substance', 'controlled_substance_schedule'], 'idx_controlled_substance');
            
            // Foreign Key Constraints
            $table->foreign('patient_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('prescriber_id')->references('id')->on('prescribers')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('reviewing_pharmacist_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('dispensing_pharmacist_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('consultation_pharmacist_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};