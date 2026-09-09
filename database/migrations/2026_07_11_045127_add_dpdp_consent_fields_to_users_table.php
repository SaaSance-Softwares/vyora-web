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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_consented_to_terms')->default(false);
            $table->boolean('has_consented_to_marketing')->default(false);
            $table->timestamp('consent_timestamp')->nullable();
            $table->string('consent_ip_address', 45)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'has_consented_to_terms',
                'has_consented_to_marketing',
                'consent_timestamp',
                'consent_ip_address',
            ]);
        });
    }
};
