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
        Schema::create('prescribers', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('suffix')->nullable(); // Jr, Sr, III, etc.
            $table->string('title')->nullable(); // Dr, MD, DO, NP, PA, etc.
            
            // Professional Identifiers
            $table->string('npi_number')->unique(); // National Provider Identifier (required)
            $table->string('dea_number')->unique()->nullable(); // DEA Registration Number (required for controlled substances)
            $table->string('state_license_number');
            $table->string('state_license_state', 2); // 2-letter state code
            $table->date('state_license_expiry');
            
            // Additional License Information
            $table->json('additional_licenses')->nullable(); // For multi-state licenses
            $table->string('specialty')->nullable();
            $table->string('subspecialty')->nullable();
            
            // Contact Information
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            
            // Practice Information
            $table->string('practice_name')->nullable();
            $table->text('practice_address');
            $table->string('practice_city');
            $table->string('practice_state', 2);
            $table->string('practice_zip', 10);
            $table->string('practice_phone')->nullable();
            $table->string('practice_fax')->nullable();
            
            // DEA Information (for controlled substances)
            $table->string('dea_schedule')->nullable(); // Schedules I-V
            $table->date('dea_expiry')->nullable();
            $table->string('dea_activity_code')->nullable(); // A, B, C, etc.
            $table->string('dea_business_activity')->nullable();
            
            // Verification Status
            $table->enum('verification_status', [
                'pending',
                'verified',
                'suspended',
                'revoked',
                'expired'
            ])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->text('verification_notes')->nullable();
            
            // System Fields
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_prescription_date')->nullable();
            $table->integer('total_prescriptions')->default(0);
            $table->json('compliance_flags')->nullable(); // Array of compliance issues
            
            // Audit Trail
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['npi_number']);
            $table->index(['dea_number']);
            $table->index(['state_license_number', 'state_license_state']);
            $table->index(['verification_status']);
            $table->index(['is_active']);
            $table->index(['practice_state']);
            $table->index(['specialty']);
            $table->index(['created_at']);
            
            // Foreign Key Constraints
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
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
        Schema::dropIfExists('prescribers');
    }
};