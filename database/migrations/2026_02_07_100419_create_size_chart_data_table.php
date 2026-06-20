<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('size_chart_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('size_chart_id')->constrained('size_charts')->onDelete('cascade');
            $table->json('table_data');
            $table->enum('unit', ['inches', 'cm'])->default('inches');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('size_chart_data');
    }
};
