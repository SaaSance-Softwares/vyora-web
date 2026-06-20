<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Size;
use App\Models\Sku;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QikInkImporter
{
    public function importRow(array $row, int $rowNumber = 0)
    {
        return DB::transaction(function () use ($row) {
            // 1. Resolve Product
            $productName = $row['Item name'] ?? 'Unknown Product';
            $slug = Str::slug($productName);

            $product = Product::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $productName,
                    'is_active' => false,
                    'product_type_id' => $this->getDefaultProductType(),
                ]
            );

            // 2. Parse Variant (Black - S)
            $variantString = $row['Variant'] ?? '';
            $parsed = $this->parseVariantString($variantString);

            // 3. Create/Update SKU
            $skuCode = $row['Store SKU'];

            $sku = Sku::updateOrCreate(
                ['code' => $skuCode],
                [
                    'product_id' => $product->id,
                    'design_sku' => $row['Design SKU'] ?? null,
                    'product_sku' => $row['Product SKU'] ?? null,
                    'price' => $this->parsePrice($row['Product price'] ?? 0),
                    'mrp' => $this->parsePrice($row['Product price'] ?? 0),
                    'cost_price' => $this->parsePrice($row['Selling price'] ?? 0),
                    'stock' => 100, // Default stock
                    'color_id' => $parsed['color_id'],
                    'size_id' => $parsed['size_id'],
                ]
            );

            // We no longer use the pivot table for Color/Size as per new requirement
            // But if we had other attributes, we would attach them here.

            return $sku;
        });
    }

    private function parseVariantString(string $variant): array
    {
        // Format: "Black - S" => Color: Black, Size: S
        $parts = explode('-', $variant);

        $colorId = null;
        $sizeId = null;

        if (count($parts) >= 2) {
            // "Black - S"
            $colorName = trim($parts[0]);
            $sizeName = trim($parts[1]); // "S"

            if ($colorName) {
                $colorId = $this->getOrCreateColor($colorName);
            }
            if ($sizeName) {
                $sizeId = $this->getOrCreateSize($sizeName);
            }
        } elseif (count($parts) == 1) {
            // "Black" -> Assume Color? Or Size?
            // User says "Attribute will split in Color and size", implying strict structure.
            // If only one part, it's ambiguous. Let's check if it matches a known Size format (S, M, L, XL, XXL) vs Color.
            $val = trim($parts[0]);
            if ($this->isLikelySize($val)) {
                $sizeId = $this->getOrCreateSize($val);
            } else {
                $colorId = $this->getOrCreateColor($val); // Default fallback to Color
            }
        }

        return ['color_id' => $colorId, 'size_id' => $sizeId];
    }

    private function getOrCreateColor($name)
    {
        if (empty(trim($name))) {
            return null;
        }

        $color = Color::firstOrCreate(
            ['name' => $name],
            ['hex_code' => '#000000']
        );

        return $color->id;
    }

    private function getOrCreateSize($name)
    {
        if (empty(trim($name))) {
            return null;
        }

        $val = trim($name);
        $code = strtoupper($val);

        $size = Size::where('code', $code)
            ->orWhere('name', $val)
            ->first();

        if ($size) {
            return $size->id;
        }

        $size = Size::create([
            'name' => $val,
            'code' => $code,
        ]);

        return $size->id;
    }

    private function isLikelySize($val)
    {
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL'];

        return in_array(strtoupper($val), $sizes);
    }

    private function getDefaultProductType()
    {
        return ProductType::firstOrCreate(['name' => 'General'], ['tax_percentage' => 18])->id;
    }

    private function parsePrice($price)
    {
        return preg_replace('/[^0-9.]/', '', $price);
    }
}
