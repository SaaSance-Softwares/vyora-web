<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShortlinkController extends Controller
{
    public function store(Request $request, \App\Models\Product $product)
    {
        $request->validate([
            'actual_link' => 'required|url',
            'short_code' => 'nullable|string|max:50|unique:shortlinks,short_code',
        ]);

        $shortCode = $request->short_code ?: \Illuminate\Support\Str::random(6);

        // Ensure uniqueness if auto-generated
        while (\App\Models\Shortlink::where('short_code', $shortCode)->exists()) {
            $shortCode = \Illuminate\Support\Str::random(6);
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

    public function destroy(\App\Models\Shortlink $shortlink)
    {
        $shortlink->delete();
        return redirect()->back()
            ->with('success', 'Shortlink deleted successfully')
            ->withFragment('shortlinks');
    }
}
