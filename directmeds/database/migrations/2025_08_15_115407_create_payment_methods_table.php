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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Payment method type and gateway
            $table->string('type'); // 'card', 'bank_account', 'insurance'
            $table->string('gateway')->default('stripe'); // 'stripe', 'square', etc.
            $table->string('gateway_method_id')->nullable(); // Stripe payment method ID
            
            // Card information (tokenized/masked)
            $table->string('card_brand')->nullable(); // visa, mastercard, amex, etc.
            $table->string('card_last_four', 4)->nullable();
            $table->integer('card_exp_month')->nullable();
            $table->integer('card_exp_year')->nullable();
            $table->string('card_fingerprint')->nullable(); // For deduplication
            
            // Bank account information (masked)
            $table->string('bank_name')->nullable();
            $table->string('bank_account_type')->nullable(); // checking, savings
            $table->string('bank_account_last_four', 4)->nullable();
            $table->string('bank_routing_number_last_four', 4)->nullable();
            
            // Insurance information
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_member_id')->nullable();
            $table->string('insurance_group_number')->nullable();
            $table->string('insurance_plan_name')->nullable();
            $table->decimal('insurance_copay_amount', 8, 2)->nullable();
            $table->decimal('insurance_deductible', 8, 2)->nullable();
            $table->boolean('insurance_verified')->default(false);
            $table->timestamp('insurance_verified_at')->nullable();
            
            // Security and compliance
            $table->text('encrypted_data')->nullable(); // Encrypted sensitive data
            $table->string('pci_token')->nullable(); // PCI-compliant token
            $table->string('tokenization_method')->nullable(); // 'gateway', 'vault', etc.
            
            // Status and metadata
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            
            // Billing address
            $table->string('billing_name')->nullable();
            $table->string('billing_street_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state', 2)->nullable();
            $table->string('billing_postal_code', 10)->nullable();
            $table->string('billing_country', 2)->default('US');
            
            // Audit fields
            $table->string('created_by_ip')->nullable();
            $table->string('last_used_ip')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->integer('usage_count')->default(0);
            $table->json('compliance_checks')->nullable();
            $table->json('fraud_checks')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'is_default']);
            $table->index(['gateway', 'gateway_method_id']);
            $table->index(['type', 'is_active']);
            $table->index('card_fingerprint');
            $table->index('pci_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};