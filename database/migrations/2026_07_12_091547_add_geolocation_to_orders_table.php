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
            $table->string('ip_address', 45)->nullable();
            $table->string('placed_from_city')->nullable();
            $table->string('placed_from_state')->nullable();
            $table->string('placed_from_country')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'placed_from_city', 'placed_from_state', 'placed_from_country']);
        });
    }
};
