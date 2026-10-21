<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('return_requests')) {
            Schema::table('return_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('return_requests', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('comments');
                }
                if (!Schema::hasColumn('return_requests', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('approved_at');
                }
                if (!Schema::hasColumn('return_requests', 'received_at')) {
                    $table->timestamp('received_at')->nullable()->after('rejected_at');
                }
                if (!Schema::hasColumn('return_requests', 'completed_at')) {
                    $table->timestamp('completed_at')->nullable()->after('received_at');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('return_requests', function (Blueprint $table) {
            $table->dropColumn(['approved_at', 'rejected_at', 'received_at', 'completed_at']);
        });
    }
};
