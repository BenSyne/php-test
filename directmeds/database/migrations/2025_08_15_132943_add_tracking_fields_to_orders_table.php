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
        Schema::table('orders', function (Blueprint $table) {
            // Only add the fields that don't exist yet
            $table->enum('order_type', ['new', 'refill'])->default('new');
            $table->foreignId('prescription_id')->nullable()->constrained('prescriptions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['prescription_id']);
            $table->dropColumn(['order_type', 'prescription_id']);
        });
    }
};
