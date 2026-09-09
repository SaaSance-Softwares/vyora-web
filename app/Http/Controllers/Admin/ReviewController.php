<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with(['product', 'user', 'images'])
            ->latest()
            ->paginate(20);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function reply(Request $request, Review $review)
    {
        $request->strictValidate([
            'admin_reply' => 'nullable|string|max:5000',
        ]);

        $review->update([
            'admin_reply' => $request->admin_reply,
        ]);

        return back()->with('success', 'Reply updated successfully.');
    }

    public function destroy(Review $review)
    {
        // Delete images from storage first
        foreach ($review->images as $image) {
            $path = public_path($image->image_path);
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        $review->delete();

        return back()->with('success', 'Review deleted successfully.');
    }
}
