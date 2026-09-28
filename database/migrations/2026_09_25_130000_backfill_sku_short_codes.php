<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Sku;

return new class extends Migration
{
    public function up(): void
    {
        $skus = Sku::whereNull('short_code')->orWhere('short_code', '')->get();
        foreach ($skus as $sku) {
            do {
                $short = 'SKU-' . mt_rand(10000000, 99999999);
            } while (Sku::where('short_code', $short)->exists());
            
            $sku->short_code = $short;
            $sku->save();
        }
    }

    public function down(): void
    {
        // Cannot reliably reverse this.
    }
};
