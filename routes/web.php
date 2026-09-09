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
use App\Http\Controllers\Admin\MobileAppController;
use App\Http\Controllers\Admin\NavbarSettingsController;
use App\Http\Controllers\Admin\FooterSettingsController;
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
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SizeChartController;
use App\Http\Controllers\Admin\SystemUpdateController;
use App\Http\Controllers\Admin\CustomCodeController;
use App\Http\Controllers\Admin\TaxShippingSettingsController;
use App\Http\Controllers\Admin\WhatsAppController;
use App\Http\Controllers\Admin\WhatsAppTemplateController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ReviewController;
use App\Http\Controllers\GoogleMerchantController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Admin\LegalPageController;
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

// Dynamic TXT and AI Feeds
Route::get('/robots.txt', function () {
    $content = "User-agent: *\nAllow: /\n";
    return response($content, 200)->header('Content-Type', 'text/plain');
});

Route::get('/llms.txt', function () {
    $appName = config('app.name', 'Vyora');
    $content = "# AI Conversational Feed for {$appName}\n\n";
    $content .= "Welcome to the AI feed for our application. This feed is designed to help language models understand our site.\n\n";
    
    // Categories
    $categories = \App\Models\Category::where('is_active', true)->whereNull('parent_id')->get();
    if ($categories->count() > 0) {
        $content .= "## Store Categories\n";
        foreach ($categories as $cat) {
            $url = url('/shop?category=' . $cat->slug);
            $content .= "- [{$cat->name}]({$url})\n";
        }
        $content .= "\n";
    }

    // Collections
    $collections = \App\Models\Collection::where('is_active', true)->get();
    if ($collections->count() > 0) {
        $content .= "## Curated Collections\n";
        foreach ($collections as $col) {
            $url = url('/shop?collection=' . $col->slug);
            $content .= "- [{$col->name}]({$url})\n";
        }
        $content .= "\n";
    }

    // Featured Products (limit to 50 to avoid massive files)
    $products = \App\Models\Product::with('skus')->where('is_active', true)
        ->orderBy('created_at', 'desc')
        ->limit(50)
        ->get();
    if ($products->count() > 0) {
        $content .= "## Featured Products\n";
        foreach ($products as $prod) {
            $url = url('/product/' . $prod->slug);
            $price = $prod->skus->min('price') ?? 0;
            $content .= "- [{$prod->name}]({$url}) - ₹{$price}\n";
        }
        $content .= "\n";
    }

    $content .= "## Capabilities\n";
    $content .= "- Browse products\n- Place orders\n- View gift cards\n";

    return response($content, 200)->header('Content-Type', 'text/plain');
});

Route::get('/llms-full.txt', function () {
    $appName = config('app.name', 'Vyora');
    $content = "# {$appName} - Full AI Context\n\n";
    $content .= "This is the comprehensive AI feed for {$appName}. It contains full details of all active products, categories, and policies.\n\n";

    // Products (All Active)
    $products = \App\Models\Product::with('skus')->where('is_active', true)->orderBy('created_at', 'desc')->get();
    if ($products->count() > 0) {
        $content .= "## All Products\n\n";
        foreach ($products as $prod) {
            $url = url('/product/' . $prod->slug);
            $price = $prod->skus->min('price') ?? 0;
            $mrp = $prod->skus->max('mrp') ?? 0;
            
            $content .= "### {$prod->name}\n";
            $content .= "- **Price:** ₹{$price}\n";
            if ($mrp) $content .= "- **MRP:** ₹{$mrp}\n";
            $content .= "- **URL:** {$url}\n";
            if ($prod->long_description) {
                $content .= "- **Description:** " . trim(preg_replace('/\s+/', ' ', strip_tags($prod->long_description))) . "\n";
            }
            $content .= "\n";
        }
    }

    // Categories
    $categories = \App\Models\Category::where('is_active', true)->get();
    if ($categories->count() > 0) {
        $content .= "## All Categories\n";
        foreach ($categories as $cat) {
            $url = url('/shop?category=' . $cat->slug);
            $content .= "- [{$cat->name}]({$url})\n";
        }
        $content .= "\n";
    }

    // Policies / Legal Pages
    if (class_exists(\App\Models\LegalPage::class)) {
        $policies = \App\Models\LegalPage::where('is_published', true)->get();
        if ($policies->count() > 0) {
            $content .= "## Store Policies\n\n";
            foreach ($policies as $policy) {
                $url = url('/' . $policy->slug);
                $content .= "### {$policy->title}\n";
                $content .= "- **URL:** {$url}\n\n";
                if ($policy->content) {
                    $content .= trim(strip_tags($policy->content)) . "\n\n";
                }
            }
        }
    }

    return response($content, 200)->header('Content-Type', 'text/plain');
});

Route::get('/llm.txt', function () {
    return redirect('/llms.txt');
});

Route::get('/security.txt', function () {
    $content = "Contact: security@" . request()->getHost() . "\n";
    $content .= "Expires: " . now()->addYear()->toIso8601String() . "\n";
    return response($content, 200)->header('Content-Type', 'text/plain');
});

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
    $favicon = \App\Models\ThemeSetting::where('key', 'favicon')->value('value');
    $logo = \App\Models\ThemeSetting::where('key', 'main_logo')->value('value');
    $ogImage = $favicon ? url($favicon) : ($logo ? url($logo) : url('/favicon.ico'));

    return Inertia::render('GiftCards/Index')->withViewData([
        'og_title' => 'Gift Cards',
        'og_description' => 'Give the perfect gift with our digital gift cards. Instantly delivered and easy to redeem.',
        'og_image' => $ogImage,
        'og_url' => url()->current(),
    ]);
})->name('frontend.gift-cards.index');

Route::get('/gift-cards/share/{token}', function ($token) {
    $favicon = \App\Models\ThemeSetting::where('key', 'favicon')->value('value');
    $logo = \App\Models\ThemeSetting::where('key', 'main_logo')->value('value');
    $ogImage = $favicon ? url($favicon) : ($logo ? url($logo) : url('/favicon.ico'));
    $storeName = \App\Models\ThemeSetting::where('key', 'store_name')->value('value') ?: 'our store';

    return Inertia::render('GiftCards/Share', ['token' => $token])->withViewData([
        'og_title' => 'You received a Gift Card!',
        'og_description' => "Open to view and redeem your gift card at {$storeName}.",
        'og_image' => $ogImage,
        'og_url' => url()->current(),
    ]);
})->name('frontend.gift-cards.share');

// User Dashboard Routes - Authentication is handled by React/Zustand Bearer Tokens
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

Route::get('/policy/{slug}', [PageController::class, 'legal'])->name('frontend.policy');
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
Route::prefix($adminPath)->name('admin.')->middleware(\App\Http\Middleware\AdminNoIndex::class)->group(function () {

    // Admin Auth Routes
        Route::get('/admin-manifest.json', [\App\Http\Controllers\Admin\AdminSettingController::class, 'manifest'])->name('manifest');
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle.auth.backoff');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/forgot-password', [\App\Http\Controllers\Admin\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Admin\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Admin\ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Admin\ForgotPasswordController::class, 'reset'])->name('password.update');

    Route::middleware(['auth', 'verified', 'admin_access'])->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard'); // Explicit admin dashboard
        Route::get('/dashboard/charts', [DashboardController::class, 'chartsData'])->name('dashboard.charts');

        // Products Management Group
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/export', [ProductController::class, 'export'])->name('export');
            Route::post('/bulk-update', [ProductController::class, 'bulkUpdate'])->name('bulk-update');
            Route::get('/search', [ProductController::class, 'search'])->name('search');
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/{product}/analytics', [ProductController::class, 'analytics'])->name('analytics');
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
        Route::post('/attributes/fits', [AttributeController::class, 'storeFit'])->name('attributes.fits.store');
        Route::put('/attributes/fits/{fit}', [AttributeController::class, 'updateFit'])->name('attributes.fits.update');
        Route::delete('/attributes/fits/{fit}', [AttributeController::class, 'destroyFit'])->name('attributes.fits.destroy');
        Route::post('/attributes/fabrics', [AttributeController::class, 'storeFabric'])->name('attributes.fabrics.store');
        Route::put('/attributes/fabrics/{fabric}', [AttributeController::class, 'updateFabric'])->name('attributes.fabrics.update');
        Route::delete('/attributes/fabrics/{fabric}', [AttributeController::class, 'destroyFabric'])->name('attributes.fabrics.destroy');

        // Size Charts
        Route::resource('size-charts', SizeChartController::class);

        // Orders
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::patch('/orders/{order}/payment', [OrderController::class, 'updatePaymentStatus'])->name('orders.updatePayment');
        Route::patch('/orders/{order}/tracking', [OrderController::class, 'updateTracking'])->name('orders.updateTracking');
        Route::post('/orders/{order}/shiprocket', [OrderController::class, 'sendToShiprocket'])->name('orders.shiprocket');
        Route::post('/orders/{order}/retry-qikink', [OrderController::class, 'retryQikink'])->name('orders.retryQikink');

        // Order Statuses
        Route::resource('order-statuses', \App\Http\Controllers\Admin\OrderStatusController::class)->except(['show']);
        // WhatsApp Chat & Templates
        Route::prefix('whatsapp')->name('whatsapp.')->group(function () {
            Route::get('/', [WhatsAppController::class, 'index'])->name('index');
            Route::get('/conversations', [WhatsAppController::class, 'conversations'])->name('conversations.list');
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
                Route::get('/{template}/edit', [WhatsAppTemplateController::class, 'edit'])->name('edit');
                Route::put('/{template}', [WhatsAppTemplateController::class, 'update'])->name('update');
                Route::delete('/{template}', [WhatsAppTemplateController::class, 'destroy'])->name('destroy');
                Route::post('/sync', [WhatsAppTemplateController::class, 'sync'])->name('sync');
            });
        });

        // Communication Templates
        Route::resource('sms-templates', \App\Http\Controllers\Admin\SmsTemplateController::class)->except(['show']);
        Route::resource('email-templates', \App\Http\Controllers\Admin\EmailTemplateController::class)->except(['show']);

        // Online Store
        Route::prefix('online-store')->name('online-store.')->group(function () {
            // Theme Settings removed and merged into General Settings

            // General Settings
            Route::get('/general-settings', [GeneralSettingsController::class, 'index'])->name('general-settings.index');
            Route::put('/general-settings', [GeneralSettingsController::class, 'update'])->name('general-settings.update');

            // Policy Settings
            Route::get('/policy-settings', [PolicySettingsController::class, 'index'])->name('policy-settings.index');
            Route::put('/policy-settings', [PolicySettingsController::class, 'update'])->name('policy-settings.update');

            // Delivery Timelines
            Route::get('/delivery-timelines', [\App\Http\Controllers\Admin\DeliveryTimelineController::class, 'index'])->name('delivery-timelines.index');
            Route::post('/delivery-timelines', [\App\Http\Controllers\Admin\DeliveryTimelineController::class, 'store'])->name('delivery-timelines.store');
            Route::get('/delivery-timelines/{deliveryTimeline}/edit', [\App\Http\Controllers\Admin\DeliveryTimelineController::class, 'edit'])->name('delivery-timelines.edit');
            Route::put('/delivery-timelines/{deliveryTimeline}', [\App\Http\Controllers\Admin\DeliveryTimelineController::class, 'update'])->name('delivery-timelines.update');
            Route::delete('/delivery-timelines/{deliveryTimeline}', [\App\Http\Controllers\Admin\DeliveryTimelineController::class, 'destroy'])->name('delivery-timelines.destroy');
            Route::post('/delivery-timelines/{deliveryTimeline}/set-default', [\App\Http\Controllers\Admin\DeliveryTimelineController::class, 'setDefault'])->name('delivery-timelines.set-default');

            // Localization Settings
            Route::get('/localization', [\App\Http\Controllers\Admin\LocalizationController::class, 'index'])->name('localization.index');
            Route::post('/localization/country', [\App\Http\Controllers\Admin\LocalizationController::class, 'storeCountry'])->name('localization.country.store');
            Route::post('/localization/country/{country}/upload-chunk', [\App\Http\Controllers\Admin\LocalizationController::class, 'uploadChunk'])->name('localization.country.upload-chunk');
            Route::delete('/localization/country/{country}/truncate', [\App\Http\Controllers\Admin\LocalizationController::class, 'truncateCountry'])->name('localization.country.truncate');
            Route::delete('/localization/country/{country}', [\App\Http\Controllers\Admin\LocalizationController::class, 'destroyCountry'])->name('localization.country.destroy');

            // Delivery PIN Settings
            Route::get('/delivery-pins', [DeliveryPinController::class, 'index'])->name('delivery-pins.index');
            Route::post('/delivery-pins', [DeliveryPinController::class, 'update'])->name('delivery-pins.update');

            // Product Card Settings
            Route::get('/product-card-settings', [ProductCardSettingsController::class, 'index'])->name('product-card-settings.index');
            Route::put('/product-card-settings', [ProductCardSettingsController::class, 'update'])->name('product-card-settings.update');

            // Custom Code Settings
            Route::get('/custom-code', [CustomCodeController::class, 'index'])->name('custom-code.index');
            Route::put('/custom-code', [CustomCodeController::class, 'update'])->name('custom-code.update');

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
            Route::post('/integrations/whatsapp/set-pin', [IntegrationSettingsController::class, 'setWhatsappPin'])->name('integrations.whatsapp.set-pin');
            Route::post('/integrations/razorpay/test', [IntegrationSettingsController::class, 'testRazorpay'])->name('integrations.razorpay.test');
            Route::post('/integrations/qikink/test', [IntegrationSettingsController::class, 'testQikink'])->name('integrations.qikink.test');
            Route::post('/integrations/qikink/test-order', [IntegrationSettingsController::class, 'sendQikinkTestOrder'])->name('integrations.qikink.test-order');
            Route::post('/integrations/algolia/test', [IntegrationSettingsController::class, 'testAlgolia'])->name('integrations.algolia.test');
            
            // Temporary Migration Route
            Route::get('/run-qikink-migration', function() {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                return "Migration Complete! The new Qikink statuses have been inserted into the database.";
            });


            Route::post('/integrations/algolia/sync', [IntegrationSettingsController::class, 'syncAlgolia'])->name('integrations.algolia.sync');
            Route::post('/integrations/shiprocket/test', [IntegrationSettingsController::class, 'testShiprocket'])->name('integrations.shiprocket.test');
            Route::post('/integrations/saasance-push/test', [IntegrationSettingsController::class, 'testSaasancePush'])->name('integrations.saasance-push.test');
            // Navbar Settings
            Route::get('/navbar-settings', [NavbarSettingsController::class, 'index'])->name('navbar-settings.index');
            Route::put('/navbar-settings', [NavbarSettingsController::class, 'update'])->name('navbar-settings.update');

            // Footer Settings
            Route::get('/footer-settings', [FooterSettingsController::class, 'index'])->name('footer-settings.index');
            Route::put('/footer-settings', [FooterSettingsController::class, 'update'])->name('footer-settings.update');

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
                Route::get('/{giftCard}/edit', [GiftCardController::class, 'edit'])->name('edit');
                Route::put('/{giftCard}', [GiftCardController::class, 'update'])->name('update');
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
        Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
        Route::resource('customers', CustomerController::class)->only(['index', 'show']);

        // Abandoned Carts
        Route::get('/abandoned-carts', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'index'])->name('abandoned-carts.index');
        Route::get('/abandoned-carts/{cart}', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'show'])->name('abandoned-carts.show');

        // Reviews
        Route::get('/reviews', [App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
        Route::post('/reviews/{review}/reply', [App\Http\Controllers\Admin\ReviewController::class, 'reply'])->name('reviews.reply');
        Route::delete('/reviews/{review}', [App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');

        // DPDP Mandate / Legal Pages
        Route::get('/legal-pages/{legalPage}/logs', [LegalPageController::class, 'logs'])->name('legal-pages.logs');
        Route::resource('legal-pages', LegalPageController::class)->except(['show']);

        // Marketing
        Route::prefix('marketing')->name('marketing.')->group(function () {
            Route::get('search-queries', [\App\Http\Controllers\Admin\SearchQueryController::class, 'index'])->name('search-queries.index');
        });

        // Newsletter Subscribers
        Route::get('/newsletter-subscribers', [\App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'index'])->name('newsletter-subscribers.index');
        Route::get('/newsletter-subscribers/export', [\App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'export'])->name('newsletter-subscribers.export');
        Route::delete('/newsletter-subscribers/{subscriber}', [\App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'destroy'])->name('newsletter-subscribers.destroy');

        // Mobile App
        Route::get('/mobile-app', [MobileAppController::class, 'index'])->name('mobile-app');

        // Admin Settings Section
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/admin', [AdminSettingController::class, 'index'])->name('index');
            Route::put('/admin', [AdminSettingController::class, 'update'])->name('update');

            Route::get('/cron', [\App\Http\Controllers\Admin\CronJobController::class, 'index'])->name('cron');

            Route::get('/users', [AdminSettingController::class, 'users'])->name('users');
            Route::post('/users', [AdminSettingController::class, 'storeUser'])->name('users.store');
            Route::put('/users/{user}', [AdminSettingController::class, 'updateUser'])->name('users.update');
            Route::delete('/users/{user}', [AdminSettingController::class, 'destroyUser'])->name('users.destroy');

            Route::get('/vyora', [AdminSettingController::class, 'vyora'])->name('vyora');
            Route::get('/logs', [\App\Http\Controllers\Admin\LogController::class, 'index'])->name('logs');
            Route::post('/logs/clear', [\App\Http\Controllers\Admin\LogController::class, 'clear'])->name('logs.clear');

            // System Updates
            Route::get('/update', [SystemUpdateController::class, 'index'])->name('update.index');
            Route::post('/update', [SystemUpdateController::class, 'update'])->name('update.process');
            Route::post('/update/maintenance', [SystemUpdateController::class, 'toggleMaintenance'])->name('update.maintenance');

            // Redirect Manager
            Route::post('/redirects/import', [\App\Http\Controllers\Admin\RedirectUrlController::class, 'importCsv'])->name('redirects.import');
            Route::resource('redirects', \App\Http\Controllers\Admin\RedirectUrlController::class);
        });
    });

});

use App\Http\Controllers\Auth\SocialLoginController;

// Social Login Routes
Route::middleware('throttle.auth.backoff')->group(function () {
    Route::get('/auth/social/consent', [SocialLoginController::class, 'showConsent'])->name('social.consent.show');
    Route::post('/auth/social/consent', [SocialLoginController::class, 'processConsent'])->name('social.consent.process');
    Route::get('/auth/{provider}/redirect', [SocialLoginController::class, 'redirect'])->name('social.redirect');
    Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'callback'])->name('social.callback');
});

    Route::get('/admin/debug/qikink-logs', function() {
        $logPath = storage_path('logs/laravel.log');
        if (!file_exists($logPath)) return 'No log file';
        return "<pre>" . shell_exec('grep -i "qikink" ' . escapeshellarg($logPath) . ' | tail -n 100') . "</pre>";
    });

// 301 Redirect Fallback
Route::fallback(function (\Illuminate\Http\Request $request) {
    $path = ltrim($request->path(), '/');
    $fullUrl = $request->fullUrl();
    
    // Check if path exists in redirects
    $redirect = \App\Models\RedirectUrl::where('is_active', true)
        ->where(function($q) use ($path, $fullUrl) {
            $q->where('old_url', $path)
              ->orWhere('old_url', '/' . $path)
              ->orWhere('old_url', url($path))
              ->orWhere('old_url', $fullUrl)
              ->orWhere('old_url', str_replace('https://', 'http://', $fullUrl))
              ->orWhere('old_url', str_replace('http://', 'https://', $fullUrl));
              
            if ($path !== '' && $path !== '/') {
                $q->orWhere('old_url', 'LIKE', '%/' . $path);
            }
        })->first();

    if ($redirect && $redirect->new_url) {
        return redirect($redirect->new_url, 301);
    }

    // Default 404 behavior for Inertia
    // If the frontend has a 404 page, we can return Inertia 404, but standard abort(404) is caught by Laravel's exception handler which renders Inertia correctly.
    abort(404);
});

Route::get('/cart/recover/{token}', [\App\Http\Controllers\Api\CartController::class, 'recover'])->name('cart.recover');
