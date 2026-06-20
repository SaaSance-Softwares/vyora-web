<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/products', [\App\Http\Controllers\Api\ProductController::class, 'index']);
Route::get('/products/{slug}', [\App\Http\Controllers\Api\ProductController::class, 'show']);
Route::get('/search', [\App\Http\Controllers\Api\SearchController::class, 'index']);
Route::get('/search-suggestions', [\App\Http\Controllers\Api\SearchController::class, 'suggestions']);
Route::get('/categories', function () {
    return \App\Models\Category::with([
        'children.children' => function ($q) {
            $q->orderBy('sort_order');
        }
    ])->whereNull('parent_id')->orderBy('sort_order')->get();
});
Route::post('/checkout', [\App\Http\Controllers\Api\OrderController::class, 'store']);

Route::post('/payment/initiate', [\App\Http\Controllers\Api\PaymentController::class, 'initiate']);
Route::post('/payment/verify', [\App\Http\Controllers\Api\PaymentController::class, 'verify']);

// Auth
Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('/user', [\App\Http\Controllers\Api\AuthController::class, 'user']);
    Route::get('/my-orders', [\App\Http\Controllers\Api\OrderController::class, 'index']);
    Route::get('/my-orders/{uuid}', [\App\Http\Controllers\Api\OrderController::class, 'show']);

    // Account
    Route::put('/account/profile', [\App\Http\Controllers\Api\AccountController::class, 'updateProfile']);
    Route::put('/account/password', [\App\Http\Controllers\Api\AccountController::class, 'updatePassword']);
    Route::get('/account/addresses', [\App\Http\Controllers\Api\AccountController::class, 'listAddresses']);
    Route::post('/account/addresses', [\App\Http\Controllers\Api\AccountController::class, 'storeAddress']);
    Route::put('/account/addresses/{address}', [\App\Http\Controllers\Api\AccountController::class, 'updateAddress']);
    Route::delete('/account/addresses/{address}', [\App\Http\Controllers\Api\AccountController::class, 'deleteAddress']);
    Route::put('/account/addresses/{address}/default', [\App\Http\Controllers\Api\AccountController::class, 'setDefaultAddress']);

    // Gift Cards – Authenticated
    Route::get('/gift-cards/my-cards', [\App\Http\Controllers\Api\GiftCardApiController::class, 'myCards']);
    Route::get('/gift-cards/wallet', [\App\Http\Controllers\Api\GiftCardApiController::class, 'walletSummary']);
    Route::post('/gift-cards/lookup-user', [\App\Http\Controllers\Api\GiftCardApiController::class, 'lookupUser']);
    Route::post('/gift-cards/assign', [\App\Http\Controllers\Api\GiftCardApiController::class, 'assignCard']);
    Route::post('/gift-cards/activate', [\App\Http\Controllers\Api\GiftCardApiController::class, 'activateAfterPurchase']);
    Route::post('/gift-cards/validate', [\App\Http\Controllers\Api\GiftCardApiController::class, 'validateCode']);
});

// Settings
Route::get('/settings', [\App\Http\Controllers\Api\SettingsController::class, 'index']);

// CMS Pages
Route::get('/home-page', [\App\Http\Controllers\Api\PageController::class, 'home']);
Route::get('/pages/{slug}', [\App\Http\Controllers\Api\PageController::class, 'show']);

// Coupons
Route::get('/coupons/public', [\App\Http\Controllers\Api\CouponApiController::class, 'getActivePublicCoupons']);
Route::post('/coupons/apply', [\App\Http\Controllers\Api\CouponApiController::class, 'applyCoupon']);

// Gift Cards – Public
Route::get('/gift-cards/purchasable', [\App\Http\Controllers\Api\GiftCardApiController::class, 'getPurchasableOptions']);
Route::get('/gift-cards/share/{token}', [\App\Http\Controllers\Api\GiftCardApiController::class, 'resolveShareToken']);

// Razorpay Webhook
Route::post('/webhooks/razorpay', [\App\Http\Controllers\Api\PaymentController::class, 'handleWebhook']);

// WhatsApp Webhook
Route::get('/webhooks/whatsapp', [\App\Http\Controllers\Api\WhatsAppWebhookController::class, 'verify']);
Route::post('/webhooks/whatsapp', [\App\Http\Controllers\Api\WhatsAppWebhookController::class, 'handle']);

// Webhooks
Route::post('/webhooks/qikink', [\App\Http\Controllers\Api\WebhookController::class, 'handleQikink']);

// Shortlinks
Route::get('/shortlinks/{short_code}', [\App\Http\Controllers\Api\ShortlinkApiController::class, 'resolve']);

// Tracking
Route::post('/tracking/meta-event', [\App\Http\Controllers\Api\TrackingController::class, 'metaEvent']);

// System Status
Route::get('/maintenance-status', function () {
    return response()->json([
        'maintenance' => file_exists(storage_path('framework/down')),
    ]);
});
