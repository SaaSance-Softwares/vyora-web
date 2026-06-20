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
        Schema::table('skus', function (Blueprint $table) {
            // Swap columns logic: 
            // 1. Rename design_sku to a temporary name
            // 2. Rename product_sku to design_sku
            // 3. Rename temporary name to product_sku
            
            $table->renameColumn('design_sku', 'temp_sku');
        });

        Schema::table('skus', function (Blueprint $table) {
            $table->renameColumn('product_sku', 'design_sku');
        });

        Schema::table('skus', function (Blueprint $table) {
            $table->renameColumn('temp_sku', 'product_sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skus', function (Blueprint $table) {
            $table->renameColumn('product_sku', 'temp_sku');
        });

        Schema::table('skus', function (Blueprint $table) {
            $table->renameColumn('design_sku', 'product_sku');
        });

        Schema::table('skus', function (Blueprint $table) {
            $table->renameColumn('temp_sku', 'design_sku');
        });
    }
};
