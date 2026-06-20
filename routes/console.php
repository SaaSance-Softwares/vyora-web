<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('debug:cats', function () {
    $product = \App\Models\Product::with(['categories', 'categoryMasterImages'])->find(21);
    $this->info(json_encode([
        'categories' => $product->categories->toArray(),
        'images' => $product->categoryMasterImages->toArray(),
    ], JSON_PRETTY_PRINT));
});
