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
        Schema::create('compliance_reports', function (Blueprint $table) {
            $table->id();
            
            // Report identification
            $table->string('report_type'); // hipaa, dea, pci, audit_trail, data_retention, etc.
            $table->string('report_name');
            $table->string('report_period'); // daily, weekly, monthly, quarterly, yearly
            $table->date('period_start');
            $table->date('period_end');
            $table->string('report_identifier')->unique(); // unique identifier for the report
            
            // Report metadata
            $table->enum('status', ['pending', 'generating', 'completed', 'failed', 'archived'])->default('pending');
            $table->json('parameters')->nullable(); // report generation parameters
            $table->json('filters')->nullable(); // filters applied to the report
            $table->text('description')->nullable();
            
            // Compliance framework
            $table->string('regulatory_framework'); // HIPAA, DEA, PCI-DSS, SOX, etc.
            $table->string('compliance_standard')->nullable(); // specific standard or requirement
            $table->enum('criticality', ['low', 'medium', 'high', 'critical'])->default('medium');
            
            // Report content
            $table->json('summary_data')->nullable(); // high-level summary statistics
            $table->json('detailed_findings')->nullable(); // detailed compliance findings
            $table->json('violations')->nullable(); // compliance violations found
            $table->json('recommendations')->nullable(); // recommended actions
            $table->longText('raw_data')->nullable(); // raw report data (compressed)
            
            // File storage
            $table->string('file_path')->nullable(); // path to generated report file
            $table->string('file_format')->nullable(); // PDF, CSV, JSON, XML
            $table->bigInteger('file_size')->nullable(); // file size in bytes
            $table->string('file_hash')->nullable(); // file integrity hash
            
            // Generation details
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamp('generation_started_at')->nullable();
            $table->timestamp('generation_completed_at')->nullable();
            $table->integer('generation_time_seconds')->nullable();
            $table->text('generation_errors')->nullable();
            
            // Compliance metrics
            $table->decimal('compliance_score', 5, 2)->nullable(); // 0.00 to 100.00
            $table->integer('total_records_analyzed')->default(0);
            $table->integer('violations_found')->default(0);
            $table->integer('warnings_found')->default(0);
            $table->integer('exceptions_found')->default(0);
            
            // Retention and archival
            $table->boolean('requires_retention')->default(true);
            $table->integer('retention_years')->default(7);
            $table->date('retention_expiry_date')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            
            // Review and approval
            $table->enum('review_status', ['pending', 'under_review', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            
            // Distribution
            $table->json('distribution_list')->nullable(); // who should receive this report
            $table->timestamp('distributed_at')->nullable();
            $table->json('distribution_log')->nullable(); // log of who received the report
            
            // System metadata
            $table->string('system_version')->nullable();
            $table->string('report_template_version')->nullable();
            $table->json('system_state')->nullable(); // system state at time of generation
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['report_type', 'created_at']);
            $table->index(['regulatory_framework', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['period_start', 'period_end']);
            $table->index(['generated_by', 'created_at']);
            $table->index(['review_status', 'created_at']);
            $table->index(['is_archived', 'retention_expiry_date']);
            $table->index(['report_identifier']);
            
            // Foreign key constraints
            $table->foreign('generated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_reports');
    }
};
