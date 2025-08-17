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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('prescription_id')->nullable()->constrained()->onDelete('set null');
            
            // Product information at time of order
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->string('ndc_number')->nullable();
            $table->json('product_snapshot'); // Complete product data at time of order
            
            // Prescription information
            $table->json('prescription_snapshot')->nullable(); // Prescription data if applicable
            $table->boolean('requires_prescription')->default(false);
            $table->boolean('prescription_verified')->default(false);
            $table->timestamp('prescription_verified_at')->nullable();
            $table->foreignId('prescription_verified_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Quantity and pricing
            $table->integer('quantity_ordered');
            $table->integer('quantity_fulfilled')->default(0);
            $table->integer('quantity_shipped')->default(0);
            $table->integer('quantity_returned')->default(0);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            
            // Insurance and payments
            $table->decimal('insurance_copay', 10, 2)->nullable();
            $table->decimal('insurance_coverage', 10, 2)->nullable();
            $table->decimal('patient_pay_amount', 10, 2)->nullable();
            
            // Fulfillment tracking
            $table->string('fulfillment_status')->default('pending'); // pending, processing, fulfilled, partially_fulfilled, cancelled, returned
            $table->string('lot_number')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('ndc_dispensed')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->foreignId('fulfilled_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Shipping information
            $table->string('tracking_number')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            // Special handling
            $table->text('special_instructions')->nullable();
            $table->text('pharmacist_notes')->nullable();
            $table->json('compliance_checks')->nullable();
            $table->boolean('requires_cold_storage')->default(false);
            $table->boolean('requires_signature')->default(false);
            
            // Return/refund information
            $table->string('return_reason')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->timestamp('refunded_at')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['order_id', 'product_id']);
            $table->index(['prescription_id', 'fulfillment_status']);
            $table->index(['fulfillment_status', 'created_at']);
            $table->index(['requires_prescription', 'prescription_verified']);
            $table->index('tracking_number');
            $table->index('lot_number');
            $table->index('ndc_dispensed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};