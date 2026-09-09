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
        Schema::table('postal_codes', function (Blueprint $table) {
            $table->string('district')->nullable()->after('city');
            $table->string('city')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('postal_codes', function (Blueprint $table) {
            $table->dropColumn('district');
            $table->string('city')->nullable(false)->change();
        });
    }
};
