<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosMarketController extends Controller
{
    public function index()
    {
        $locations = DB::table('pos_locations')->get();
        return view('admin.pos-markets.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.pos-markets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'type' => 'required|string|in:store,temporary',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'address' => 'nullable|string',
            'address_line_2' => 'nullable|string',
            'pincode' => 'nullable|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'district' => 'nullable|string',
            'country' => 'nullable|string',
            'gst_number' => 'nullable|string|max:50',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'open_time' => 'nullable|date_format:H:i',
            'close_time' => 'nullable|date_format:H:i',
            'receipt_header' => 'nullable|string',
            'receipt_footer' => 'nullable|string',
            'receipt_printer_size' => 'nullable|string|in:58mm,80mm',
            'receipt_barcode_type' => 'nullable|string|in:QR,1D,None',
            'store_image' => 'nullable|image|max:2048',
            'map_link' => 'nullable|url',
        ]);

        $imagePath = null;
        if ($request->hasFile('store_image')) {
            $imagePath = $request->file('store_image')->store('pos_locations', 'public');
        }

        DB::table('pos_locations')->insert([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->slug ?? $request->name),
            'type' => $request->type,
            'is_active' => $request->boolean('is_active') ? 1 : 0,
            'show_in_store' => $request->boolean('show_in_store') ? 1 : 0,
            'map_link' => $request->map_link,
            'store_image' => $imagePath,
            'start_date' => $request->type === 'temporary' ? $request->start_date : null,
            'end_date' => $request->type === 'temporary' ? $request->end_date : null,
            'open_time' => $request->open_time,
            'close_time' => $request->close_time,
            'address' => $request->address,
            'address_line_2' => $request->address_line_2,
            'pincode' => $request->pincode,
            'state' => $request->state,
            'city' => $request->city,
            'district' => $request->district,
            'country' => $request->country,
            'gst_number' => $request->gst_number,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
            'receipt_header' => $request->receipt_header,
            'receipt_footer' => $request->receipt_footer,
            'receipt_printer_size' => $request->receipt_printer_size ?? '80mm',
            'receipt_barcode_type' => $request->receipt_barcode_type ?? 'QR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('admin.pos-markets.index')->with('success', 'Store created successfully.');
    }

    public function edit($slug)
    {
        $location = DB::table('pos_locations')->where('slug', $slug)->first();
        if (!$location) abort(404);
        
        $logoSetting = DB::table('theme_settings')->where('group', 'logos')->where('key', 'main_logo')->first();
        $mainLogoUrl = $logoSetting && $logoSetting->value ? asset($logoSetting->value) : null;
        
        return view('admin.pos-markets.edit', compact('location', 'mainLogoUrl'));
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'type' => 'required|string|in:store,temporary',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'address' => 'nullable|string',
            'address_line_2' => 'nullable|string',
            'pincode' => 'nullable|string',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'district' => 'nullable|string',
            'country' => 'nullable|string',
            'gst_number' => 'nullable|string|max:50',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'open_time' => 'nullable|date_format:H:i',
            'close_time' => 'nullable|date_format:H:i',
            'receipt_header' => 'nullable|string',
            'receipt_footer' => 'nullable|string',
            'receipt_printer_size' => 'nullable|string|in:58mm,80mm',
            'receipt_barcode_type' => 'nullable|string|in:QR,1D,None',
            'store_image' => 'nullable|image|max:2048',
            'map_link' => 'nullable|url',
        ]);

        $location = DB::table('pos_locations')->where('slug', $slug)->first();
        if (!$location) abort(404);

        $imagePath = $location->store_image;
        if ($request->hasFile('store_image')) {
            if ($imagePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($imagePath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('store_image')->store('pos_locations', 'public');
        }

        $tagMainLogo = $location->tag_main_logo ?? null;
        if ($request->hasFile('tag_main_logo')) {
            if ($tagMainLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($tagMainLogo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($tagMainLogo);
            }
            $tagMainLogo = $request->file('tag_main_logo')->store('pos_tags', 'public');
        }

        $tagIcon = $location->tag_icon ?? null;
        if ($request->hasFile('tag_icon')) {
            if ($tagIcon && \Illuminate\Support\Facades\Storage::disk('public')->exists($tagIcon)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($tagIcon);
            }
            $tagIcon = $request->file('tag_icon')->store('pos_tags', 'public');
        }

        $tagWashing = $location->tag_washing_instruction ?? null;
        if ($request->hasFile('tag_washing_instruction')) {
            if ($tagWashing && \Illuminate\Support\Facades\Storage::disk('public')->exists($tagWashing)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($tagWashing);
            }
            $tagWashing = $request->file('tag_washing_instruction')->store('pos_tags', 'public');
        }

        DB::table('pos_locations')->where('slug', $slug)->update([
            'name' => $request->name,
            'slug' => \Illuminate\Support\Str::slug($request->slug ?? $request->name),
            'type' => $request->type,
            'is_active' => $request->boolean('is_active') ? 1 : 0,
            'show_in_store' => $request->boolean('show_in_store') ? 1 : 0,
            'map_link' => $request->map_link,
            'store_image' => $imagePath,
            'start_date' => $request->type === 'temporary' ? $request->start_date : null,
            'end_date' => $request->type === 'temporary' ? $request->end_date : null,
            'open_time' => $request->open_time,
            'close_time' => $request->close_time,
            'address' => $request->address,
            'address_line_2' => $request->address_line_2,
            'pincode' => $request->pincode,
            'state' => $request->state,
            'city' => $request->city,
            'district' => $request->district,
            'country' => $request->country,
            'gst_number' => $request->gst_number,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
            'receipt_header' => $request->receipt_header,
            'receipt_footer' => $request->receipt_footer,
            'receipt_printer_size' => $request->receipt_printer_size ?? '80mm',
            'receipt_barcode_type' => $request->receipt_barcode_type ?? 'QR',
            'tag_printer_size' => $request->tag_printer_size ?? '48x72',
            'tag_custom_size_w' => $request->tag_custom_size_w,
            'tag_custom_size_h' => $request->tag_custom_size_h,
            'tag_margin_top' => $request->tag_margin_top ?? '0',
            'tag_margin_bottom' => $request->tag_margin_bottom ?? '0',
            'tag_hole_top_margin' => $request->tag_hole_top_margin ?? '8',
            'tag_barcode_type' => $request->tag_barcode_type ?? 'QR',
            'tag_template' => $request->tag_template ?? 'default',
            'tag_main_logo' => $tagMainLogo,
            'tag_icon' => $tagIcon,
            'tag_washing_instruction' => $tagWashing,
            'updated_at' => now(),
        ]);
        return back()->with('success', 'Store updated successfully.');
    }

    public function show($slug)
    {
        $market = DB::table('pos_locations')->where('slug', $slug)->first();
        if (!$market) abort(404);
        $id = $market->id;
        
        $assignedSkus = DB::table('pos_market_pricing')
            ->join('skus', 'pos_market_pricing.sku_id', '=', 'skus.id')
            ->join('products', 'skus.product_id', '=', 'products.id')
            ->leftJoin('fabrics', 'products.fabric_id', '=', 'fabrics.id')
            ->leftJoin('colors', 'skus.color_id', '=', 'colors.id')
            ->leftJoin('sizes', 'skus.size_id', '=', 'sizes.id')
            ->where('pos_market_pricing.pos_location_id', $id)
            ->select(
                'pos_market_pricing.override_price',
                'pos_market_pricing.stock',
                'skus.id as sku_id',
                'skus.code as barcode',
                'skus.short_code',
                DB::raw('COALESCE(skus.mrp, skus.price) as original_price'),
                'products.name',
                'products.preview_image',
                'fabrics.name as fabric_name',
                'skus.product_id',
                'skus.color_id',
                'colors.name as color_name',
                'sizes.name as size_name'
            )
            ->get();

        foreach ($assignedSkus as $sku) {
            $colorImage = DB::table('product_images')
                ->where('product_id', $sku->product_id)
                ->where('color_id', $sku->color_id)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->first();
            $path = $colorImage ? $colorImage->image_path : $sku->preview_image;
            if ($path) {
                if (str_starts_with($path, 'http')) {
                    $sku->image_url = $path;
                } else {
                    $cleanPath = ltrim($path, '/');
                    $sku->image_url = (str_starts_with($cleanPath, 'storage/') || str_starts_with($cleanPath, 'uploads/')) 
                        ? asset($cleanPath) 
                        : asset('storage/' . $cleanPath);
                }
            } else {
                $sku->image_url = null;
            }

            // Extract Chest and Length from Size Chart
            $sku->chest = '--';
            $sku->length = '--';
            $sku->chest_label = 'Chest';
            $sku->length_label = 'Length';
            
            $productSizeChart = DB::table('product_size_chart')->where('product_id', $sku->product_id)->first();
            if ($productSizeChart) {
                $chartData = DB::table('size_chart_data')->where('size_chart_id', $productSizeChart->size_chart_id)->first();
                if ($chartData && $chartData->table_data) {
                    $data = is_string($chartData->table_data) ? json_decode($chartData->table_data, true) : $chartData->table_data;
                    if (is_string($data)) $data = json_decode($data, true);
                    
                    $m_labels = [];
                    if (is_array($data) && isset($data['headers']) && is_array($data['headers'])) {
                        foreach ($data['headers'] as $header) {
                            $m_labels[] = $header;
                        }
                        if (count($m_labels) >= 1) $sku->chest_label = $m_labels[0];
                        if (count($m_labels) >= 2) $sku->length_label = $m_labels[1];
                    }
                    
                    if (is_array($data) && isset($data['rows']) && is_array($data['rows'])) {
                        foreach ($data['rows'] as $row) {
                            if (isset($row['size_code']) && strtolower(trim($row['size_code'])) === strtolower(trim($sku->size_name))) {
                                $measurements = array_change_key_case($row['measurements'] ?? [], CASE_LOWER);
                                
                                if (count($m_labels) >= 1) {
                                    $k1 = strtolower($m_labels[0]);
                                    $sku->chest = $measurements[$k1] ?? '--';
                                }
                                if (count($m_labels) >= 2) {
                                    $k2 = strtolower($m_labels[1]);
                                    $sku->length = $measurements[$k2] ?? '--';
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }

        return view('admin.pos-markets.show', compact('market', 'assignedSkus'));
    }

    public function searchProducts(Request $request, $slug)
    {
        $location = DB::table('pos_locations')->where('slug', $slug)->first();
        if (!$location) abort(404);
        $id = $location->id;
        $q = $request->q;
        $assignedSkuIds = DB::table('pos_market_pricing')->where('pos_location_id', $id)->pluck('sku_id')->toArray();
        
        $query = \App\Models\Product::with(['skus.color', 'skus.size', 'images'])
            ->where('is_active', true)
            ->whereHas('skus', function($q2) use ($assignedSkuIds) {
                if(!empty($assignedSkuIds)) {
                    $q2->whereNotIn('id', $assignedSkuIds);
                }
            });
            
        if ($q) {
            $query->where('name', 'like', '%' . $q . '%');
        }
        
        // Return max 50 products at a time to prevent UI lag, while still allowing searching the whole DB
        $productsRaw = $query->limit(50)->get();

        $availableProducts = $productsRaw->map(function($product) use ($assignedSkuIds) {
            $unassignedSkus = $product->skus->filter(fn($sku) => !in_array($sku->id, $assignedSkuIds));
            
            if ($unassignedSkus->isEmpty()) return null;

            $mappedSkus = $unassignedSkus->map(function($sku) use ($product) {
                $variantImageObj = $product->images->where('color_id', $sku->color_id)->first();
                
                $variantImageUrl = $product->image_url;
                if ($variantImageObj && $variantImageObj->image_path) {
                    $path = $variantImageObj->image_path;
                    if (str_starts_with($path, 'http')) {
                        $variantImageUrl = $path;
                    } else {
                        $cleanPath = ltrim($path, '/');
                        $variantImageUrl = (str_starts_with($cleanPath, 'storage/') || str_starts_with($cleanPath, 'uploads/')) 
                            ? asset($cleanPath) 
                            : asset('storage/'.$cleanPath);
                    }
                }

                return [
                    'id' => $sku->id,
                    'color_name' => $sku->color->name ?? 'Default',
                    'size_name' => $sku->size->name ?? 'Default',
                    'barcode' => $sku->code,
                    'short_code' => $sku->short_code,
                    'online_price' => $sku->price,
                    'image' => $variantImageUrl,
                ];
            })->values();

            return [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->image_url,
                'skus' => $mappedSkus
            ];
        })->filter()->values();

        return response()->json($availableProducts);
    }

    public function addProduct(Request $request, $slug)
    {
        $location = DB::table('pos_locations')->where('slug', $slug)->first();
        if (!$location) abort(404);
        $id = $location->id;
        $request->validate([
            'variants' => 'required|array',
            'variants.*.sku_id' => 'required|integer',
            'variants.*.override_price' => 'required|numeric',
            'variants.*.stock' => 'required|integer|min:0'
        ]);

        foreach($request->variants as $variant) {
            // Ensure the SKU has a short_code (backfill for imported products)
            $skuModel = \App\Models\Sku::find($variant['sku_id']);
            if ($skuModel && empty($skuModel->short_code)) {
                do {
                    $short = 'SKU-' . mt_rand(10000000, 99999999);
                } while (\App\Models\Sku::where('short_code', $short)->exists());
                $skuModel->short_code = $short;
                $skuModel->save();
            }

            DB::table('pos_market_pricing')->updateOrInsert(
                [
                    'pos_location_id' => $id,
                    'sku_id' => $variant['sku_id']
                ],
                [
                    'override_price' => $variant['override_price'],
                    'stock' => $variant['stock'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        return back()->with('success', count($request->variants) . ' product variants added to market.');
    }

    public function updateProduct(Request $request, $slug, $skuId)
    {
        $location = DB::table('pos_locations')->where('slug', $slug)->first();
        if (!$location) abort(404);
        $id = $location->id;
        $request->validate([
            'override_price' => 'required|numeric',
            'stock' => 'required|integer|min:0'
        ]);

        DB::table('pos_market_pricing')
            ->where('pos_location_id', $id)
            ->where('sku_id', $skuId)
            ->update([
                'override_price' => $request->override_price,
                'stock' => $request->stock,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Product details updated successfully.');
    }

    public function removeProduct($slug, $skuId)
    {
        $location = DB::table('pos_locations')->where('slug', $slug)->first();
        if (!$location) abort(404);
        $id = $location->id;
        DB::table('pos_market_pricing')
            ->where('pos_location_id', $id)
            ->where('sku_id', $skuId)
            ->delete();
        return back()->with('success', 'Product removed.');
    }
}
