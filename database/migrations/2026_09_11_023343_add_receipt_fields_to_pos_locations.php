<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_locations', 'receipt_header')) {
                $table->text('receipt_header')->nullable();
            }
            if (!Schema::hasColumn('pos_locations', 'receipt_footer')) {
                $table->text('receipt_footer')->nullable();
            }
            if (!Schema::hasColumn('pos_locations', 'receipt_printer_size')) {
                $table->string('receipt_printer_size')->default('80mm');
            }
            if (!Schema::hasColumn('pos_locations', 'receipt_barcode_type')) {
                $table->string('receipt_barcode_type')->default('QR');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pos_locations', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('pos_locations', 'receipt_header')) $columns[] = 'receipt_header';
            if (Schema::hasColumn('pos_locations', 'receipt_footer')) $columns[] = 'receipt_footer';
            if (Schema::hasColumn('pos_locations', 'receipt_printer_size')) $columns[] = 'receipt_printer_size';
            if (Schema::hasColumn('pos_locations', 'receipt_barcode_type')) $columns[] = 'receipt_barcode_type';
            
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
