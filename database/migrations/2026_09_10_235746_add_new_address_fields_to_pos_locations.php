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
            if (!Schema::hasColumn('pos_locations', 'address_line_2')) {
                $table->string('address_line_2')->nullable()->after('address');
            }
            if (!Schema::hasColumn('pos_locations', 'district')) {
                $table->string('district')->nullable()->after('city');
            }
            if (!Schema::hasColumn('pos_locations', 'country')) {
                $table->string('country')->nullable()->after('state');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_locations', function (Blueprint $table) {
            $table->dropColumn(['address_line_2', 'district', 'country']);
        });
    }
};
