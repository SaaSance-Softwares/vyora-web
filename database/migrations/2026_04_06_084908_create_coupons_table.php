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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            
            // Basics
            $table->string('code')->unique();
            $table->string('name')->nullable(); // Internal reference name
            $table->enum('type', ['percentage', 'fixed', 'free_shipping', 'bogo'])->default('percentage');
            $table->decimal('discount_amount', 10, 2)->nullable();
            
            // Status & Display
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default_magic')->default(false); // Auto apply logic
            $table->boolean('show_on_product_page')->default(false);

            // Time & Validity
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();

            // Usage Limits
            $table->integer('usage_limit')->nullable(); // Total times coupon can be used globally
            $table->integer('usage_limit_per_user')->nullable(); // Total times a single user can use it
            $table->integer('times_used')->default(0); // Track global usage

            // Advanced Constraints
            $table->decimal('min_cart_value', 10, 2)->nullable();
            $table->integer('min_item_quantity')->nullable();
            
            $table->boolean('exclude_sale_items')->default(false);
            $table->boolean('first_time_users_only')->default(false);
            $table->boolean('can_combine')->default(false); // Can stack with other coupons
            
            // JSON targets for specific Products or Categories (nullable if storewide)
            $table->json('applicable_product_ids')->nullable();
            $table->json('applicable_category_ids')->nullable();
            $table->json('excluded_product_ids')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
