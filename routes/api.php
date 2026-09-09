<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CouponApiController;
use App\Http\Controllers\Api\DeliveryPinApiController;
use App\Http\Controllers\Api\GiftCardApiController;
use App\Http\Controllers\Api\NewsletterController;
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

Route::middleware('throttle:public_api')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
    Route::post('/subscribe', [NewsletterController::class, 'subscribe']);
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
    Route::post('/payment/failed', [PaymentController::class, 'failed']);
});

// Auth
Route::middleware([
    'throttle.auth.backoff',
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register/send-otp', [AuthController::class, 'sendRegistrationOtp']);
    Route::post('/register/verify-otp', [AuthController::class, 'verifyRegistrationOtp']);
    
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login/send-otp', [AuthController::class, 'sendLoginOtp']);
    Route::post('/login/verify-otp', [AuthController::class, 'verifyLoginOtp']);
});

Route::middleware(['auth:sanctum', 'throttle:authenticated_api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/cart', [\App\Http\Controllers\Api\CartController::class, 'get']);
    Route::get('/my-orders', [OrderController::class, 'index']);
    Route::get('/my-orders/{uuid}', [OrderController::class, 'show']);
    Route::post('/my-orders/{uuid}/cancel', [\App\Http\Controllers\Api\UserOrderActionController::class, 'cancel']);
    Route::post('/my-orders/{uuid}/return', [\App\Http\Controllers\Api\UserOrderActionController::class, 'returnOrder']);
    Route::post('/my-orders/{uuid}/exchange', [\App\Http\Controllers\Api\UserOrderActionController::class, 'exchange']);

    // Wishlist
    Route::get('/wishlist', [\App\Http\Controllers\Api\WishlistController::class, 'get']);
    Route::post('/wishlist/sync', [\App\Http\Controllers\Api\WishlistController::class, 'sync']);

    // DPDP User Management
    Route::put('/user/consent', [UserController::class, 'updateConsent']);
    Route::post('/user/delete-account/otp', [UserController::class, 'sendDeleteOtp']);
    Route::delete('/user/delete-account', [UserController::class, 'deleteAccount']);

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

Route::middleware('throttle:public_api')->group(function () {
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
    Route::post('/gift-cards/share-email', [GiftCardApiController::class, 'shareEmail']);

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
    Route::post('/tracking/snapchat-event', [TrackingController::class, 'snapchatEvent']);

    // System Status
    Route::get('/maintenance-status', function () {
        return response()->json([
            'maintenance' => file_exists(storage_path('framework/down')),
        ]);
    });

    // Localization
    Route::get('/localization/countries', [\App\Http\Controllers\Api\LocalizationApiController::class, 'getActiveCountries']);
    Route::get('/localization/postal-code/{country_id}/{postal_code}', [\App\Http\Controllers\Api\LocalizationApiController::class, 'lookupPostalCode']);

    // Delivery Pincode Check
    Route::post('/check-delivery', [DeliveryPinApiController::class, 'check']);
    Route::post('/cart/sync', [\App\Http\Controllers\Api\CartController::class, 'sync']);

    Route::get('/debug-wa', function() {
        return response()->json([
            'conversations' => \App\Models\WhatsappConversation::count(),
            'messages' => \App\Models\WhatsappMessage::count(),
            'recent' => \App\Models\WhatsappMessage::orderBy('id', 'desc')->take(5)->get()
        ]);
    });
});

// Mobile App API Routes
Route::prefix('mobile')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\Mobile\AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Api\Mobile\AuthController::class, 'logout']);
        Route::get('/dashboard', [\App\Http\Controllers\Api\Mobile\DashboardController::class, 'index']);
        Route::get('/subscription', [\App\Http\Controllers\Api\Mobile\DashboardController::class, 'subscription']);
        Route::get('/orders', [\App\Http\Controllers\Api\Mobile\OrderController::class, 'index']);
        Route::get('/orders/{id}', [\App\Http\Controllers\Api\Mobile\OrderController::class, 'show']);
        Route::post('/fcm-token', [\App\Http\Controllers\Api\DeviceTokenController::class, 'store']);
    });
});

