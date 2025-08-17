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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Core audit information
            $table->string('event_type'); // action performed
            $table->string('entity_type'); // model being acted upon
            $table->unsignedBigInteger('entity_id')->nullable(); // ID of the entity
            $table->string('entity_identifier')->nullable(); // human-readable identifier
            
            // User information
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_type')->nullable();
            $table->string('user_role')->nullable();
            
            // Request context
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->string('request_id')->nullable();
            $table->string('route_name')->nullable();
            $table->string('http_method')->nullable();
            $table->text('url')->nullable();
            
            // Data tracking
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            
            // Compliance flags
            $table->boolean('is_phi_access')->default(false); // HIPAA PHI access
            $table->boolean('is_controlled_substance')->default(false); // DEA controlled substance
            $table->boolean('is_financial_data')->default(false); // PCI compliance
            $table->boolean('requires_retention')->default(true);
            $table->integer('retention_years')->default(7);
            
            // Audit trail integrity
            $table->string('checksum'); // for data integrity verification
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            
            // System metadata
            $table->string('source_system')->default('directmeds');
            $table->string('environment')->nullable();
            $table->string('application_version')->nullable();
            $table->text('description')->nullable();
            
            // Performance tracking
            $table->decimal('response_time_ms', 8, 2)->nullable();
            $table->integer('response_status')->nullable();
            $table->boolean('access_granted')->default(true);
            
            // Risk classification
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->enum('data_classification', ['public', 'internal', 'confidential', 'phi', 'pci'])->default('internal');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index(['event_type', 'created_at']);
            $table->index(['is_phi_access', 'created_at']);
            $table->index(['is_controlled_substance', 'created_at']);
            $table->index(['data_classification', 'created_at']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['session_id']);
            $table->index(['checksum']);
            
            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
