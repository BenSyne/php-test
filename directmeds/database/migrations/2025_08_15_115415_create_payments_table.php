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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('payment_number')->unique(); // Human-readable payment ID
            
            // Core payment information
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('payment_method_id')->nullable()->constrained()->onDelete('set null');
            
            // Payment details
            $table->string('type'); // 'payment', 'refund', 'partial_refund', 'chargeback'
            $table->string('status'); // 'pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'
            $table->string('gateway')->default('stripe'); // 'stripe', 'square', etc.
            $table->string('gateway_transaction_id')->nullable();
            $table->string('gateway_payment_intent_id')->nullable();
            $table->string('gateway_charge_id')->nullable();
            
            // Amount information
            $table->decimal('amount', 10, 2); // Total payment amount
            $table->decimal('amount_authorized', 10, 2)->nullable(); // Amount authorized
            $table->decimal('amount_captured', 10, 2)->nullable(); // Amount actually captured
            $table->decimal('amount_refunded', 10, 2)->default(0); // Amount refunded
            $table->decimal('amount_fee', 8, 2)->nullable(); // Gateway processing fee
            $table->decimal('amount_net', 10, 2)->nullable(); // Net amount after fees
            $table->string('currency', 3)->default('USD');
            
            // Insurance and copay
            $table->decimal('insurance_copay', 8, 2)->nullable();
            $table->decimal('insurance_coverage', 8, 2)->nullable();
            $table->string('insurance_claim_number')->nullable();
            $table->boolean('insurance_processed')->default(false);
            $table->timestamp('insurance_processed_at')->nullable();
            
            // Payment method snapshot (for audit trail)
            $table->json('payment_method_snapshot')->nullable();
            $table->string('payment_method_type')->nullable(); // card, bank_account, insurance
            $table->string('card_last_four', 4)->nullable();
            $table->string('card_brand')->nullable();
            
            // Transaction flow
            $table->string('flow_type')->default('single'); // 'single', 'split', 'recurring'
            $table->foreignId('parent_payment_id')->nullable()->constrained('payments')->onDelete('set null');
            $table->boolean('is_partial')->default(false);
            $table->integer('installment_number')->nullable();
            $table->integer('total_installments')->nullable();
            
            // Authorization and capture
            $table->timestamp('authorized_at')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('expires_at')->nullable(); // Authorization expiration
            $table->boolean('auto_capture')->default(true);
            $table->timestamp('capture_scheduled_at')->nullable();
            
            // Compliance and security
            $table->string('pci_compliance_level')->default('level_1');
            $table->boolean('requires_3ds')->default(false);
            $table->json('3ds_data')->nullable();
            $table->boolean('passed_3ds')->default(false);
            $table->json('fraud_check_result')->nullable();
            $table->decimal('fraud_score', 5, 2)->nullable();
            $table->boolean('manual_review_required')->default(false);
            $table->boolean('manual_review_passed')->nullable();
            $table->timestamp('manual_review_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Failure and retry information
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();
            $table->json('gateway_response')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            
            // Refund information
            $table->string('refund_reason')->nullable();
            $table->text('refund_notes')->nullable();
            $table->foreignId('refunded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('refunded_at')->nullable();
            $table->string('refund_reference')->nullable();
            
            // Metadata and notes
            $table->text('description')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->json('webhook_data')->nullable();
            
            // Audit trail
            $table->string('created_by_ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('audit_trail')->nullable();
            $table->json('compliance_logs')->nullable();
            
            // Timestamps
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['order_id', 'type']);
            $table->index(['payment_method_id', 'status']);
            $table->index(['gateway', 'gateway_transaction_id']);
            $table->index(['gateway', 'gateway_payment_intent_id']);
            $table->index(['status', 'created_at']);
            $table->index(['type', 'status']);
            $table->index(['parent_payment_id']);
            $table->index(['manual_review_required', 'manual_review_passed']);
            $table->index(['processed_at']);
            $table->index(['created_at', 'amount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};