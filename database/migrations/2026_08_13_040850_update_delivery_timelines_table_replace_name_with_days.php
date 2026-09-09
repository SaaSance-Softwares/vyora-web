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
        Schema::table('delivery_timelines', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->integer('min_days')->after('id');
            $table->integer('max_days')->after('min_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_timelines', function (Blueprint $table) {
            $table->dropColumn(['min_days', 'max_days']);
            $table->string('name')->after('id');
        });
    }
};
