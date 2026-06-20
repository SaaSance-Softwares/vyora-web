<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Size;
use App\Models\SizeChart;
use App\Models\Sku;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('skus')->latest()->paginate(10);

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'low_stock' => Sku::where('stock', '<=', 5)->count(),
            'out_of_stock' => Sku::where('stock', 0)->count(),
        ];

        return view('admin.products.index', compact('products', 'stats'));
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        $products = Product::where('name', 'like', "%{$query}%")
            ->select('id', 'name', 'preview_image')
            ->limit(20)
            ->get();

        // Transform image URL
        $products->transform(function ($product) {
            $product->image_url = $product->preview_image ? '/'.$product->preview_image : null;

            return $product;
        });

        return response()->json($products);
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->with('children.children')->get();
        $collections = Collection::where('is_active', true)->get();
        $productTypes = ProductType::all();
        $sizeCharts = SizeChart::where('is_active', true)->orderBy('name')->get();
        $colors = Color::orderBy('name')->get();
        $sizes = Size::orderBy('name')->get();

        $taxRows = ThemeSetting::where('group', 'tax_shipping')->get()->keyBy('key');
        $taxes = json_decode($taxRows->get('taxes')?->value ?? '[{"id":"t1","name":"GST 5%","rate":5},{"id":"t2","name":"GST 18%","rate":18}]', true);

        return view('admin.products.create', compact('categories', 'collections', 'productTypes', 'sizeCharts', 'colors', 'sizes', 'taxes'));
    }

    public function store(Request $request)
    {
        // Enforce slug formatting
        $request->merge([
            'slug' => Str::slug($request->slug ?? $request->name),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug',
        ]);

        // Create product
        $data = [
            'name' => $request->name,
            'slug' => $request->slug,
            'brand_name' => $request->brand_name,
            'short_description' => $request->short_description,
            'long_description' => $request->long_description,
            'product_type_id' => $request->product_type_id,
            'seo_title' => $request->seo_title,
            'seo_description' => $request->seo_description,
            'seo_keywords' => $request->seo_keywords,
            'is_active' => false, // Products are inactive by default until admin activates
            'is_returnable' => $request->has('is_returnable'),
            'on_sale' => $request->has('on_sale'),
            'use_qikink' => $request->has('use_qikink'),
            'tax_class' => $request->tax_class,
        ];

        // Handle preview image upload
        if ($request->hasFile('preview_image')) {
            $file = $request->file('preview_image');
            $fileName = time().'_'.$file->getClientOriginalName();
            $relativePath = 'storage/products/preview';
            $destinationPath = public_path($relativePath);
            if (! file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $fileName);
            $data['preview_image'] = "{$relativePath}/{$fileName}";
        }

        $product = Product::create($data);

        // Sync associations
        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        }

        if ($request->has('collections')) {
            $product->collections()->sync($request->collections);
        }

        if ($request->filled('size_chart_id')) {
            $product->sizeChart()->sync([$request->size_chart_id]);
        }

        // Create new SKUs if provided
        if ($request->has('new_skus')) {
            foreach ($request->new_skus as $newSku) {
                if (! empty($newSku['code']) && isset($newSku['stock'])) {
                    $sizeId = null;
                    if (! empty($newSku['size'])) {
                        $size = Size::firstOrCreate(
                            ['name' => trim($newSku['size'])],
                            ['code' => strtoupper(trim($newSku['size']))]
                        );
                        $sizeId = $size->id;
                    }

                    $product->skus()->create([
                        'code' => $newSku['code'],
                        'price' => $newSku['price'] ?: 0,
                        'mrp' => ! empty($newSku['mrp']) ? $newSku['mrp'] : null,
                        'stock' => $newSku['stock'],
                        'color_id' => $newSku['color_id'] ?: null,
                        'size_id' => $sizeId,
                    ]);
                }
            }
        }

        $tab = $request->input('redirect_tab', 'info');

        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Product created successfully')
            ->withFragment($tab);
    }

    public function edit(Product $product)
    {
        $product->load([
            'skus.color',
            'skus.size',
            'categories',
            'collections',
            'images' => function ($query) {
                $query->orderBy('sort_order', 'asc');
            },
            'images.color',
            'productType',
            'categoryMasterImages',
            'shortlinks',
        ]);

        $categories = Category::whereNull('parent_id')->with('children.children')->get();
        $collections = Collection::where('is_active', true)->get();
        $productTypes = ProductType::all();

        $productParentCategories = $product->categories->whereNull('parent_id');

        // Get unique colors from product SKUs
        $productColors = $product->skus->pluck('color')->unique('id')->filter();

        // Group images by color_id
        $mediaByColor = $product->images->groupBy('color_id');

        // Get all active size charts
        $sizeCharts = SizeChart::where('is_active', true)->orderBy('name')->get();

        // Get available attributes for new variants
        $colors = Color::orderBy('name')->get();
        $sizes = Size::orderBy('name')->get();

        $taxRows = ThemeSetting::where('group', 'tax_shipping')->get()->keyBy('key');
        $taxes = json_decode($taxRows->get('taxes')?->value ?? '[{"id":"t1","name":"GST 5%","rate":5},{"id":"t2","name":"GST 18%","rate":18}]', true);

        return view('admin.products.edit', compact('product', 'categories', 'collections', 'productTypes', 'productColors', 'mediaByColor', 'sizeCharts', 'colors', 'sizes', 'productParentCategories', 'taxes'));
    }

    public function update(Request $request, Product $product)
    {
        // Enforce slug formatting
        $request->merge([
            'slug' => Str::slug($request->slug),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,'.$product->id,
            'skus' => 'nullable|array',
            'skus.*.code' => 'required|string|max:255',
            'skus.*.price' => 'required|numeric|min:0',
            'skus.*.mrp' => 'nullable|numeric|min:0',
            'skus.*.stock' => 'required|integer|min:0',
        ]);

        // Update basic product details
        // Update basic product details
        $data = [
            'name' => $request->name,
            'slug' => $request->slug,
            'brand_name' => $request->brand_name,
            'short_description' => $request->short_description,
            'long_description' => $request->long_description,
            'product_type_id' => $request->product_type_id,
            'seo_title' => $request->seo_title,
            'seo_description' => $request->seo_description,
            'seo_keywords' => $request->seo_keywords,
            'is_active' => $request->has('is_active'),
            'is_returnable' => $request->has('is_returnable'),
            'on_sale' => $request->has('on_sale'),
            'use_qikink' => $request->has('use_qikink'),
            'tax_class' => $request->tax_class,
        ];

        // Handle Master Image Upload
        if ($request->hasFile('preview_image')) {
            $request->validate([
                'preview_image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            // Delete old image if exists
            if ($product->preview_image) {
                $oldPath = public_path("/{$product->preview_image}");
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $file = $request->file('preview_image');
            $fileName = time().'_'.$file->getClientOriginalName();
            $relativePath = 'storage/products/preview';
            $destinationPath = public_path($relativePath);
            if (! file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $fileName);
            $data['preview_image'] = "{$relativePath}/{$fileName}";
        } elseif ($request->file('preview_image') && ! $request->file('preview_image')->isValid()) {
            return redirect()->back()
                ->withInput()
                ->withFragment($request->input('redirect_tab', 'media'))
                ->withErrors(['preview_image' => 'Upload failed: '.$request->file('preview_image')->getErrorMessage()]);
        }

        $product->update($data);

        // Sync Associations
        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        } else {
            $product->categories()->detach();
        }

        if ($request->has('collections')) {
            $product->collections()->sync($request->collections);
        } else {
            $product->collections()->detach();
        }

        // Sync Size Chart (one product can have only one size chart)
        if ($request->filled('size_chart_id')) {
            $product->sizeChart()->sync([$request->size_chart_id]);
        } else {
            $product->sizeChart()->detach();
        }

        // Update SKUs
        if ($request->has('skus')) {
            foreach ($request->skus as $skuId => $skuData) {
                // Ensure we only update SKUs belonging to this product
                $sku = $product->skus()->find($skuId);
                if ($sku) {
                    $skuDataToUpdate = [
                        'code' => $skuData['code'],
                        'price' => $skuData['price'],
                        'stock' => $skuData['stock'],
                        'design_sku' => $skuData['design_sku'] ?? null,
                        'product_sku' => $skuData['product_sku'] ?? null,
                        'weight' => isset($skuData['weight']) && $skuData['weight'] !== '' ? $skuData['weight'] : null,
                        'width' => isset($skuData['width']) && $skuData['width'] !== '' ? $skuData['width'] : null,
                        'height' => isset($skuData['height']) && $skuData['height'] !== '' ? $skuData['height'] : null,
                        'length' => isset($skuData['length']) && $skuData['length'] !== '' ? $skuData['length'] : null,
                    ];
                    if (array_key_exists('mrp', $skuData)) {
                        $skuDataToUpdate['mrp'] = $skuData['mrp'] !== null && $skuData['mrp'] !== '' ? $skuData['mrp'] : null;
                    }
                    $sku->update($skuDataToUpdate);
                }
            }
        }

        // Create new SKUs
        if ($request->has('new_skus')) {
            foreach ($request->new_skus as $newSku) {
                // Validate basic required fields for a new SKU
                if (! empty($newSku['code']) && isset($newSku['stock'])) {
                    // Handle manual size entry - find or create the size
                    $sizeId = null;
                    if (! empty($newSku['size'])) {
                        $size = Size::firstOrCreate(
                            ['name' => trim($newSku['size'])],
                            ['code' => strtoupper(trim($newSku['size']))]
                        );
                        $sizeId = $size->id;
                    }

                    $product->skus()->create([
                        'code' => $newSku['code'],
                        'price' => $newSku['price'] ?: 0,
                        'mrp' => ! empty($newSku['mrp']) ? $newSku['mrp'] : null,
                        'stock' => $newSku['stock'],
                        'color_id' => $newSku['color_id'] ?: null,
                        'size_id' => $sizeId,
                        'design_sku' => $newSku['design_sku'] ?? null,
                        'product_sku' => $newSku['product_sku'] ?? null,
                        'weight' => isset($newSku['weight']) && $newSku['weight'] !== '' ? $newSku['weight'] : null,
                        'width' => isset($newSku['width']) && $newSku['width'] !== '' ? $newSku['width'] : null,
                        'height' => isset($newSku['height']) && $newSku['height'] !== '' ? $newSku['height'] : null,
                        'length' => isset($newSku['length']) && $newSku['length'] !== '' ? $newSku['length'] : null,
                    ]);
                }
            }
        }

        $tab = $request->input('redirect_tab', 'info');

        return redirect()->route('admin.products.edit', $product)
            ->with('success', 'Product updated successfully')
            ->withFragment($tab);
    }

    public function destroy(Product $product)
    {
        // Check if product has any orders
        // Assuming relationship or direct query. Let's use direct query to be safe if relation is missing.
        $hasOrders = DB::table('order_items')->where('product_id', $product->id)->exists();

        if ($hasOrders) {
            return back()->with('error', 'Cannot delete product: There are existing purchases associated with it.');
        }

        // Proceed to delete
        // Detach relations first if needed, but cascade usually handles it.
        // Explicitly ensuring SKUs are handled if cascade is missing, but migration said cascade.
        // Let's just delete the product.
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}
