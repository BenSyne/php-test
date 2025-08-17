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
        Schema::table('users', function (Blueprint $table) {
            // User type enum for different roles in pharmacy system
            $table->enum('user_type', ['patient', 'pharmacist', 'admin', 'prescriber'])
                  ->default('patient')
                  ->after('email');
            
            // Two-Factor Authentication fields
            $table->boolean('two_factor_enabled')->default(false)->after('password');
            $table->text('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            
            // Security and compliance fields
            $table->boolean('is_active')->default(true)->after('two_factor_confirmed_at');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->integer('failed_login_attempts')->default(0)->after('last_login_ip');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            
            // HIPAA compliance fields
            $table->boolean('hipaa_acknowledged')->default(false)->after('locked_until');
            $table->timestamp('hipaa_acknowledged_at')->nullable()->after('hipaa_acknowledged');
            $table->string('hipaa_acknowledgment_ip')->nullable()->after('hipaa_acknowledged_at');
            
            // Professional credentials (for pharmacists and prescribers)
            $table->string('license_number')->nullable()->after('hipaa_acknowledgment_ip');
            $table->string('license_state')->nullable()->after('license_number');
            $table->date('license_expiry')->nullable()->after('license_state');
            $table->string('dea_number')->nullable()->after('license_expiry');
            $table->string('npi_number')->nullable()->after('dea_number');
            
            // Pharmacy association (for pharmacists)
            $table->unsignedBigInteger('pharmacy_id')->nullable()->after('npi_number');
            
            // Contact information
            $table->string('phone')->nullable()->after('pharmacy_id');
            $table->date('date_of_birth')->nullable()->after('phone');
            
            // Soft deletes for compliance
            $table->softDeletes()->after('updated_at');
            
            // Audit trail
            $table->unsignedBigInteger('created_by')->nullable()->after('deleted_at');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');
            
            // Indexes for performance and security
            $table->index(['user_type', 'is_active']);
            $table->index(['license_number', 'license_state']);
            $table->index(['pharmacy_id']);
            $table->index(['last_login_at']);
            $table->index(['failed_login_attempts', 'locked_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['user_type', 'is_active']);
            $table->dropIndex(['license_number', 'license_state']);
            $table->dropIndex(['pharmacy_id']);
            $table->dropIndex(['last_login_at']);
            $table->dropIndex(['failed_login_attempts', 'locked_until']);
            
            $table->dropColumn([
                'user_type',
                'two_factor_enabled',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'is_active',
                'last_login_at',
                'last_login_ip',
                'failed_login_attempts',
                'locked_until',
                'hipaa_acknowledged',
                'hipaa_acknowledged_at',
                'hipaa_acknowledgment_ip',
                'license_number',
                'license_state',
                'license_expiry',
                'dea_number',
                'npi_number',
                'pharmacy_id',
                'phone',
                'date_of_birth',
                'deleted_at',
                'created_by',
                'updated_by',
                'deleted_by'
            ]);
        });
    }
};