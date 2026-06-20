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
        Schema::table('product_category_master_images', function (Blueprint $table) {
            $table->string('video_path')->nullable()->after('image_path');
            $table->string('image_path')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_category_master_images', function (Blueprint $table) {
            $table->dropColumn('video_path');
            $table->string('image_path')->nullable(false)->change();
        });
    }
};
