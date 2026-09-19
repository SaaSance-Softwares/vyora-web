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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('return_days')->default(7)->after('is_returnable');
            $table->boolean('is_exchangeable')->default(true)->after('return_days');
            $table->integer('exchange_days')->default(7)->after('is_exchangeable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['return_days', 'is_exchangeable', 'exchange_days']);
        });
    }
};
