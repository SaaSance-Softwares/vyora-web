<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'amount_cash')) {
                $table->decimal('amount_cash', 10, 2)->default(0)->after('amount_paid');
            }
            if (!Schema::hasColumn('orders', 'amount_upi')) {
                $table->decimal('amount_upi', 10, 2)->default(0)->after('amount_cash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['amount_cash', 'amount_upi']);
        });
    }
};
