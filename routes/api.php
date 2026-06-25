<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CouponApiController;
use App\Http\Controllers\Api\DeliveryPinApiController;
use App\Http\Controllers\Api\GiftCardApiController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\ShortlinkApiController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::get('/search', [SearchController::class, 'index']);
Route::get('/search-suggestions', [SearchController::class, 'suggestions']);
Route::get('/categories', function () {
    return Category::with([
        'children.children' => function ($q) {
            $q->orderBy('sort_order');
        },
    ])->whereNull('parent_id')->orderBy('sort_order')->get();
});
Route::post('/checkout', [OrderController::class, 'store']);

Route::post('/payment/initiate', [PaymentController::class, 'initiate']);
Route::post('/payment/verify', [PaymentController::class, 'verify']);

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/my-orders', [OrderController::class, 'index']);
    Route::get('/my-orders/{uuid}', [OrderController::class, 'show']);

    // Account
    Route::put('/account/profile', [AccountController::class, 'updateProfile']);
    Route::put('/account/password', [AccountController::class, 'updatePassword']);
    Route::get('/account/addresses', [AccountController::class, 'listAddresses']);
    Route::post('/account/addresses', [AccountController::class, 'storeAddress']);
    Route::put('/account/addresses/{address}', [AccountController::class, 'updateAddress']);
    Route::delete('/account/addresses/{address}', [AccountController::class, 'deleteAddress']);
    Route::put('/account/addresses/{address}/default', [AccountController::class, 'setDefaultAddress']);

    // Gift Cards – Authenticated
    Route::get('/gift-cards/my-cards', [GiftCardApiController::class, 'myCards']);
    Route::get('/gift-cards/wallet', [GiftCardApiController::class, 'walletSummary']);
    Route::post('/gift-cards/lookup-user', [GiftCardApiController::class, 'lookupUser']);
    Route::post('/gift-cards/assign', [GiftCardApiController::class, 'assignCard']);
    Route::post('/gift-cards/activate', [GiftCardApiController::class, 'activateAfterPurchase']);
    Route::post('/gift-cards/validate', [GiftCardApiController::class, 'validateCode']);
});

// Settings
Route::get('/settings', [SettingsController::class, 'index']);

// CMS Pages
Route::get('/home-page', [PageController::class, 'home']);
Route::get('/pages/{slug}', [PageController::class, 'show']);

// Coupons
Route::get('/coupons/public', [CouponApiController::class, 'getActivePublicCoupons']);
Route::post('/coupons/apply', [CouponApiController::class, 'applyCoupon']);

// Gift Cards – Public
Route::get('/gift-cards/purchasable', [GiftCardApiController::class, 'getPurchasableOptions']);
Route::get('/gift-cards/share/{token}', [GiftCardApiController::class, 'resolveShareToken']);

// Razorpay Webhook
Route::post('/webhooks/razorpay', [PaymentController::class, 'handleWebhook']);

// WhatsApp Webhook
Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify']);
Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'handle']);

// Webhooks
Route::post('/webhooks/qikink', [WebhookController::class, 'handleQikink']);
Route::post('/webhooks/shipping-partner', [WebhookController::class, 'handleShiprocket']);

// Shortlinks
Route::get('/shortlinks/{short_code}', [ShortlinkApiController::class, 'resolve']);

// Tracking
Route::post('/tracking/meta-event', [TrackingController::class, 'metaEvent']);

// System Status
Route::get('/maintenance-status', function () {
    return response()->json([
        'maintenance' => file_exists(storage_path('framework/down')),
    ]);
});

// Delivery Pincode Check
Route::post('/check-delivery', [DeliveryPinApiController::class, 'check']);
Route::post('/cart/sync', [\App\Http\Controllers\Api\CartController::class, 'sync']);
