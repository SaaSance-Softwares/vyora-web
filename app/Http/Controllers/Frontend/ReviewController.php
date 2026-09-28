<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\ReviewImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    public function update(Request $request, Review $review)
    {
        if (! Auth::check() || $review->user_id !== Auth::id()) {
            if ($request->wantsJson() || $request->is('api/*')) { return response()->json(['error' => 'Unauthorized.'], 403); } return back()->with('error', 'Unauthorized.');
        }

        $request->strictValidate([
            'rating' => 'required|numeric|min:0.5|max:5',
            'comment' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|mimes:jpeg,png,jpg,gif,webp,heic,heif|max:8192',
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'integer',
            'order_id' => 'nullable|integer',
        ]);

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => false,
        ]);

        if ($request->has('deleted_images')) {
            $imagesToDelete = ReviewImage::where('review_id', $review->id)
                ->whereIn('id', $request->deleted_images)
                ->get();
                
            foreach ($imagesToDelete as $img) {
                if (file_exists(public_path($img->image_path))) {
                    unlink(public_path($img->image_path));
                }
                $img->delete();
            }
        }

        if ($request->hasFile('images')) {
            $destinationPath = public_path('storage/reviews');
            if (! file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            // Check current count
            $currentCount = ReviewImage::where('review_id', $review->id)->count();
            $newFiles = $request->file('images');
            
            foreach ($newFiles as $file) {
                if ($currentCount >= 5) break;
                
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $fileName = time().'_'.Str::random(5).'_'.Str::slug($originalName).'.webp';
                
                try {
                    \Intervention\Image\Laravel\Facades\Image::read($file)
                        ->scaleDown(1200, 1200)
                        ->toWebp(80)
                        ->save($destinationPath . '/' . $fileName);
                        
                    ReviewImage::create([
                        'review_id' => $review->id,
                        'image_path' => "storage/reviews/{$fileName}",
                    ]);
                } catch (\Exception $e) {
                    $fallbackName = time().'_'.Str::random(5).'_'.Str::slug($originalName).'.'.$file->getClientOriginalExtension();
                    $file->move($destinationPath, $fallbackName);
                    
                    ReviewImage::create([
                        'review_id' => $review->id,
                        'image_path' => "storage/reviews/{$fallbackName}",
                    ]);
                }
                
                $currentCount++;
            }
        }

        if ($request->wantsJson() || $request->is('api/*')) { return response()->json(['success' => 'Your review has been updated and is pending approval.']); } return back()->with('success', 'Your review has been updated and is pending approval.');
    }

    public function store(Request $request, Product $product)
    {
        if (! Auth::check()) {
            if ($request->wantsJson() || $request->is('api/*')) { return response()->json(['error' => 'You must be logged in to leave a review.'], 403); } return back()->with('error', 'You must be logged in to leave a review.');
        }

        $user = Auth::user();

        // Accept order_id explicitly if provided
        $orderId = $request->input('order_id');
        
        $orderQuery = Order::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('shippingAddress', function ($q) use ($user) {
                          $q->where('email', $user->email);
                      });
            })
            ->where('status', 'delivered')
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            });
            
        if ($orderId) {
            $orderQuery->where('id', $orderId);
        }
        
        $order = $orderQuery->first();

        if (! $order) {
            if ($request->wantsJson() || $request->is('api/*')) { return response()->json(['error' => 'You can only review products you have purchased and received.'], 403); } return back()->with('error', 'You can only review products you have purchased and received.');
        }

        // Check if they already reviewed it for this order
        $existingReview = Review::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('order_id', $order->id)
            ->first();

        if ($existingReview) {
            if ($request->wantsJson() || $request->is('api/*')) { return response()->json(['error' => 'You have already reviewed this product.'], 403); } return back()->with('error', 'You have already reviewed this product.');
        }

        $request->strictValidate([
            'rating' => 'required|numeric|min:0.5|max:5',
            'comment' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|mimes:jpeg,png,jpg,gif,webp,heic,heif|max:8192',
            'order_id' => 'nullable|integer',
        ]);

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        if ($request->hasFile('images')) {
            $destinationPath = public_path('storage/reviews');
            if (! file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            foreach ($request->file('images') as $file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $fileName = time().'_'.Str::random(5).'_'.Str::slug($originalName).'.webp';
                
                try {
                    \Intervention\Image\Laravel\Facades\Image::read($file)
                        ->scaleDown(1200, 1200)
                        ->toWebp(80)
                        ->save($destinationPath . '/' . $fileName);
                        
                    ReviewImage::create([
                        'review_id' => $review->id,
                        'image_path' => "storage/reviews/{$fileName}",
                    ]);
                } catch (\Exception $e) {
                    // Fallback for HEIC if server lacks support
                    $fallbackName = time().'_'.Str::random(5).'_'.Str::slug($originalName).'.'.$file->getClientOriginalExtension();
                    $file->move($destinationPath, $fallbackName);
                    
                    ReviewImage::create([
                        'review_id' => $review->id,
                        'image_path' => "storage/reviews/{$fallbackName}",
                    ]);
                }
            }
        }

        if ($request->wantsJson() || $request->is('api/*')) { return response()->json(['success' => 'Your review has been submitted and is pending approval.']); } return back()->with('success', 'Your review has been submitted and is pending approval.');
    }
}
