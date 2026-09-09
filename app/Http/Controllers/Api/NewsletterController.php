<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:newsletter_subscribers,email']
        ]);

        NewsletterSubscriber::create([
            'email' => $request->email,
            'ip_address' => $request->ip(),
            'status' => 'subscribed',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully subscribed to the newsletter.',
        ]);
    }
}
