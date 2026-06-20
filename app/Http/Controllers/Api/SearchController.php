<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductListResource;
use App\Models\Product;
use App\Models\SearchQuery;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q', '');

        // Use Scout's search, which will use Algolia if configured, or Database if not
        if (config('scout.driver') === 'database') {
            $queryBuilder = Product::where('is_active', true)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('short_description', 'like', "%{$query}%")
                      ->orWhere('brand_name', 'like', "%{$query}%")
                      ->orWhere('tags', 'like', "%{$query}%")
                      ->orWhereHas('categories', function($cq) use ($query) {
                          $cq->where('name', 'like', "%{$query}%");
                      })
                      ->orWhereHas('collections', function($cq) use ($query) {
                          $cq->where('name', 'like', "%{$query}%");
                      });
                });

            $products = $queryBuilder->with(['images', 'skus'])->paginate(12);
        } else {
            $products = Product::search($query)
                ->query(function ($builder) {
                    // Eager load related data and apply any necessary constraints
                    $builder->with(['images', 'skus'])
                            ->where('is_active', true);
                })
                ->paginate(12);
        }

        // Record the search if there's a valid query string
        if (!empty(trim($query))) {
            $normalized = $this->normalizeQuery($query);
            if (empty($normalized)) {
                $normalized = strtolower(trim($query));
            }
            
            $existing = SearchQuery::where('normalized_query', $normalized)->first();
            
            if ($existing) {
                $existing->increment('count');
                $existing->update([
                    'results_count' => $products->total(),
                    'updated_at' => now(),
                ]);
            } else {
                SearchQuery::create([
                    'query' => trim($query),
                    'normalized_query' => $normalized,
                    'results_count' => $products->total(),
                    'count' => 1,
                ]);
            }
        }

        return ProductListResource::collection($products);
    }

    private function normalizeQuery($phrase)
    {
        $phrase = preg_replace("/[^a-zA-Z0-9\s]/", "", strtolower($phrase));
        $stopwords = ["which", "is", "a", "an", "the", "in", "on", "of", "and", "or", "for", "to", "with"];
        $words = explode(" ", $phrase);
        $words = array_diff($words, $stopwords);
        
        $normalized = [];
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) > 3) {
                $word = preg_replace("/(ed|ing|s|es)$/", "", $word);
            }
            if (!empty($word)) {
                $normalized[] = $word;
            }
        }
        sort($normalized);
        return implode(" ", $normalized);
    }

    public function suggestions(Request $request)
    {
        $q = trim($request->input('q', ''));
        
        if (!empty($q)) {
            $suggestions = SearchQuery::where('query', 'like', "{$q}%")
                ->orWhere('normalized_query', 'like', "{$q}%")
                ->orderBy('count', 'desc')
                ->take(6)
                ->pluck('query')
                ->unique()
                ->values();
        } else {
            $suggestions = SearchQuery::orderBy('count', 'desc')
                ->take(6)
                ->pluck('query')
                ->unique()
                ->values();
        }
        
        return response()->json($suggestions);
    }
}
