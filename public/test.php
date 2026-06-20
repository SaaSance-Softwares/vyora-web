<?php

use App\Models\Product;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$response = $kernel->handle(
    $request = Request::capture()
);

$product = Product::with(['categories', 'categoryMasterImages'])->find(21);
echo json_encode([
    'categories' => $product->categories->toArray(),
    'images' => $product->categoryMasterImages->toArray(),
], JSON_PRETTY_PRINT);
