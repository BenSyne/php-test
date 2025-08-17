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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            // Basic Product Information
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('generic_name');
            
            // NDC and Identification
            $table->string('ndc_number', 15)->unique(); // National Drug Code
            $table->string('upc_code', 20)->nullable(); // Universal Product Code
            $table->string('lot_number')->nullable();
            
            // DEA and Legal Classification
            $table->string('dea_schedule', 5)->nullable(); // CI, CII, CIII, CIV, CV
            $table->boolean('is_controlled_substance')->default(false);
            $table->boolean('requires_prescription')->default(true);
            $table->boolean('is_otc')->default(false); // Over-the-counter
            
            // Physical Properties
            $table->string('dosage_form'); // tablet, capsule, liquid, etc.
            $table->string('strength'); // e.g., "500mg", "10mg/5ml"
            $table->string('route_of_administration')->nullable(); // oral, topical, injection
            $table->integer('package_size')->nullable(); // number of units
            $table->string('package_type')->nullable(); // bottle, blister pack, etc.
            
            // Relationships
            $table->foreignId('manufacturer_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            
            // Pricing and Inventory
            $table->decimal('cost_price', 10, 4)->nullable();
            $table->decimal('retail_price', 10, 2);
            $table->decimal('insurance_price', 10, 2)->nullable();
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('minimum_stock_level')->default(10);
            $table->integer('maximum_stock_level')->default(1000);
            
            // Expiration and Storage
            $table->date('expiration_date')->nullable();
            $table->string('storage_requirements')->nullable();
            $table->decimal('storage_temperature_min', 5, 2)->nullable();
            $table->decimal('storage_temperature_max', 5, 2)->nullable();
            
            // Clinical Information
            $table->text('active_ingredients')->nullable(); // JSON or comma-separated
            $table->text('inactive_ingredients')->nullable();
            $table->text('warnings')->nullable();
            $table->text('side_effects')->nullable();
            $table->text('contraindications')->nullable();
            $table->text('drug_interactions')->nullable(); // JSON array of drug names
            $table->text('dosage_instructions')->nullable();
            
            // Regulatory and Compliance
            $table->string('fda_approval_number')->nullable();
            $table->date('fda_approval_date')->nullable();
            $table->string('therapeutic_equivalence_code', 5)->nullable(); // AB, AA, etc.
            $table->boolean('is_generic')->default(false);
            $table->foreignId('brand_equivalent_id')->nullable()->constrained('products');
            
            // Status and Availability
            $table->boolean('is_active')->default(true);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_discontinued')->default(false);
            $table->date('discontinuation_date')->nullable();
            $table->string('discontinuation_reason')->nullable();
            
            // SEO and Display
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('image_url')->nullable();
            $table->json('images')->nullable(); // Array of image URLs
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['ndc_number']);
            $table->index(['generic_name', 'is_active']);
            $table->index(['brand_name', 'is_active']);
            $table->index(['dea_schedule', 'is_controlled_substance']);
            $table->index(['requires_prescription', 'is_otc']);
            $table->index(['manufacturer_id', 'is_active']);
            $table->index(['category_id', 'is_active']);
            $table->index(['is_active', 'is_available', 'is_discontinued']);
            $table->index(['expiration_date']);
            // Note: Fulltext indexes are not supported in SQLite
            // $table->fullText(['name', 'brand_name', 'generic_name', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
