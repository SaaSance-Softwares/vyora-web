<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CmsPage;

class PageController extends Controller
{
    public function home(Request $request)
    {
        $page = CmsPage::where('is_home', true)->where('is_active', true)->first();

        if (!$page) {
            return response()->json(['message' => 'No home page configured'], 404);
        }

        return response()->json([
            'title' => $page->title,
            'layout' => $page->layout ?? 'default',
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'content' => $request->has('preview') && $request->preview === 'true' ? $page->draft_content : $page->content,
        ]);
    }

    public function show(Request $request, $slug)
    {
        $page = CmsPage::where('slug', $slug)->where('is_active', true)->first();

        if (!$page) {
            return response()->json(['message' => 'Page not found'], 404);
        }

        return response()->json([
            'title' => $page->title,
            'layout' => $page->layout ?? 'default',
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'content' => $request->has('preview') && $request->preview === 'true' ? $page->draft_content : $page->content,
        ]);
    }
}
