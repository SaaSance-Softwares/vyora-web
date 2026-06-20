<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_cards', function (Blueprint $table) {
            // Link each issued card back to the template it was purchased from
            $table->unsignedBigInteger('template_id')->nullable()->after('id');
            $table->foreign('template_id')->references('id')->on('gift_card_templates')->nullOnDelete();

            // Share token – random string that lets anyone claim/view the card via a URL
            $table->string('share_token', 64)->nullable()->unique()->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropColumn(['template_id', 'share_token']);
        });
    }
};
