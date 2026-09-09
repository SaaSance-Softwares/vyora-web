<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class MobileAppController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Delete any existing mobile-app tokens for this user to keep it clean
        $user->tokens()->where('name', 'mobile-app')->delete();

        // Generate a new secure token for the mobile app
        $token = $user->createToken('mobile-app')->plainTextToken;

        return view('admin.mobile-app', compact('token'));
    }
}
