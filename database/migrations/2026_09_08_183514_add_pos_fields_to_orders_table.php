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
            if (!Schema::hasColumn('orders', 'pos_location_id')) {
                $table->unsignedBigInteger('pos_location_id')->nullable()->after('id');
                $table->foreign('pos_location_id')->references('id')->on('pos_locations')->onDelete('set null');
            }
            if (!Schema::hasColumn('orders', 'source')) {
                $table->string('source')->default('online')->after('pos_location_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['pos_location_id']);
            $table->dropColumn(['pos_location_id', 'source']);
        });
    }
};
