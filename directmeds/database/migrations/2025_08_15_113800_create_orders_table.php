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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('cart_id')->nullable()->constrained()->onDelete('set null');
            
            // Order status tracking
            $table->string('status')->default('pending'); // pending, processing, shipped, delivered, cancelled, refunded
            $table->string('payment_status')->default('pending'); // pending, processing, paid, failed, refunded
            $table->string('fulfillment_status')->default('pending'); // pending, processing, partially_fulfilled, fulfilled, cancelled
            
            // Financial information
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('shipping_amount', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            
            // Customer information
            $table->json('customer_info'); // name, email, phone from user at time of order
            
            // Addresses
            $table->json('billing_address');
            $table->json('shipping_address');
            
            // Payment information
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->json('payment_details')->nullable(); // Store encrypted payment info
            $table->timestamp('payment_processed_at')->nullable();
            
            // Insurance information
            $table->json('insurance_info')->nullable();
            $table->decimal('insurance_copay', 10, 2)->nullable();
            $table->decimal('insurance_coverage', 10, 2)->nullable();
            
            // Shipping information
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('carrier')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('shipping_details')->nullable();
            
            // Pharmacy information
            $table->foreignId('processing_pharmacist_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('fulfillment_pharmacist_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('prescription_verification_completed_at')->nullable();
            
            // Notes and special instructions
            $table->text('customer_notes')->nullable();
            $table->text('pharmacy_notes')->nullable();
            $table->text('special_instructions')->nullable();
            
            // Compliance and audit
            $table->json('compliance_checks')->nullable();
            $table->json('audit_trail')->nullable();
            $table->boolean('requires_signature')->default(false);
            $table->timestamp('signature_required_by')->nullable();
            
            // Dates
            $table->timestamp('estimated_delivery_date')->nullable();
            $table->timestamp('requested_delivery_date')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            $table->string('source')->default('web'); // web, mobile, phone, etc.
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['payment_status', 'created_at']);
            $table->index(['fulfillment_status', 'created_at']);
            $table->index('order_number');
            $table->index('tracking_number');
            $table->index('estimated_delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};