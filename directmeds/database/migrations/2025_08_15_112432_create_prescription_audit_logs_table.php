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
        Schema::create('prescription_audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Reference to the prescription
            $table->unsignedBigInteger('prescription_id');
            
            // Action Details
            $table->string('action'); // created, updated, verified, rejected, dispensed, etc.
            $table->string('action_type'); // system, user, automated
            $table->text('description'); // Human readable description of the action
            
            // User Information
            $table->unsignedBigInteger('user_id')->nullable(); // Who performed the action
            $table->string('user_name')->nullable(); // Store name for audit trail
            $table->string('user_type')->nullable(); // patient, pharmacist, admin, etc.
            $table->string('user_role')->nullable(); // Additional role information
            
            // System Information
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('session_id')->nullable();
            
            // Change Details
            $table->json('old_values')->nullable(); // Previous values (for updates)
            $table->json('new_values')->nullable(); // New values (for updates)
            $table->json('metadata')->nullable(); // Additional context data
            
            // Compliance and Legal
            $table->boolean('is_hipaa_action')->default(false); // HIPAA-sensitive action
            $table->boolean('is_dea_action')->default(false); // DEA compliance action
            $table->boolean('requires_retention')->default(true); // Must be retained for compliance
            $table->integer('retention_years')->default(7); // How long to retain this record
            
            // Verification and Integrity
            $table->string('checksum')->nullable(); // For data integrity verification
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            
            // System Fields
            $table->string('source_system')->default('directmeds'); // Source of the action
            $table->string('environment')->nullable(); // production, staging, etc.
            $table->string('application_version')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance and compliance queries
            $table->index(['prescription_id']);
            $table->index(['action']);
            $table->index(['action_type']);
            $table->index(['user_id']);
            $table->index(['is_hipaa_action']);
            $table->index(['is_dea_action']);
            $table->index(['created_at']);
            $table->index(['prescription_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            
            // Foreign Key Constraints
            $table->foreign('prescription_id')->references('id')->on('prescriptions')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_audit_logs');
    }
};