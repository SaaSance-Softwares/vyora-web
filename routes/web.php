<?php

use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\AttributeImportExportController;
use App\Http\Controllers\Admin\AuthSettingsController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryPinController;
use App\Http\Controllers\Admin\GeneralSettingsController;
use App\Http\Controllers\Admin\GiftCardController;
use App\Http\Controllers\Admin\IntegrationSettingsController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\NavbarSettingsController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageUploadController;
use App\Http\Controllers\Admin\PdpSettingsController;
use App\Http\Controllers\Admin\PolicySettingsController;
use App\Http\Controllers\Admin\ProductCardSettingsController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductMediaController;
use App\Http\Controllers\Admin\ProductUploadController;
use App\Http\Controllers\Admin\SearchQueryController;
use App\Http\Controllers\Admin\ShortlinkController;
use App\Http\Controllers\Admin\SizeChartController;
use App\Http\Controllers\Admin\SystemUpdateController;
use App\Http\Controllers\Admin\TaxShippingSettingsController;
use App\Http\Controllers\Admin\WhatsAppController;
use App\Http\Controllers\Admin\WhatsAppTemplateController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ReviewController;
use App\Http\Controllers\GoogleMerchantController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

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
Route::get('/', [PageController::class, 'home'])->name('frontend.home');
Route::get('/shop', [PageController::class, 'shop'])->name('frontend.shop');
Route::get('/search', [PageController::class, 'search'])->name('frontend.search');
Route::get('/product/{slug}', [PageController::class, 'product'])->name('frontend.product');
Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('frontend.reviews.store');
Route::get('/category/{slug}', [PageController::class, 'category'])->name('frontend.category');
Route::get('/collection/{slug}', [PageController::class, 'collection'])->name('frontend.collection');
Route::get('/cart', [PageController::class, 'cart'])->name('frontend.cart');
Route::get('/checkout', [PageController::class, 'checkout'])->name('frontend.checkout');
Route::get('/checkout/thank-you/{uuid}', [PageController::class, 'thankYou'])->name('frontend.thank-you');
Route::get('/wishlist', [PageController::class, 'wishlist'])->name('frontend.wishlist');
Route::get('/google-merchant-feed.xml', [GoogleMerchantController::class, 'feed'])->name('frontend.google-merchant-feed');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('frontend.sitemap');
Route::get('/gift-cards', function () {
    return Inertia::render('GiftCards/Index');
})->name('frontend.gift-cards.index');

Route::get('/gift-cards/share/{token}', function ($token) {
    return Inertia::render('GiftCards/Share', ['token' => $token]);
})->name('frontend.gift-cards.share');

// User Dashboard Routes
Route::middleware('auth')->group(function () {
    Route::get('/account', function () {
        return Inertia::render('Account/Index');
    })->name('frontend.account');

    Route::get('/orders', function () {
        return Inertia::render('Account/Orders');
    })->name('frontend.orders');

    Route::get('/orders/{uuid}', function ($uuid) {
        return Inertia::render('Account/OrderDetails', ['uuid' => $uuid]);
    })->name('frontend.orders.show');

    Route::get('/gift-cards/my-cards', function () {
        return Inertia::render('GiftCards/MyCards');
    })->name('frontend.gift-cards.my-cards');
});

Route::get('/p/{slug}', [PageController::class, 'show'])->name('frontend.page');

Route::get('/add-tax-class', function () {
    try {
        if (! Schema::hasColumn('products', 'tax_class')) {
            DB::statement('ALTER TABLE products ADD COLUMN tax_class VARCHAR(255) NULL');
        }

        return 'Added tax_class column successfully';
    } catch (Exception $e) {
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
Route::get('/s/{short_code}', [App\Http\Controllers\Frontend\ShortlinkController::class, 'resolve'])->name('frontend.shortlink.resolve');

Route::get('/login', function () {
    return Inertia::render('Auth/Login');
})->name('login');

Route::get('/register', function () {
    return Inertia::render('Auth/Register');
})->name('register');

$adminPath = config('app.admin_path', 'admin');

// Admin Auth and Dashboard Routes
Route::prefix($adminPath)->name('admin.')->group(function () {

    // Admin Auth Routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'verified', 'admin_access'])->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard'); // Explicit admin dashboard

        // Products Management Group
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/search', [ProductController::class, 'search'])->name('search');
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');

            Route::post('/{product}/shortlinks', [ShortlinkController::class, 'store'])->name('shortlinks.store');
            Route::delete('/shortlinks/{shortlink}', [ShortlinkController::class, 'destroy'])->name('shortlinks.destroy');

            // Media routes
            Route::post('/{product}/media/upload', [ProductMediaController::class, 'upload'])->name('media.upload');
            Route::post('/{product}/media/upload-preview', [ProductMediaController::class, 'uploadMasterPreview'])->name('media.upload-preview');
            Route::post('/{product}/media/upload-cat-preview', [ProductMediaController::class, 'uploadCategoryMasterPreview'])->name('media.upload-cat-preview');
            Route::delete('/{product}/media/delete-cat-preview', [ProductMediaController::class, 'deleteCategoryMasterPreview'])->name('media.delete-cat-preview');
            Route::delete('/{product}/media/{productImage}', [ProductMediaController::class, 'delete'])->name('media.delete');
            Route::post('/{product}/media/{productImage}/primary', [ProductMediaController::class, 'setPrimary'])->name('media.setPrimary');
            Route::post('/{product}/media/reorder', [ProductMediaController::class, 'reorder'])->name('media.reorder');
        });

        // Reuse existing upload controller but link it conceptually under products
        Route::get('/upload', [ProductUploadController::class, 'index'])->name('upload');
        Route::get('/upload/sample-qikink', [ProductUploadController::class, 'downloadSampleQikink'])->name('upload.sample-qikink');
        Route::get('/upload/sample-general', [ProductUploadController::class, 'downloadSampleGeneral'])->name('upload.sample-general');
        Route::post('/upload', [ProductUploadController::class, 'store'])->name('upload.store');

        // Categories
        Route::post('/categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
        Route::resource('categories', CategoryController::class);

        // Collections
        Route::resource('collections', CollectionController::class);

        // Attributes (Colors, Product Types)
        Route::get('/attributes/export/{type}', [AttributeImportExportController::class, 'export'])->name('attributes.export');
        Route::post('/attributes/import/{type}', [AttributeImportExportController::class, 'import'])->name('attributes.import');
        Route::get('/attributes/sample/{type}', [AttributeImportExportController::class, 'sample'])->name('attributes.sample');
        Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
        Route::post('/attributes/colors', [AttributeController::class, 'storeColor'])->name('attributes.colors.store');
        Route::put('/attributes/colors/{color}', [AttributeController::class, 'updateColor'])->name('attributes.colors.update');
        Route::delete('/attributes/colors/{color}', [AttributeController::class, 'destroyColor'])->name('attributes.colors.destroy');
        Route::post('/attributes/types', [AttributeController::class, 'storeType'])->name('attributes.types.store');
        Route::put('/attributes/types/{type}', [AttributeController::class, 'updateType'])->name('attributes.types.update');
        Route::delete('/attributes/types/{type}', [AttributeController::class, 'destroyType'])->name('attributes.types.destroy');
        Route::post('/attributes/sizes', [AttributeController::class, 'storeSize'])->name('attributes.sizes.store');
        Route::put('/attributes/sizes/{size}', [AttributeController::class, 'updateSize'])->name('attributes.sizes.update');
        Route::delete('/attributes/sizes/{size}', [AttributeController::class, 'destroySize'])->name('attributes.sizes.destroy');

        // Size Charts
        Route::resource('size-charts', SizeChartController::class);

        // Orders
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::patch('/orders/{order}/tracking', [OrderController::class, 'updateTracking'])->name('orders.updateTracking');

        // WhatsApp Chat & Templates
        Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
            Route::get('/', [WhatsAppController::class, 'index'])->name('index');
            Route::get('/unread-count', [WhatsAppController::class, 'unreadCount'])->name('unread-count');
            Route::get('/customers/search', [WhatsAppController::class, 'searchCustomers'])->name('customers.search');
            Route::post('/conversations/start', [WhatsAppController::class, 'startConversation'])->name('conversations.start');

            Route::get('/conversations/{conversation}/messages', [WhatsAppController::class, 'messages'])->name('messages');
            Route::post('/conversations/{conversation}/messages', [WhatsAppController::class, 'sendMessage'])->name('send');
            Route::post('/conversations/{conversation}/template', [WhatsAppController::class, 'sendTemplate'])->name('sendTemplate');

            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/', [WhatsAppTemplateController::class, 'index'])->name('index');
                Route::get('/create', [WhatsAppTemplateController::class, 'create'])->name('create');
                Route::post('/', [WhatsAppTemplateController::class, 'store'])->name('store');
                Route::post('/sync', [WhatsAppTemplateController::class, 'sync'])->name('sync');
            });
        });

        // Online Store
        Route::prefix('online-store')->name('online-store.')->group(function () {
            // Theme Settings removed and merged into General Settings

            // General Settings
            Route::get('/general-settings', [GeneralSettingsController::class, 'index'])->name('general-settings.index');
            Route::put('/general-settings', [GeneralSettingsController::class, 'update'])->name('general-settings.update');

            // Policy Settings
            Route::get('/policy-settings', [PolicySettingsController::class, 'index'])->name('policy-settings.index');
            Route::put('/policy-settings', [PolicySettingsController::class, 'update'])->name('policy-settings.update');

            // Delivery PIN Settings
            Route::get('/delivery-pins', [DeliveryPinController::class, 'index'])->name('delivery-pins.index');
            Route::post('/delivery-pins', [DeliveryPinController::class, 'update'])->name('delivery-pins.update');

            // Product Card Settings
            Route::get('/product-card-settings', [ProductCardSettingsController::class, 'index'])->name('product-card-settings.index');
            Route::put('/product-card-settings', [ProductCardSettingsController::class, 'update'])->name('product-card-settings.update');

            // PDP Settings
            Route::get('/pdp-settings', [PdpSettingsController::class, 'index'])->name('pdp-settings.index');
            Route::put('/pdp-settings', [PdpSettingsController::class, 'update'])->name('pdp-settings.update');

            // Auth Settings
            Route::get('/auth-settings', [AuthSettingsController::class, 'index'])->name('auth-settings.index');
            Route::put('/auth-settings', [AuthSettingsController::class, 'update'])->name('auth-settings.update');

            // Tax & Shipping
            Route::get('/tax-shipping', [TaxShippingSettingsController::class, 'index'])->name('tax-shipping.index');
            Route::put('/tax-shipping', [TaxShippingSettingsController::class, 'update'])->name('tax-shipping.update');

            // Integrations
            Route::get('/integrations', [IntegrationSettingsController::class, 'index'])->name('integrations.index');
            Route::get('/integrations/{slug}', [IntegrationSettingsController::class, 'show'])->name('integrations.show');
            Route::put('/integrations/{slug}', [IntegrationSettingsController::class, 'update'])->name('integrations.update');
            Route::post('/integrations/razorpay/test', [IntegrationSettingsController::class, 'testRazorpay'])->name('integrations.razorpay.test');
            Route::post('/integrations/qikink/test', [IntegrationSettingsController::class, 'testQikink'])->name('integrations.qikink.test');
            Route::post('/integrations/algolia/test', [IntegrationSettingsController::class, 'testAlgolia'])->name('integrations.algolia.test');

            // Navbar Settings
            Route::get('/navbar-settings', [NavbarSettingsController::class, 'index'])->name('navbar-settings.index');
            Route::put('/navbar-settings', [NavbarSettingsController::class, 'update'])->name('navbar-settings.update');

            // Coupons
            Route::resource('coupons', CouponController::class);

            // Marketing / Search Queries
            Route::prefix('marketing/search-queries')->name('marketing.search-queries.')->group(function () {
                Route::get('/', [SearchQueryController::class, 'index'])->name('index');
                Route::post('/export', [SearchQueryController::class, 'export'])->name('export');
                Route::delete('/delete-by-date', [SearchQueryController::class, 'deleteByDate'])->name('deleteByDate');
            });

            // Gift Cards – Templates
            Route::prefix('gift-cards')->name('gift-cards.')->group(function () {
                Route::get('/', [GiftCardController::class, 'index'])->name('index');
                Route::get('/create', [GiftCardController::class, 'create'])->name('create');
                Route::post('/', [GiftCardController::class, 'store'])->name('store');
                // Template actions (giftCard = GiftCardTemplate model)
                Route::get('/{giftCard}', [GiftCardController::class, 'show'])->name('show');
                Route::post('/{giftCard}/toggle', [GiftCardController::class, 'toggleTemplate'])->name('toggle');
                Route::delete('/{giftCard}', [GiftCardController::class, 'destroyTemplate'])->name('destroy');
                // Issued card actions
                Route::get('/cards/{card}', [GiftCardController::class, 'showCard'])->name('cards.show');
                Route::post('/cards/{card}/withdraw', [GiftCardController::class, 'withdraw'])->name('cards.withdraw');
            });

            // CMS Pages (Placeholder for next step)
            Route::post('/mnpages/upload-image', [PageUploadController::class, 'upload'])->name('mnpages.upload-image');
            Route::post('/mnpages/{mnpage}/auto-save', [App\Http\Controllers\Admin\PageController::class, 'autoSave'])->name('mnpages.auto-save');
            Route::post('/mnpages/{mnpage}/publish', [App\Http\Controllers\Admin\PageController::class, 'publish'])->name('mnpages.publish');
            Route::get('/mnpages/{mnpage}/design', [App\Http\Controllers\Admin\PageController::class, 'design'])->name('mnpages.design');
            Route::resource('mnpages', App\Http\Controllers\Admin\PageController::class);
        });

        // Customers
        Route::resource('customers', CustomerController::class)->only(['index', 'show']);

        // Reviews
        Route::get('/reviews', [App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
        Route::post('/reviews/{review}/reply', [App\Http\Controllers\Admin\ReviewController::class, 'reply'])->name('reviews.reply');
        Route::delete('/reviews/{review}', [App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

        // Admin Settings Section
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/admin', [AdminSettingController::class, 'index'])->name('index');
            Route::put('/admin', [AdminSettingController::class, 'update'])->name('update');

            Route::get('/users', [AdminSettingController::class, 'users'])->name('users');
            Route::post('/users', [AdminSettingController::class, 'storeUser'])->name('users.store');
            Route::put('/users/{user}', [AdminSettingController::class, 'updateUser'])->name('users.update');
            Route::delete('/users/{user}', [AdminSettingController::class, 'destroyUser'])->name('users.destroy');

            Route::get('/vyora', [AdminSettingController::class, 'vyora'])->name('vyora');

            // System Updates
            Route::get('/update', [SystemUpdateController::class, 'index'])->name('update.index');
            Route::post('/update', [SystemUpdateController::class, 'update'])->name('update.process');
            Route::post('/update/maintenance', [SystemUpdateController::class, 'toggleMaintenance'])->name('update.maintenance');
        });
    });

});
