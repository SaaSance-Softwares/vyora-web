<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Size;
use App\Models\Sku;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GeneralProductImporter
{
    /**
     * Process a single row from the General CSV.
     */
    public function importRow(array $row, int $rowNumber = 0)
    {
        return DB::transaction(function () use ($row) {
            // 1. Resolve Product Type (like T-shirt, hoodie)
            // 'Product type' column, 'HSN', 'Tax On Product'
            $productType = $this->getOrCreateProductType(
                $row['Product type'] ?? 'General',
                $row['HSN'] ?? null,
                $row['Tax On Product'] ?? 0
            );

            // 2. Create/Update Product
            $productName = $row['Product name'] ?? 'Unknown Product';
            $slug = Str::slug($productName);

            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $productName,
                    'short_description' => $row['Short description'] ?? null,
                    'long_description' => $row['Long description'] ?? null,
                    'brand_name' => $row['Brand name'] ?? null,
                    'product_type_id' => $productType->id,

                    // SEO
                    'seo_title' => $row['Seo title'] ?? $productName,
                    'seo_description' => $row['Seo description'] ?? null,
                    'seo_keywords' => $row['Seo keyword'] ?? null,
                    'tags' => json_encode(explode(',', $row['Product tag'] ?? '')),

                    // Flags - Products are inactive by default until admin reviews
                    'is_active' => false,
                    'is_returnable' => strtolower($row['Qualified for return'] ?? 'yes') === 'yes',
                    'on_sale' => strtolower($row['On sale batch'] ?? 'no') === 'yes',
                ]
            );

            // 3. Categories (Multiple, comma separated?)
            // "Category (One Product can be listed in multiple category)"
            if (! empty($row['Category'])) {
                $categoryNames = explode(',', $row['Category']);
                $categoryIds = [];
                foreach ($categoryNames as $catName) {
                    $catName = trim($catName);
                    if ($catName) {
                        $cat = Category::firstOrCreate(
                            ['name' => $catName],
                            ['slug' => Str::slug($catName)]
                        );
                        $categoryIds[] = $cat->id;
                    }
                }
                $product->categories()->sync($categoryIds);
            }

            // 4. Get Color and Size IDs
            // Support both "Attribute Size"/"Attribute Color" and "Size"/"Color" column names
            $colorId = null;
            $sizeId = null;

            $sizeValue = $row['Attribute Size'] ?? $row['Size'] ?? null;
            if (! empty($sizeValue)) {
                $val = trim($sizeValue);
                $code = strtoupper($val);

                $size = Size::where('code', $code)
                    ->orWhere('name', $val)
                    ->first();

                if (! $size) {
                    $size = Size::create([
                        'name' => $val,
                        'code' => $code,
                    ]);
                }
                $sizeId = $size->id;
            }

            $colorValue = $row['Attribute Color'] ?? $row['Color'] ?? null;
            if (! empty($colorValue)) {
                $color = Color::firstOrCreate(
                    ['name' => trim($colorValue)],
                    ['hex_code' => '#000000'] // Default hex code
                );
                $colorId = $color->id;
            }

            // 5. SKU Creation - Validate Required Fields
            $skuCode = trim($row['SKU'] ?? '');
            $sellingPrice = trim($row['Selling price'] ?? '');
            $labelPrice = trim($row['Label price'] ?? '');

            // Build error message for missing fields
            $missingFields = [];
            if (empty($skuCode)) {
                $missingFields[] = 'SKU';
            }
            if (empty($sellingPrice)) {
                $missingFields[] = 'Selling price';
            }
            if (empty($labelPrice)) {
                $missingFields[] = 'Label price';
            }

            // Throw exception if any required field is missing
            if (! empty($missingFields)) {
                throw new \Exception(
                    'Missing required field(s): '.implode(', ', $missingFields).'. '.
                    'Please check your CSV and ensure all required fields are filled.'
                );
            }

            // Check if SKU already exists in database
            $existingSku = Sku::where('code', $skuCode)->first();
            if ($existingSku) {
                throw new \Exception(
                    'SKU "'.$skuCode.'" already exists in database. '.
                    'Please use a unique SKU code or update the existing product instead.'
                );
            }

            $sku = Sku::create(
                [
                    'code' => $skuCode,
                    'product_id' => $product->id,
                    'price' => $this->parsePrice($sellingPrice),
                    'mrp' => $this->parsePrice($labelPrice),
                    'stock' => intval($row['Stock per sku'] ?? 0),
                    'min_order_quantity' => intval($row['Minimum order'] ?? 1),
                    'max_order_quantity' => $row['Maximum order'] ? intval($row['Maximum order']) : null,

                    // QikInk fields
                    'design_sku' => $row['Design SKU'] ?? null,
                    'product_sku' => $row['Product SKU'] ?? null,

                    // Link Color and Size directly
                    'color_id' => $colorId,
                    'size_id' => $sizeId,

                    // Dimensions - convert empty strings to null for decimal fields
                    'weight' => ! empty($row['Package weight']) ? floatval($row['Package weight']) : null,
                    'width' => ! empty($row['Package width']) ? floatval($row['Package width']) : null,
                    'height' => ! empty($row['Package height']) ? floatval($row['Package height']) : null,
                    'length' => ! empty($row['Package length']) ? floatval($row['Package length']) : null,
                ]
            );

            return $sku;
        });
    }

    private function getOrCreateProductType($name, $hsn, $tax)
    {
        // If HSN code is provided, try to find existing ProductType by HSN first
        if (! empty($hsn)) {
            $existingByHsn = ProductType::where('hsn_code', $hsn)->first();
            if ($existingByHsn) {
                return $existingByHsn;
            }
        }

        // Otherwise, find or create by name
        return ProductType::firstOrCreate(
            ['name' => $name],
            [
                'hsn_code' => $hsn,
                'tax_percentage' => floatval(str_replace('%', '', $tax ?? 0)),
            ]
        );
    }

    private function getOrCreateAttributeValue($attrName, $value)
    {
        $attribute = Attribute::firstOrCreate(['name' => $attrName], ['slug' => Str::slug($attrName)]);

        return AttributeValue::firstOrCreate(
            [
                'attribute_id' => $attribute->id,
                'value' => $value,
            ],
            [
                'code' => Str::upper(Str::slug($value)),
            ]
        );
    }

    private function parsePrice($price)
    {
        if (empty($price)) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9.]/', '', $price);

        return ! empty($cleaned) ? floatval($cleaned) : null;
    }
}
