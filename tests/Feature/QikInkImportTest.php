<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class QikInkImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_qikink_import_flow()
    {
        // 1. Create Admin User
        // 1. Create Admin User
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        \Illuminate\Support\Facades\File::put(storage_path('installed'), 'installed');

        // 2. Create Fake CSV
        $header = "Item name,Variant,Design SKU,Product SKU,Store SKU,Selling price,Product price";
        $row1 = "Test T-Shirt,Black - L,DES-001,PROD-001,SKU-TEST-BLK-L,999,500";

        $content = "$header\n$row1";
        $file = UploadedFile::fake()->createWithContent('test_import.csv', $content);

        // 3. Post to Endpoint
        $response = $this->actingAs($user)->post(route('admin.upload.store'), [
            'file' => $file,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        // 4. Verify Database
        $this->assertDatabaseHas('products', [
            'slug' => 'test-t-shirt',
        ]);

        $this->assertDatabaseHas('skus', [
            'code' => 'SKU-TEST-BLK-L',
            'design_sku' => 'DES-001',
            'stock' => 100
        ]);

        $this->assertDatabaseHas('attributes', ['name' => 'Color']);
        $this->assertDatabaseHas('attributes', ['name' => 'Size']);

        // Cleanup if needed (optional since we verified functionality)
        // User::where('id', $user->id)->delete();
        // \App\Models\Product::where('slug', 'test-t-shirt')->delete();
    }
}
