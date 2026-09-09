<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Size;
use App\Models\Fit;
use App\Models\Fabric;
use App\Models\SizeChart;
use App\Models\Sku;
use App\Models\ThemeSetting;
use App\Models\DeliveryTimeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('skus')->withCount('orderItems as purchase_count')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhereHas('skus', function ($sq) use ($search) {
                      $sq->where('code', 'like', '%' . $search . '%');
                  });
            });
        }

        $products = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'low_stock' => Sku::where('stock', '<=', 5)->count(),
            'out_of_stock' => Sku::where('stock', 0)->count(),
        ];

                $sizeCharts = \App\Models\SizeChart::all();
        $fits = \App\Models\Fit::all();
        $fabrics = \App\Models\Fabric::all();
        $productTypes = \App\Models\ProductType::all();
        $categories = \App\Models\Category::whereNull('parent_id')->with('children.children')->get();
        $collections = \App\Models\Collection::all();
        $deliveryTimelines = \App\Models\DeliveryTimeline::all();
        $qikinkEnabled = \App\Models\ThemeSetting::where('group', 'integration.qikink')->where('key', 'enabled')->value('value') == '1';
        
        $taxRows = \App\Models\ThemeSetting::where('group', 'tax_shipping')->get()->keyBy('key');
        $taxes = json_decode($taxRows->get('taxes')?->value ?? '[{"id":"t1","name":"GST 5%","rate":5},{"id":"t2","name":"GST 18%","rate":18}]', true);

        return view('admin.products.index', compact('products', 'stats', 'sizeCharts', 'fits', 'fabrics', 'productTypes', 'categories', 'collections', 'deliveryTimelines', 'qikinkEnabled', 'taxes'));
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

    public function analytics(Product $product)
    {
        $product->loadMissing('orderItems.order');
        
        $viewCount = $product->view_count;
        $purchaseCount = $product->orderItems->count();
        
        // Count items in returned/cancelled orders
        $returnCount = $product->orderItems->filter(function($item) {
            return in_array($item->order->status, ['returned', 'cancelled', 'refunded']);
        })->count();

        // Revenue (sum of total for items in completed/delivered/shipped orders)
        $revenue = $product->orderItems->filter(function($item) {
            return in_array($item->order->status, ['completed', 'delivered', 'shipped']);
        })->sum('total');

        $conversionRate = $viewCount > 0 ? round(($purchaseCount / $viewCount) * 100, 2) : 0;
        $returnRate = $purchaseCount > 0 ? round(($returnCount / $purchaseCount) * 100, 2) : 0;

        return view('admin.products.analytics', compact(
            'product', 'viewCount', 'purchaseCount', 'returnCount', 
            'revenue', 'conversionRate', 'returnRate'
        ));
    }

    public function create()
    {
        $categories = Category::whereNull('parent_id')->with('children.children')->get();
        $collections = Collection::where('is_active', true)->get();
        $productTypes = ProductType::all();
        $sizeCharts = SizeChart::where('is_active', true)->orderBy('name')->get();
        $colors = Color::orderBy('name')->get();
        $sizes = Size::orderBy('name')->get();
        $fits = Fit::orderBy('name')->get();
        $fabrics = Fabric::orderBy('name')->get();

        $taxRows = ThemeSetting::where('group', 'tax_shipping')->get()->keyBy('key');
        $taxes = json_decode($taxRows->get('taxes')?->value ?? '[{"id":"t1","name":"GST 5%","rate":5},{"id":"t2","name":"GST 18%","rate":18}]', true);
        
        $deliveryTimelines = DeliveryTimeline::orderBy('created_at', 'desc')->get();

        return view('admin.products.create', compact('categories', 'collections', 'productTypes', 'sizeCharts', 'colors', 'sizes', 'fits', 'fabrics', 'taxes', 'deliveryTimelines'));
    }

    public function store(Request $request)
    {
        // Enforce slug formatting
        $request->merge([
            'slug' => Str::slug($request->slug ?? $request->name),
        ]);

        $request->strictValidate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug',
            'brand_name' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:5000',
            'use_case' => 'nullable|string|max:255',
            'long_description' => 'nullable|string|max:5000',
            'product_type_id' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:5000',
            'seo_keywords' => 'nullable|string|max:1000',
            'is_returnable' => 'boolean',
            'on_sale' => 'boolean',
            'use_qikink' => 'boolean',
            'tax_class' => 'nullable|string|max:255',
            'delivery_timeline_id' => 'nullable|exists:delivery_timelines,id',
            'fit_id' => 'nullable|exists:fits,id',
            'fabric_id' => 'nullable|exists:fabrics,id',
            'categories' => 'nullable|array',
            'categories.*' => 'string|max:255',
            'collections' => 'nullable|array',
            'collections.*' => 'string|max:255',
            'size_chart_id' => 'nullable|string|max:255',
            'new_skus' => 'nullable|array|max:100',
            'new_skus.*' => 'array',
            'new_skus.*.code' => 'nullable|string|max:255',
            'new_skus.*.price' => 'nullable|numeric',
            'new_skus.*.mrp' => 'nullable|numeric',
            'new_skus.*.stock' => 'nullable|integer',
            'new_skus.*.color_id' => 'nullable|string|max:255',
            'new_skus.*.size' => 'nullable|string|max:255',
            'new_skus.*.design_sku' => 'nullable|string|max:255',
            'new_skus.*.product_sku' => 'nullable|string|max:255',
            'new_skus.*.weight' => 'nullable|numeric',
            'new_skus.*.width' => 'nullable|numeric',
            'new_skus.*.height' => 'nullable|numeric',
            'new_skus.*.length' => 'nullable|numeric',
            'redirect_tab' => 'nullable|string|max:255',
            'preview_image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Create product
        $data = [
            'name' => $request->name,
            'slug' => $request->slug,
            'brand_name' => $request->brand_name,
            'short_description' => $request->short_description,
            'use_case' => $request->use_case,
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
            'delivery_timeline_id' => $request->delivery_timeline_id,
            'fit_id' => $request->fit_id,
            'fabric_id' => $request->fabric_id,
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
        
        $rootCategories = collect();
        foreach ($product->categories as $category) {
            $current = $category;
            while ($current->parent_id != null) {
                $current = $current->parent;
            }
            if (!$rootCategories->contains('id', $current->id)) {
                $rootCategories->push($current);
            }
        }
        $productCategories = $rootCategories;

        // Get unique colors from product SKUs
        $productColors = $product->skus->pluck('color')->unique('id')->filter();

        // Group images by color_id
        $mediaByColor = $product->images->groupBy('color_id');

        // Get all active size charts
        $sizeCharts = SizeChart::where('is_active', true)->orderBy('name')->get();

        // Get available attributes for new variants
        $colors = Color::orderBy('name')->get();
        $sizes = Size::orderBy('name')->get();
        $fits = Fit::orderBy('name')->get();
        $fabrics = Fabric::orderBy('name')->get();

        $taxRows = ThemeSetting::where('group', 'tax_shipping')->get()->keyBy('key');
        $taxes = json_decode($taxRows->get('taxes')?->value ?? '[{"id":"t1","name":"GST 5%","rate":5},{"id":"t2","name":"GST 18%","rate":18}]', true);
        
        $deliveryTimelines = DeliveryTimeline::orderBy('created_at', 'desc')->get();

        return view('admin.products.edit', compact('product', 'categories', 'collections', 'productTypes', 'productColors', 'mediaByColor', 'sizeCharts', 'colors', 'sizes', 'fits', 'fabrics', 'productCategories', 'taxes', 'deliveryTimelines'));
    }

    public function update(Request $request, Product $product)
    {
        // Enforce slug formatting
        $request->merge([
            'slug' => Str::slug($request->slug),
        ]);

        $request->strictValidate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,'.$product->id,
            'brand_name' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:5000',
            'use_case' => 'nullable|string|max:255',
            'long_description' => 'nullable|string|max:5000',
            'product_type_id' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:5000',
            'seo_keywords' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'is_returnable' => 'boolean',
            'on_sale' => 'boolean',
            'use_qikink' => 'boolean',
            'tax_class' => 'nullable|string|max:255',
            'delivery_timeline_id' => 'nullable|exists:delivery_timelines,id',
            'fit_id' => 'nullable|exists:fits,id',
            'fabric_id' => 'nullable|exists:fabrics,id',
            'categories' => 'nullable|array',
            'categories.*' => 'string|max:255',
            'collections' => 'nullable|array',
            'collections.*' => 'string|max:255',
            'size_chart_id' => 'nullable|string|max:255',
            'skus' => 'nullable|array|max:100',
            'skus.*' => 'array',
            'skus.*.code' => 'required|string|max:255',
            'skus.*.price' => 'required|numeric|min:0|max:9999999',
            'skus.*.mrp' => 'nullable|numeric|min:0|max:9999999',
            'skus.*.stock' => 'required|integer|min:0|max:999999',
            'skus.*.design_sku' => 'nullable|string|max:255',
            'skus.*.product_sku' => 'nullable|string|max:255',
            'skus.*.weight' => 'nullable|numeric',
            'skus.*.width' => 'nullable|numeric',
            'skus.*.height' => 'nullable|numeric',
            'skus.*.length' => 'nullable|numeric',
            'new_skus' => 'nullable|array|max:100',
            'new_skus.*' => 'array',
            'new_skus.*.code' => 'nullable|string|max:255',
            'new_skus.*.price' => 'nullable|numeric',
            'new_skus.*.mrp' => 'nullable|numeric',
            'new_skus.*.stock' => 'nullable|integer',
            'new_skus.*.color_id' => 'nullable|string|max:255',
            'new_skus.*.size' => 'nullable|string|max:255',
            'new_skus.*.design_sku' => 'nullable|string|max:255',
            'new_skus.*.product_sku' => 'nullable|string|max:255',
            'new_skus.*.weight' => 'nullable|numeric',
            'new_skus.*.width' => 'nullable|numeric',
            'new_skus.*.height' => 'nullable|numeric',
            'new_skus.*.length' => 'nullable|numeric',
            'redirect_tab' => 'nullable|string|max:255',
            'preview_image' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Update basic product details
        // Update basic product details
        $data = [
            'name' => $request->name,
            'slug' => $request->slug,
            'brand_name' => $request->brand_name,
            'short_description' => $request->short_description,
            'use_case' => $request->use_case,
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
            'delivery_timeline_id' => $request->delivery_timeline_id,
            'fit_id' => $request->fit_id,
            'fabric_id' => $request->fabric_id,
        ];

        // Handle Master Image Upload
        if ($request->hasFile('preview_image')) {

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

    public function export()
    {
        $products = Product::with('skus')->withCount('orderItems as purchase_count')->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products_export_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'ID', 'Name', 'Slug', 'Brand', 'Status', 
                'Total Stock', 'Min Price', 'Max Price', 
                'Views', 'Purchases', 'Created At'
            ]);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->slug,
                    $product->brand_name,
                    $product->is_active ? 'Active' : 'Draft',
                    $product->skus->sum('stock'),
                    $product->skus->isNotEmpty() ? $product->skus->min('price') : 0,
                    $product->skus->isNotEmpty() ? $product->skus->max('price') : 0,
                    $product->view_count,
                    $product->purchase_count,
                    $product->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|string',
        ]);

        // Convert comma-separated string to array
        $productIds = array_filter(explode(',', $request->product_ids));
        
        if (empty($productIds)) {
            return back()->with('error', 'No products selected.');
        }

        $updateData = [];

        $fields = ['fit_id', 'fabric_id', 'product_type_id', 'delivery_timeline_id', 'tax_class'];
        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $updateData[$field] = $request->$field;
            }
        }
        
        $boolFields = ['is_active', 'is_returnable', 'on_sale', 'use_qikink'];
        foreach ($boolFields as $field) {
            if ($request->filled($field) && $request->$field !== 'leave') {
                $updateData[$field] = $request->$field === '1';
            }
        }

        if (!empty($updateData)) {
            Product::whereIn('id', $productIds)->update($updateData);
        }

        $products = Product::whereIn('id', $productIds)->get();

        if ($request->filled('size_chart_id')) {
            foreach ($products as $product) {
                $product->sizeChart()->sync([$request->size_chart_id]);
            }
        }

        if ($request->filled('categories')) {
            foreach ($products as $product) {
                // If they check multiple checkboxes, it replaces the product's categories
                $product->categories()->sync($request->categories);
            }
        }

        if ($request->filled('collections')) {
            foreach ($products as $product) {
                $product->collections()->sync($request->collections);
            }
        }

        return back()->with('success', count($productIds) . ' products updated successfully.');
    }
}