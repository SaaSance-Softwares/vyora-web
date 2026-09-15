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
        Schema::table('pos_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_locations', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (!Schema::hasColumn('pos_locations', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('pos_locations', 'pincode')) {
                $table->string('pincode')->nullable()->after('state');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_locations', function (Blueprint $table) {
            $table->dropColumn(['city', 'state', 'pincode']);
        });
    }
};
