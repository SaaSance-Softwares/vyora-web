<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shortlink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShortlinkController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'actual_link' => 'required|url',
            'short_code' => 'nullable|string|max:50|unique:shortlinks,short_code',
        ]);

        $shortCode = $request->short_code ?: Str::random(6);

        // Ensure uniqueness if auto-generated
        while (Shortlink::where('short_code', $shortCode)->exists()) {
            $shortCode = Str::random(6);
        }

        $shortlink = $product->shortlinks()->create([
            'short_code' => $shortCode,
            'actual_link' => $request->actual_link,
            'utm_source' => $request->utm_source,
            'utm_medium' => $request->utm_medium,
            'utm_campaign' => $request->utm_campaign,
            'utm_term' => $request->utm_term,
            'utm_content' => $request->utm_content,
        ]);

        return redirect()->back()
            ->with('success', 'Shortlink created successfully')
            ->withFragment('shortlinks');
    }

    public function destroy(Shortlink $shortlink)
    {
        $shortlink->delete();

        return redirect()->back()
            ->with('success', 'Shortlink deleted successfully')
            ->withFragment('shortlinks');
    }
}
