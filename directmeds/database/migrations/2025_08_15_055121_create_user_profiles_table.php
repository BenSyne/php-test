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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Personal Information
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('preferred_name')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->string('ssn_encrypted')->nullable(); // For HIPAA compliance, should be encrypted
            
            // Address Information
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('US');
            
            // Contact Information
            $table->string('phone_mobile')->nullable();
            $table->string('phone_work')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            
            // Medical Information (for patients)
            $table->json('allergies')->nullable(); // Store as JSON for flexibility
            $table->json('medical_conditions')->nullable();
            $table->json('current_medications')->nullable();
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->string('insurance_group_number')->nullable();
            $table->date('insurance_expiry')->nullable();
            
            // Professional Information (for healthcare providers)
            $table->string('specialization')->nullable();
            $table->string('medical_school')->nullable();
            $table->year('graduation_year')->nullable();
            $table->json('certifications')->nullable(); // Store certifications as JSON
            $table->text('bio')->nullable();
            $table->string('consultation_fee')->nullable();
            
            // Pharmacy-specific fields
            $table->string('preferred_pharmacy_id')->nullable();
            $table->boolean('consent_to_text')->default(false);
            $table->boolean('consent_to_email')->default(true);
            $table->boolean('consent_to_marketing')->default(false);
            
            // Avatar/Profile Image
            $table->string('avatar_path')->nullable();
            
            // Privacy and Security
            $table->boolean('profile_visibility')->default(true);
            $table->timestamp('privacy_policy_accepted_at')->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
            
            // Audit Trail
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            
            // Indexes
            $table->index(['user_id']);
            $table->index(['first_name', 'last_name']);
            $table->index(['postal_code']);
            $table->index(['preferred_pharmacy_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};