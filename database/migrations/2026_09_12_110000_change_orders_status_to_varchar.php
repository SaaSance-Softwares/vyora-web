<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Modify the status column to be a string (VARCHAR) to support custom statuses
        // We use raw SQL because doctrine/dbal doesn't support modifying ENUMs directly easily in all versions.
        DB::statement("ALTER TABLE orders MODIFY status VARCHAR(100) NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending','processing','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending'");
    }
};
