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
    public function store(Request $request, Product $product)
    {
        if (! Auth::check()) {
            return back()->with('error', 'You must be logged in to leave a review.');
        }

        $user = Auth::user();

        // Check if user has purchased this product and the order is delivered
        $order = Order::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->first();

        if (! $order) {
            return back()->with('error', 'You can only review products you have purchased and received.');
        }

        // Check if they already reviewed it for this order
        $existingReview = Review::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->where('order_id', $order->id)
            ->first();

        if ($existingReview) {
            return back()->with('error', 'You have already reviewed this product.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'images.*' => 'nullable|image|max:5120', // Max 5MB per image
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
                $fileName = time().'_'.Str::random(5).'_'.Str::slug($originalName).'.'.$file->getClientOriginalExtension();
                $file->move($destinationPath, $fileName);

                ReviewImage::create([
                    'review_id' => $review->id,
                    'image_path' => "storage/reviews/{$fileName}",
                ]);
            }
        }

        return back()->with('success', 'Your review has been submitted successfully.');
    }
}
