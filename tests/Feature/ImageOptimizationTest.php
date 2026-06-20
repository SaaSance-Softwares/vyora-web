<?php

namespace Tests\Feature;

use App\Services\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageOptimizationTest extends TestCase
{
    public function test_it_converts_jpg_to_webp()
    {
        Storage::fake('public');

        // Create a fake JPG image
        $file = UploadedFile::fake()->image('test_product.jpg', 600, 600);

        $service = new ImageOptimizer();
        $path = $service->storeAndOptimize($file, 'products');

        // Assert path ends in .webp
        $this->assertStringEndsWith('.webp', $path);

        // Assert file exists in storage
        Storage::disk('public')->assertExists($path);

        // Assert original filename (jpg) does NOT exist in that folder
        // (Note: UploadedFile::fake creates a temp file, which ImageOptimizer process reads.
        // It shouldn't put the original jpg in 'products' anyway.)
        Storage::disk('public')->assertMissing('products/test_product.jpg');
    }
}
