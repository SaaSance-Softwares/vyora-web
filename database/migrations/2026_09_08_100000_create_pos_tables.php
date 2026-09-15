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
        if (!Schema::hasTable('pos_locations')) {
            Schema::create('pos_locations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('type', ['store', 'temporary'])->default('temporary');
                $table->text('address')->nullable();
                $table->string('gst_number')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pos_market_pricing')) {
            Schema::create('pos_market_pricing', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pos_location_id')->constrained('pos_locations')->onDelete('cascade');
                $table->foreignId('sku_id')->constrained('skus')->onDelete('cascade');
                $table->decimal('override_price', 10, 2);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_market_pricing');
        Schema::dropIfExists('pos_locations');
    }
};
