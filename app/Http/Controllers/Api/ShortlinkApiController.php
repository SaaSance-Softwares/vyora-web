<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShortlinkApiController extends Controller
{
    public function resolve($short_code)
    {
        $shortlink = \App\Models\Shortlink::with('product')->where('short_code', $short_code)->first();

        if (!$shortlink) {
            return response()->json(['message' => 'Shortlink not found'], 404);
        }

        $shortlink->increment('click_count');

        $url = $shortlink->actual_link;
        $params = [];
        if ($shortlink->utm_source) $params['utm_source'] = $shortlink->utm_source;
        if ($shortlink->utm_medium) $params['utm_medium'] = $shortlink->utm_medium;
        if ($shortlink->utm_campaign) $params['utm_campaign'] = $shortlink->utm_campaign;
        if ($shortlink->utm_term) $params['utm_term'] = $shortlink->utm_term;
        if ($shortlink->utm_content) $params['utm_content'] = $shortlink->utm_content;

        if (!empty($params)) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . http_build_query($params);
        }

        $meta = null;
        if ($shortlink->product) {
            $meta = [
                'title' => $shortlink->product->seo_title ?: $shortlink->product->name,
                'description' => $shortlink->product->seo_description ?: $shortlink->product->short_description,
                'image' => $shortlink->product->image_url
            ];
        }

        return response()->json([
            'url' => $url,
            'meta' => $meta
        ]);
    }
}
