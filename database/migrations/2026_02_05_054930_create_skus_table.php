<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('skus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // Identification
            $table->string('code')->unique(); // Unique SKU in entire database
            $table->string('design_sku')->nullable(); // For Qikink
            $table->string('product_sku')->nullable(); // For Qikink

            // Pricing
            $table->decimal('price', 10, 2); // Selling price (Customer Purchase)
            $table->decimal('cost_price', 10, 2)->nullable(); // Product price (Business Purchase)
            $table->decimal('mrp', 10, 2)->nullable(); // Label price (Slashed)

            // Stock
            $table->integer('stock')->default(0);
            $table->integer('min_order_quantity')->default(1);
            $table->integer('max_order_quantity')->nullable();

            // Dimensions (Auto-pick logic provided by Qikink/User types, but stored here per SKU potentially or on product type default)
            $table->decimal('weight', 8, 3)->nullable();
            $table->decimal('width', 8, 3)->nullable();
            $table->decimal('height', 8, 3)->nullable();
            $table->decimal('length', 8, 3)->nullable();

            $table->timestamps();
        });

        // Pivot table to link SKUs to Attribute Values (e.g. This SKU is Black, Small)
        Schema::create('sku_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sku_id')->constrained('skus')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained('attribute_values')->cascadeOnDelete();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skus');
    }
};
