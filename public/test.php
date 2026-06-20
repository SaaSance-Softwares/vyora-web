<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$product = \App\Models\Product::with(['categories', 'categoryMasterImages'])->find(21);
echo json_encode([
    'categories' => $product->categories->toArray(),
    'images' => $product->categoryMasterImages->toArray(),
], JSON_PRETTY_PRINT);
