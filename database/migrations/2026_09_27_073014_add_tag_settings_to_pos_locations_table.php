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
            $table->string('tag_printer_size')->nullable()->default('48x72');
            $table->string('tag_custom_size_w')->nullable();
            $table->string('tag_custom_size_h')->nullable();
            $table->string('tag_margin_top')->nullable()->default('0');
            $table->string('tag_margin_bottom')->nullable()->default('0');
            $table->string('tag_barcode_type')->nullable()->default('QR');
            $table->string('tag_template')->nullable()->default('default');
            $table->string('tag_main_logo')->nullable();
            $table->string('tag_icon')->nullable();
            $table->string('tag_washing_instruction')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_locations', function (Blueprint $table) {
            $table->dropColumn([
                'tag_printer_size',
                'tag_custom_size_w',
                'tag_custom_size_h',
                'tag_margin_top',
                'tag_margin_bottom',
                'tag_barcode_type',
                'tag_template',
                'tag_main_logo',
                'tag_icon',
                'tag_washing_instruction'
            ]);
        });
    }
};
