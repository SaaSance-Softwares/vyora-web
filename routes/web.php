<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Frontend Routes
Route::get('/', [\App\Http\Controllers\Frontend\PageController::class, 'home'])->name('frontend.home');
Route::get('/shop', [\App\Http\Controllers\Frontend\PageController::class, 'shop'])->name('frontend.shop');
Route::get('/search', [\App\Http\Controllers\Frontend\PageController::class, 'search'])->name('frontend.search');
Route::get('/product/{slug}', [\App\Http\Controllers\Frontend\PageController::class, 'product'])->name('frontend.product');
Route::post('/products/{product}/reviews', [\App\Http\Controllers\Frontend\ReviewController::class, 'store'])->name('frontend.reviews.store');
Route::get('/category/{slug}', [\App\Http\Controllers\Frontend\PageController::class, 'category'])->name('frontend.category');
Route::get('/collection/{slug}', [\App\Http\Controllers\Frontend\PageController::class, 'collection'])->name('frontend.collection');
Route::get('/cart', [\App\Http\Controllers\Frontend\PageController::class, 'cart'])->name('frontend.cart');
Route::get('/checkout', [\App\Http\Controllers\Frontend\PageController::class, 'checkout'])->name('frontend.checkout');
Route::get('/checkout/thank-you/{uuid}', [\App\Http\Controllers\Frontend\PageController::class, 'thankYou'])->name('frontend.thank-you');
Route::get('/wishlist', [\App\Http\Controllers\Frontend\PageController::class, 'wishlist'])->name('frontend.wishlist');
Route::get('/google-merchant-feed.xml', [\App\Http\Controllers\GoogleMerchantController::class, 'feed'])->name('frontend.google-merchant-feed');
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('frontend.sitemap');
Route::get('/gift-cards', function () {
    return \Inertia\Inertia::render('GiftCards/Index');
})->name('frontend.gift-cards.index');

Route::get('/gift-cards/share/{token}', function ($token) {
    return \Inertia\Inertia::render('GiftCards/Share', ['token' => $token]);
})->name('frontend.gift-cards.share');

// User Dashboard Routes
Route::middleware('auth')->group(function () {
    Route::get('/account', function () {
        return \Inertia\Inertia::render('Account/Index');
    })->name('frontend.account');

    Route::get('/orders', function () {
        return \Inertia\Inertia::render('Account/Orders');
    })->name('frontend.orders');

    Route::get('/orders/{uuid}', function ($uuid) {
        return \Inertia\Inertia::render('Account/OrderDetails', ['uuid' => $uuid]);
    })->name('frontend.orders.show');

    Route::get('/gift-cards/my-cards', function () {
        return \Inertia\Inertia::render('GiftCards/MyCards');
    })->name('frontend.gift-cards.my-cards');
});

Route::get('/p/{slug}', [\App\Http\Controllers\Frontend\PageController::class, 'show'])->name('frontend.page');

Route::get('/add-tax-class', function () {
    try {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('products', 'tax_class')) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE products ADD COLUMN tax_class VARCHAR(255) NULL');
        }
        return "Added tax_class column successfully";
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

// Installer Routes
Route::prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallerController::class, 'welcome'])->name('welcome');
    Route::get('/database', [InstallerController::class, 'database'])->name('database');
    Route::post('/database', [InstallerController::class, 'processDatabase'])->name('processDatabase');
    Route::get('/admin', [InstallerController::class, 'admin'])->name('admin');
    Route::post('/admin', [InstallerController::class, 'processAdmin'])->name('processAdmin');
});

// Frontend Auth Routes
Route::get('/login', function () {
    return \Inertia\Inertia::render('Auth/Login');
})->name('login');

Route::get('/register', function () {
    return \Inertia\Inertia::render('Auth/Register');
})->name('register');

$adminPath = config('app.admin_path', 'admin');

// Admin Auth and Dashboard Routes
Route::prefix($adminPath)->name('admin.')->group(function () {
    
    // Admin Auth Routes
    Route::get('/login', [\App\Http\Controllers\Admin\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Admin\LoginController::class, 'login']);
    Route::post('/logout', [\App\Http\Controllers\Admin\LoginController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'verified', 'admin_access'])->group(function () {

    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard'); // Explicit admin dashboard

    // Products Management Group
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/search', [\App\Http\Controllers\Admin\ProductController::class, 'search'])->name('search');
        Route::get('/', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\ProductController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('store');
        Route::get('/{product}/edit', [\App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('destroy');

        Route::post('/{product}/shortlinks', [\App\Http\Controllers\Admin\ShortlinkController::class, 'store'])->name('shortlinks.store');
        Route::delete('/shortlinks/{shortlink}', [\App\Http\Controllers\Admin\ShortlinkController::class, 'destroy'])->name('shortlinks.destroy');

        // Media routes
        Route::post('/{product}/media/upload', [\App\Http\Controllers\Admin\ProductMediaController::class, 'upload'])->name('media.upload');
        Route::post('/{product}/media/upload-preview', [\App\Http\Controllers\Admin\ProductMediaController::class, 'uploadMasterPreview'])->name('media.upload-preview');
        Route::post('/{product}/media/upload-cat-preview', [\App\Http\Controllers\Admin\ProductMediaController::class, 'uploadCategoryMasterPreview'])->name('media.upload-cat-preview');
        Route::delete('/{product}/media/delete-cat-preview', [\App\Http\Controllers\Admin\ProductMediaController::class, 'deleteCategoryMasterPreview'])->name('media.delete-cat-preview');
        Route::delete('/{product}/media/{productImage}', [\App\Http\Controllers\Admin\ProductMediaController::class, 'delete'])->name('media.delete');
        Route::post('/{product}/media/{productImage}/primary', [\App\Http\Controllers\Admin\ProductMediaController::class, 'setPrimary'])->name('media.setPrimary');
        Route::post('/{product}/media/reorder', [\App\Http\Controllers\Admin\ProductMediaController::class, 'reorder'])->name('media.reorder');
    });

    // Reuse existing upload controller but link it conceptually under products
    Route::get('/upload', [\App\Http\Controllers\Admin\ProductUploadController::class, 'index'])->name('upload');
    Route::get('/upload/sample-qikink', [\App\Http\Controllers\Admin\ProductUploadController::class, 'downloadSampleQikink'])->name('upload.sample-qikink');
    Route::get('/upload/sample-general', [\App\Http\Controllers\Admin\ProductUploadController::class, 'downloadSampleGeneral'])->name('upload.sample-general');
    Route::post('/upload', [\App\Http\Controllers\Admin\ProductUploadController::class, 'store'])->name('upload.store');

    // Categories
    Route::post('/categories/reorder', [\App\Http\Controllers\Admin\CategoryController::class, 'reorder'])->name('categories.reorder');
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);

    // Collections
    Route::resource('collections', \App\Http\Controllers\Admin\CollectionController::class);

    // Attributes (Colors, Product Types)
    Route::get('/attributes/export/{type}', [\App\Http\Controllers\Admin\AttributeImportExportController::class, 'export'])->name('attributes.export');
    Route::post('/attributes/import/{type}', [\App\Http\Controllers\Admin\AttributeImportExportController::class, 'import'])->name('attributes.import');
    Route::get('/attributes/sample/{type}', [\App\Http\Controllers\Admin\AttributeImportExportController::class, 'sample'])->name('attributes.sample');
    Route::get('/attributes', [\App\Http\Controllers\Admin\AttributeController::class, 'index'])->name('attributes.index');
    Route::post('/attributes/colors', [\App\Http\Controllers\Admin\AttributeController::class, 'storeColor'])->name('attributes.colors.store');
    Route::put('/attributes/colors/{color}', [\App\Http\Controllers\Admin\AttributeController::class, 'updateColor'])->name('attributes.colors.update');
    Route::delete('/attributes/colors/{color}', [\App\Http\Controllers\Admin\AttributeController::class, 'destroyColor'])->name('attributes.colors.destroy');
    Route::post('/attributes/types', [\App\Http\Controllers\Admin\AttributeController::class, 'storeType'])->name('attributes.types.store');
    Route::put('/attributes/types/{type}', [\App\Http\Controllers\Admin\AttributeController::class, 'updateType'])->name('attributes.types.update');
    Route::delete('/attributes/types/{type}', [\App\Http\Controllers\Admin\AttributeController::class, 'destroyType'])->name('attributes.types.destroy');
    Route::post('/attributes/sizes', [\App\Http\Controllers\Admin\AttributeController::class, 'storeSize'])->name('attributes.sizes.store');
    Route::put('/attributes/sizes/{size}', [\App\Http\Controllers\Admin\AttributeController::class, 'updateSize'])->name('attributes.sizes.update');
    Route::delete('/attributes/sizes/{size}', [\App\Http\Controllers\Admin\AttributeController::class, 'destroySize'])->name('attributes.sizes.destroy');

    // Size Charts
    Route::resource('size-charts', \App\Http\Controllers\Admin\SizeChartController::class);

    // Orders
    Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [\App\Http\Controllers\Admin\OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::patch('/orders/{order}/tracking', [\App\Http\Controllers\Admin\OrderController::class, 'updateTracking'])->name('orders.updateTracking');

    // WhatsApp Chat & Templates
    Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\WhatsAppController::class, 'index'])->name('index');
        Route::get('/unread-count', [\App\Http\Controllers\Admin\WhatsAppController::class, 'unreadCount'])->name('unread-count');
        Route::get('/customers/search', [\App\Http\Controllers\Admin\WhatsAppController::class, 'searchCustomers'])->name('customers.search');
        Route::post('/conversations/start', [\App\Http\Controllers\Admin\WhatsAppController::class, 'startConversation'])->name('conversations.start');
        
        Route::get('/conversations/{conversation}/messages', [\App\Http\Controllers\Admin\WhatsAppController::class, 'messages'])->name('messages');
        Route::post('/conversations/{conversation}/messages', [\App\Http\Controllers\Admin\WhatsAppController::class, 'sendMessage'])->name('send');
        Route::post('/conversations/{conversation}/template', [\App\Http\Controllers\Admin\WhatsAppController::class, 'sendTemplate'])->name('sendTemplate');

        Route::prefix('templates')->name('templates.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'store'])->name('store');
            Route::post('/sync', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'sync'])->name('sync');
        });
    });

    // Online Store
    Route::prefix('online-store')->name('online-store.')->group(function () {
        // Theme Settings removed and merged into General Settings

        // General Settings
        Route::get('/general-settings', [\App\Http\Controllers\Admin\GeneralSettingsController::class, 'index'])->name('general-settings.index');
        Route::put('/general-settings', [\App\Http\Controllers\Admin\GeneralSettingsController::class, 'update'])->name('general-settings.update');

        // Policy Settings
        Route::get('/policy-settings', [\App\Http\Controllers\Admin\PolicySettingsController::class, 'index'])->name('policy-settings.index');
        Route::put('/policy-settings', [\App\Http\Controllers\Admin\PolicySettingsController::class, 'update'])->name('policy-settings.update');

        // Delivery PIN Settings
        Route::get('/delivery-pins', [\App\Http\Controllers\Admin\DeliveryPinController::class, 'index'])->name('delivery-pins.index');
        Route::post('/delivery-pins', [\App\Http\Controllers\Admin\DeliveryPinController::class, 'update'])->name('delivery-pins.update');

        // Product Card Settings
        Route::get('/product-card-settings', [\App\Http\Controllers\Admin\ProductCardSettingsController::class, 'index'])->name('product-card-settings.index');
        Route::put('/product-card-settings', [\App\Http\Controllers\Admin\ProductCardSettingsController::class, 'update'])->name('product-card-settings.update');

        // PDP Settings
        Route::get('/pdp-settings', [\App\Http\Controllers\Admin\PdpSettingsController::class, 'index'])->name('pdp-settings.index');
        Route::put('/pdp-settings', [\App\Http\Controllers\Admin\PdpSettingsController::class, 'update'])->name('pdp-settings.update');

        // Auth Settings
        Route::get('/auth-settings', [\App\Http\Controllers\Admin\AuthSettingsController::class, 'index'])->name('auth-settings.index');
        Route::put('/auth-settings', [\App\Http\Controllers\Admin\AuthSettingsController::class, 'update'])->name('auth-settings.update');

        // Tax & Shipping
        Route::get('/tax-shipping', [\App\Http\Controllers\Admin\TaxShippingSettingsController::class, 'index'])->name('tax-shipping.index');
        Route::put('/tax-shipping', [\App\Http\Controllers\Admin\TaxShippingSettingsController::class, 'update'])->name('tax-shipping.update');

        // Integrations
        Route::get('/integrations', [\App\Http\Controllers\Admin\IntegrationSettingsController::class, 'index'])->name('integrations.index');
        Route::get('/integrations/{slug}', [\App\Http\Controllers\Admin\IntegrationSettingsController::class, 'show'])->name('integrations.show');
        Route::put('/integrations/{slug}', [\App\Http\Controllers\Admin\IntegrationSettingsController::class, 'update'])->name('integrations.update');
        Route::post('/integrations/razorpay/test', [\App\Http\Controllers\Admin\IntegrationSettingsController::class, 'testRazorpay'])->name('integrations.razorpay.test');
        Route::post('/integrations/qikink/test', [\App\Http\Controllers\Admin\IntegrationSettingsController::class, 'testQikink'])->name('integrations.qikink.test');
        Route::post('/integrations/algolia/test', [\App\Http\Controllers\Admin\IntegrationSettingsController::class, 'testAlgolia'])->name('integrations.algolia.test');

        // Navbar Settings
        Route::get('/navbar-settings', [\App\Http\Controllers\Admin\NavbarSettingsController::class, 'index'])->name('navbar-settings.index');
        Route::put('/navbar-settings', [\App\Http\Controllers\Admin\NavbarSettingsController::class, 'update'])->name('navbar-settings.update');

        // Coupons
        Route::resource('coupons', \App\Http\Controllers\Admin\CouponController::class);

        // Marketing / Search Queries
        Route::prefix('marketing/search-queries')->name('marketing.search-queries.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SearchQueryController::class, 'index'])->name('index');
            Route::post('/export', [\App\Http\Controllers\Admin\SearchQueryController::class, 'export'])->name('export');
            Route::delete('/delete-by-date', [\App\Http\Controllers\Admin\SearchQueryController::class, 'deleteByDate'])->name('deleteByDate');
        });

        // Gift Cards – Templates
        Route::prefix('gift-cards')->name('gift-cards.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\GiftCardController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\GiftCardController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\GiftCardController::class, 'store'])->name('store');
            // Template actions (giftCard = GiftCardTemplate model)
            Route::get('/{giftCard}', [\App\Http\Controllers\Admin\GiftCardController::class, 'show'])->name('show');
            Route::post('/{giftCard}/toggle', [\App\Http\Controllers\Admin\GiftCardController::class, 'toggleTemplate'])->name('toggle');
            Route::delete('/{giftCard}', [\App\Http\Controllers\Admin\GiftCardController::class, 'destroyTemplate'])->name('destroy');
            // Issued card actions
            Route::get('/cards/{card}', [\App\Http\Controllers\Admin\GiftCardController::class, 'showCard'])->name('cards.show');
            Route::post('/cards/{card}/withdraw', [\App\Http\Controllers\Admin\GiftCardController::class, 'withdraw'])->name('cards.withdraw');
        });

        // CMS Pages (Placeholder for next step)
        Route::post('/mnpages/upload-image', [\App\Http\Controllers\Admin\PageUploadController::class, 'upload'])->name('mnpages.upload-image');
        Route::post('/mnpages/{mnpage}/auto-save', [\App\Http\Controllers\Admin\PageController::class, 'autoSave'])->name('mnpages.auto-save');
        Route::post('/mnpages/{mnpage}/publish', [\App\Http\Controllers\Admin\PageController::class, 'publish'])->name('mnpages.publish');
        Route::get('/mnpages/{mnpage}/design', [\App\Http\Controllers\Admin\PageController::class, 'design'])->name('mnpages.design');
        Route::resource('mnpages', \App\Http\Controllers\Admin\PageController::class);
    });

    // Customers
    Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class)->only(['index', 'show']);

    // Reviews
    Route::get('/reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/reply', [\App\Http\Controllers\Admin\ReviewController::class, 'reply'])->name('reviews.reply');
    Route::delete('/reviews/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Admin Settings Section
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/admin', [\App\Http\Controllers\Admin\AdminSettingController::class, 'index'])->name('index');
        Route::put('/admin', [\App\Http\Controllers\Admin\AdminSettingController::class, 'update'])->name('update');

        Route::get('/users', [\App\Http\Controllers\Admin\AdminSettingController::class, 'users'])->name('users');
        Route::post('/users', [\App\Http\Controllers\Admin\AdminSettingController::class, 'storeUser'])->name('users.store');
        Route::put('/users/{user}', [\App\Http\Controllers\Admin\AdminSettingController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [\App\Http\Controllers\Admin\AdminSettingController::class, 'destroyUser'])->name('users.destroy');

        Route::get('/vyora', [\App\Http\Controllers\Admin\AdminSettingController::class, 'vyora'])->name('vyora');

        // System Updates
        Route::get('/update', [\App\Http\Controllers\Admin\SystemUpdateController::class, 'index'])->name('update.index');
        Route::post('/update', [\App\Http\Controllers\Admin\SystemUpdateController::class, 'update'])->name('update.process');
        Route::post('/update/maintenance', [\App\Http\Controllers\Admin\SystemUpdateController::class, 'toggleMaintenance'])->name('update.maintenance');
    });
});

});
