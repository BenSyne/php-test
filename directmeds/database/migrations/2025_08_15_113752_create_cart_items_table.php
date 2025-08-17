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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('prescription_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->json('product_snapshot')->nullable(); // Store product data at time of addition
            $table->json('prescription_snapshot')->nullable(); // Store prescription data if applicable
            $table->text('special_instructions')->nullable();
            $table->json('metadata')->nullable(); // Store additional item metadata
            $table->timestamps();
            
            $table->index(['cart_id', 'product_id']);
            $table->index('prescription_id');
            $table->unique(['cart_id', 'product_id', 'prescription_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};