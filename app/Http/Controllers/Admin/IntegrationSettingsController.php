<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\AuthenticationError;

class IntegrationSettingsController extends Controller
{
    // ── Integration Catalog ───────────────────────────────────────────────────

    private $integrations = [
        'razorpay' => [
            'name' => 'Razorpay',
            'description' => 'Accept UPI, Cards, Net Banking & Wallets via Razorpay',
            'icon' => 'razorpay',
            'status' => 'active',
        ],

        'algolia' => [
            'name' => 'Algolia Search',
            'description' => 'Lightning-fast, typo-tolerant search for your products',
            'icon' => 'search',
            'status' => 'active',
        ],
        'qikink' => [
            'name' => 'Qikink',
            'description' => 'Automated Print on Demand and Dropshipping fulfillment',
            'icon' => 'truck',
            'status' => 'active',
        ],
        'shiprocket' => [
            'name' => 'Shiprocket',
            'description' => 'Automated shipping and tracking via Shiprocket',
            'icon' => 'truck',
            'status' => 'active',
        ],
        'smtp' => [
            'name' => 'SMTP Email',
            'description' => 'Send transactional emails via a custom SMTP server',
            'icon' => 'mail',
            'status' => 'active',
        ],
        'zoho-books' => [
            'name' => 'Zoho Books',
            'description' => 'Sync orders with your Zoho Books accounting',
            'icon' => 'book',
            'status' => 'soon',
        ],
        'zoho-campaign' => [
            'name' => 'Zoho Campaigns',
            'description' => 'Email marketing automation via Zoho',
            'icon' => 'send',
            'status' => 'soon',
        ],
        'whatsapp' => [
            'name' => 'WhatsApp Business API',
            'description' => 'Connect WhatsApp for order notifications and customer support',
            'icon' => 'whatsapp',
            'status' => 'active',
        ],
        'google-analytics' => [
            'name' => 'Google Analytics',
            'description' => 'Track visitors and trace conversion data using GA4 properties',
            'icon' => 'google-analytics',
            'status' => 'active',
        ],
        'meta-pixel' => [
            'name' => 'Meta Pixel API',
            'description' => 'Track user behaviors and optimize Meta/Facebook ad campaigns',
            'icon' => 'meta-pixel',
            'status' => 'active',
        ],
        'bing-webmaster' => [
            'name' => 'Bing Webmaster',
            'description' => 'Submit sitemaps and index products with Microsoft Bing search engine',
            'icon' => 'bing',
            'status' => 'soon',
        ],
        'google-search-console' => [
            'name' => 'Google Search Console',
            'description' => 'Monitor Google Search performance and crawl status for your storefront',
            'icon' => 'google-search-console',
            'status' => 'active',
        ],
        'google-merchant' => [
            'name' => 'Google Merchant Center',
            'description' => 'Sync your products with Google Shopping feeds and free listings',
            'icon' => 'google-merchant',
            'status' => 'active',
        ],
        'ondc' => [
            'name' => 'ONDC Network Integration',
            'description' => 'List and sell products across the open commerce network in India',
            'icon' => 'ondc',
            'status' => 'soon',
        ],
        'social-login' => [
            'name' => 'Social Login Integration',
            'description' => 'Allow customers to log in using Google, Facebook, or Apple credentials',
            'icon' => 'social-login',
            'status' => 'soon',
        ],
        'twilio' => [
            'name' => 'SMS Integration (Twilio)',
            'description' => 'Send instant order tracking and verification notifications via Twilio SMS API',
            'icon' => 'twilio',
            'status' => 'active',
        ],
        'slack' => [
            'name' => 'Slack Integration',
            'description' => 'Receive instant notifications for new orders and store alerts in your Slack channels',
            'icon' => 'slack',
            'status' => 'soon',
        ],
    ];

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index()
    {
        // Attach live status from DB for each integration
        $settings = ThemeSetting::where('group', 'like', 'integration.%')->get()->keyBy(function ($s) {
            return $s->group.'.'.$s->key;
        });

        $integrations = collect($this->integrations)->map(function ($data, $slug) use ($settings) {
            $enabled = $settings->get("integration.{$slug}.enabled")?->value === '1';
            $mode = $settings->get("integration.{$slug}.mode")?->value ?? 'test';
            $data['enabled'] = $enabled;
            $data['mode'] = $mode;

            return $data;
        });

        return view('admin.integrations.index', compact('integrations'));
    }

    // ── Show Individual Integration ───────────────────────────────────────────

    public function show($slug)
    {
        if (! array_key_exists($slug, $this->integrations)) {
            abort(404);
        }

        $integration = $this->integrations[$slug];
        $rows = ThemeSetting::where('group', "integration.{$slug}")->get()->keyBy('key');

        // Pull saved settings (decrypt sensitive values)
        $saved = [
            'enabled' => $rows->get('enabled')?->value === '1',
            'mode' => $rows->get('mode')?->value ?? 'test',
            'app_id' => $rows->get('app_id') ? $this->maybeDecrypt($rows->get('app_id')->value) : '',
            'admin_api_key' => $rows->get('admin_api_key') ? $this->maskedSecret($rows->get('admin_api_key')->value) : '',
            'key_id' => $rows->get('key_id') ? $this->maybeDecrypt($rows->get('key_id')->value) : '',
            'key_secret' => $rows->get('key_secret') ? $this->maskedSecret($rows->get('key_secret')->value) : '',
            'client_secret' => $rows->get('client_secret') ? $this->maskedSecret($rows->get('client_secret')->value) : '',
            'measurement_id' => $rows->get('measurement_id') ? $this->maybeDecrypt($rows->get('measurement_id')->value) : '',
            'pixel_id' => $rows->get('pixel_id') ? $this->maybeDecrypt($rows->get('pixel_id')->value) : '',
            'access_token' => $rows->get('access_token') ? $this->maskedSecret($rows->get('access_token')->value) : '',
            'test_event_code' => $rows->get('test_event_code') ? $this->maybeDecrypt($rows->get('test_event_code')->value) : '',

            // Google Search Console
            'site_verification_code' => $rows->get('site_verification_code') ? $this->maybeDecrypt($rows->get('site_verification_code')->value) : '',

            // Shiprocket
            'shiprocket_email' => $rows->get('shiprocket_email') ? $this->maybeDecrypt($rows->get('shiprocket_email')->value) : '',
            'shiprocket_password' => $rows->get('shiprocket_password') ? $this->maskedSecret($rows->get('shiprocket_password')->value) : '',
            'shiprocket_webhook_token' => (function() use ($rows, $slug) {
                if ($slug === 'shiprocket') {
                    $tokenRow = $rows->get('shiprocket_webhook_token');
                    if (!$tokenRow) {
                        $tokenRow = \App\Models\ThemeSetting::create([
                            'group' => 'integration.shiprocket',
                            'key' => 'shiprocket_webhook_token',
                            'value' => \Illuminate\Support\Facades\Crypt::encryptString(\Illuminate\Support\Str::random(32)),
                            'type' => 'string'
                        ]);
                    }
                    return $this->maybeDecrypt($tokenRow->value);
                }
                return '';
            })(),

            // SMTP
            'smtp_host' => $rows->get('smtp_host') ? $this->maybeDecrypt($rows->get('smtp_host')->value) : '',
            'smtp_port' => $rows->get('smtp_port') ? $this->maybeDecrypt($rows->get('smtp_port')->value) : '',
            'smtp_username' => $rows->get('smtp_username') ? $this->maybeDecrypt($rows->get('smtp_username')->value) : '',
            'smtp_password' => $rows->get('smtp_password') ? $this->maskedSecret($rows->get('smtp_password')->value) : '',
            'smtp_encryption' => $rows->get('smtp_encryption') ? $this->maybeDecrypt($rows->get('smtp_encryption')->value) : '',
            'smtp_from_address' => $rows->get('smtp_from_address') ? $this->maybeDecrypt($rows->get('smtp_from_address')->value) : '',
            'smtp_from_name' => $rows->get('smtp_from_name') ? $this->maybeDecrypt($rows->get('smtp_from_name')->value) : '',

            // Twilio
            'twilio_sid' => $rows->get('twilio_sid') ? $this->maybeDecrypt($rows->get('twilio_sid')->value) : '',
            'twilio_auth_token' => $rows->get('twilio_auth_token') ? $this->maskedSecret($rows->get('twilio_auth_token')->value) : '',
            'twilio_phone_number' => $rows->get('twilio_phone_number') ? $this->maybeDecrypt($rows->get('twilio_phone_number')->value) : '',
            'twilio_template_confirmed' => $rows->get('twilio_template_confirmed') ? $this->maybeDecrypt($rows->get('twilio_template_confirmed')->value) : '',
            'twilio_template_shipped' => $rows->get('twilio_template_shipped') ? $this->maybeDecrypt($rows->get('twilio_template_shipped')->value) : '',
            'twilio_template_cancelled' => $rows->get('twilio_template_cancelled') ? $this->maybeDecrypt($rows->get('twilio_template_cancelled')->value) : '',

            // WhatsApp
            'whatsapp_access_token' => $rows->get('whatsapp_access_token') ? $this->maskedSecret($rows->get('whatsapp_access_token')->value) : '',
            'whatsapp_phone_id' => $rows->get('whatsapp_phone_id') ? $this->maybeDecrypt($rows->get('whatsapp_phone_id')->value) : '',
            'whatsapp_business_account_id' => $rows->get('whatsapp_business_account_id') ? $this->maybeDecrypt($rows->get('whatsapp_business_account_id')->value) : '',
            'whatsapp_webhook_verify_token' => $rows->get('whatsapp_webhook_verify_token') ? $this->maskedSecret($rows->get('whatsapp_webhook_verify_token')->value) : '',
            'whatsapp_template_confirmed' => $rows->get('whatsapp_template_confirmed') ? $this->maybeDecrypt($rows->get('whatsapp_template_confirmed')->value) : '',
            'whatsapp_template_shipped' => $rows->get('whatsapp_template_shipped') ? $this->maybeDecrypt($rows->get('whatsapp_template_shipped')->value) : '',
            'whatsapp_template_cancelled' => $rows->get('whatsapp_template_cancelled') ? $this->maybeDecrypt($rows->get('whatsapp_template_cancelled')->value) : '',
            'whatsapp_template_account_created' => $rows->get('whatsapp_template_account_created') ? $this->maybeDecrypt($rows->get('whatsapp_template_account_created')->value) : '',
            'whatsapp_template_password_updated' => $rows->get('whatsapp_template_password_updated') ? $this->maybeDecrypt($rows->get('whatsapp_template_password_updated')->value) : '',
            'whatsapp_template_abandoned_cart' => $rows->get('whatsapp_template_abandoned_cart') ? $this->maybeDecrypt($rows->get('whatsapp_template_abandoned_cart')->value) : '',
        ];

        return view("admin.integrations.{$slug}", compact('integration', 'slug', 'saved'));
    }

    // ── Save Settings ─────────────────────────────────────────────────────────

    public function update(Request $request, $slug)
    {
        if (! array_key_exists($slug, $this->integrations)) {
            abort(404);
        }

        if ($slug === 'algolia') {
            return $this->updateAlgolia($request);
        }

        if ($slug === 'razorpay') {
            return $this->updateRazorpay($request);
        }

        if ($slug === 'qikink') {
            return $this->updateQikink($request);
        }

        if ($slug === 'shiprocket') {
            return $this->updateShiprocket($request);
        }

        if ($slug === 'google-search-console') {
            return $this->updateGoogleSearchConsole($request);
        }

        if ($slug === 'whatsapp') {
            return $this->updateWhatsapp($request);
        }

        if ($slug === 'google-analytics') {
            return $this->updateGoogleAnalytics($request);
        }

        if ($slug === 'meta-pixel') {
            return $this->updateMetaPixel($request);
        }

        if ($slug === 'smtp') {
            return $this->updateSmtp($request);
        }

        if ($slug === 'twilio') {
            return $this->updateTwilio($request);
        }

        return redirect()->back()->with('success', 'Integration settings updated successfully.');
    }

    private function updateTwilio(Request $request)
    {
        $request->validate([
            'twilio_sid' => 'required|string',
            'twilio_auth_token' => 'nullable|string',
            'twilio_phone_number' => 'required|string',
            'twilio_template_confirmed' => 'required|string',
            'twilio_template_shipped' => 'required|string',
            'twilio_template_cancelled' => 'required|string',
        ]);

        $group = 'integration.twilio';

        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'enabled'], ['value' => $request->boolean('enabled') ? '1' : '0']);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'twilio_sid'], ['value' => Crypt::encryptString($request->twilio_sid), 'type' => 'string']);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'twilio_phone_number'], ['value' => Crypt::encryptString($request->twilio_phone_number), 'type' => 'string']);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'twilio_template_confirmed'], ['value' => Crypt::encryptString($request->twilio_template_confirmed), 'type' => 'string']);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'twilio_template_shipped'], ['value' => Crypt::encryptString($request->twilio_template_shipped), 'type' => 'string']);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'twilio_template_cancelled'], ['value' => Crypt::encryptString($request->twilio_template_cancelled), 'type' => 'string']);

        if ($request->filled('twilio_auth_token') && ! str_contains($request->twilio_auth_token, '****')) {
            ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'twilio_auth_token'], ['value' => Crypt::encryptString($request->twilio_auth_token), 'type' => 'string']);
        }

        return redirect()->back()->with('success', 'Twilio settings saved successfully.');
    }

    private function updateWhatsapp(Request $request)
    {
        $request->validate([
            'enabled' => 'nullable|boolean',
            'whatsapp_access_token' => 'nullable|string',
            'whatsapp_phone_id' => 'required|string',
            'whatsapp_business_account_id' => 'required|string',
            'whatsapp_webhook_verify_token' => 'nullable|string',
            'whatsapp_template_confirmed' => 'nullable|string',
            'whatsapp_template_shipped' => 'nullable|string',
            'whatsapp_template_cancelled' => 'nullable|string',
            'whatsapp_template_account_created' => 'nullable|string',
            'whatsapp_template_password_updated' => 'nullable|string',
            'whatsapp_template_abandoned_cart' => 'nullable|string',
        ]);

        $group = 'integration.whatsapp';
        $enabled = $request->has('enabled') ? '1' : '0';

        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'enabled'], ['value' => $enabled]);

        $fields = [
            'whatsapp_phone_id',
            'whatsapp_business_account_id',
            'whatsapp_template_confirmed',
            'whatsapp_template_shipped',
            'whatsapp_template_cancelled',
            'whatsapp_template_account_created',
            'whatsapp_template_password_updated',
            'whatsapp_template_abandoned_cart',
        ];

        foreach ($fields as $field) {
            if ($request->filled($field)) {
                ThemeSetting::updateOrCreate(['group' => $group, 'key' => $field], ['value' => Crypt::encryptString($request->$field), 'type' => 'string']);
            }
        }

        if ($request->filled('whatsapp_access_token') && ! str_contains($request->whatsapp_access_token, '****')) {
            ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'whatsapp_access_token'], ['value' => Crypt::encryptString($request->whatsapp_access_token), 'type' => 'string']);
        }

        if ($request->filled('whatsapp_webhook_verify_token') && ! str_contains($request->whatsapp_webhook_verify_token, '****')) {
            ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'whatsapp_webhook_verify_token'], ['value' => Crypt::encryptString($request->whatsapp_webhook_verify_token), 'type' => 'string']);
        }

        return redirect()->back()->with('success', 'WhatsApp settings saved successfully.');
    }

    private function updateSmtp(Request $request)
    {
        $request->validate([
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|string',
            'smtp_username' => 'required|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'nullable|string',
            'smtp_from_address' => 'required|email',
            'smtp_from_name' => 'required|string',
        ]);

        $group = 'integration.smtp';

        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'enabled'], ['value' => $request->boolean('enabled') ? '1' : '0']);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'smtp_host'], ['value' => Crypt::encryptString($request->smtp_host), 'type' => 'string']);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'smtp_port'], ['value' => Crypt::encryptString($request->smtp_port), 'type' => 'string']);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'smtp_username'], ['value' => Crypt::encryptString($request->smtp_username), 'type' => 'string']);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'smtp_encryption'], ['value' => Crypt::encryptString($request->smtp_encryption ?? ''), 'type' => 'string']);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'smtp_from_address'], ['value' => Crypt::encryptString($request->smtp_from_address), 'type' => 'string']);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'smtp_from_name'], ['value' => Crypt::encryptString($request->smtp_from_name), 'type' => 'string']);

        if ($request->filled('smtp_password') && ! str_contains($request->smtp_password, '****')) {
            ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'smtp_password'], ['value' => Crypt::encryptString($request->smtp_password), 'type' => 'string']);
        }

        return redirect()->back()->with('success', 'SMTP settings saved successfully.');
    }

    private function updateRazorpay(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:test,live',
            'key_id' => 'required|string',
            'key_secret' => 'required|string|min:4',
        ]);

        $group = 'integration.razorpay';

        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'enabled'], ['value' => $request->boolean('enabled') ? '1' : '0']);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'mode'], ['value' => $request->mode]);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'key_id'], ['value' => Crypt::encryptString($request->key_id)]);

        // Only update secret if not the masked placeholder
        if (! str_contains($request->key_secret, '****')) {
            ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'key_secret'], ['value' => Crypt::encryptString($request->key_secret)]);
        }

        return redirect()->back()->with('success', 'Razorpay settings saved successfully.');
    }

    // ── Test Connection (AJAX) ────────────────────────────────────────────────

    public function testRazorpay(Request $request)
    {
        $group = 'integration.razorpay';
        $rows = ThemeSetting::where('group', $group)->get()->keyBy('key');

        try {
            $keyId = $this->maybeDecrypt($rows->get('key_id')?->value ?? '');
            $keySecret = $this->maybeDecrypt($rows->get('key_secret')?->value ?? '');

            if (! $keyId || ! $keySecret) {
                return response()->json(['success' => false, 'message' => 'API credentials not configured. Save your keys first.']);
            }

            $api = new Api($keyId, $keySecret);

            // Lightweight read call — fetch last 1 payment (returns empty array if none, still auth-checks)
            $api->payment->all(['count' => 1]);

            return response()->json(['success' => true, 'message' => 'Connection successful! Razorpay credentials are valid.']);
        } catch (AuthenticationError $e) {
            return response()->json(['success' => false, 'message' => 'Authentication failed. Check your Key ID and Secret.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Connection failed: '.$e->getMessage()]);
        }
    }

    public function testQikink(Request $request)
    {
        $group = 'integration.qikink';
        $rows = ThemeSetting::where('group', $group)->get()->keyBy('key');

        try {
            $clientId = $this->maybeDecrypt($rows->get('client_id')?->value ?? '');
            $clientSecret = $this->maybeDecrypt($rows->get('client_secret')?->value ?? '');
            $mode = $rows->get('mode')?->value ?? 'test';

            if (! $clientId || ! $clientSecret) {
                return response()->json(['success' => false, 'message' => 'API credentials not configured. Save your keys first.']);
            }

            $endpoint = $mode === 'live' ? 'https://api.qikink.com/api/token' : 'https://sandbox.qikink.com/api/token';

            $response = Http::asForm()->post($endpoint, [
                'ClientId' => $clientId,
                'client_secret' => $clientSecret,
            ]);

            if ($response->successful() && $response->json('Accesstoken')) {
                return response()->json(['success' => true, 'message' => 'Connection successful! Qikink credentials are valid.']);
            }

            $errorDetail = $response->body();

            return response()->json(['success' => false, 'message' => 'Authentication failed. Check your Client ID and Secret. Details: '.$errorDetail]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Connection failed: '.$e->getMessage()]);
        }
    }

    public function testShiprocket(Request $request)
    {
        $group = 'integration.shiprocket';
        $rows = ThemeSetting::where('group', $group)->get()->keyBy('key');

        try {
            $email = $this->maybeDecrypt($rows->get('shiprocket_email')?->value ?? '');
            $password = $this->maybeDecrypt($rows->get('shiprocket_password')?->value ?? '');

            if (! $email || ! $password) {
                return response()->json(['success' => false, 'message' => 'API credentials not configured. Save your email and password first.']);
            }

            $response = Http::post('https://apiv2.shiprocket.in/v1/external/auth/login', [
                'email' => $email,
                'password' => $password,
            ]);

            if ($response->successful() && $response->json('token')) {
                return response()->json(['success' => true, 'message' => 'Connection successful! Shiprocket credentials are valid.']);
            }

            $errorDetail = $response->body();
            return response()->json(['success' => false, 'message' => 'Authentication failed. Check your Email and Password. Details: '.$errorDetail]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Connection failed: '.$e->getMessage()]);
        }
    }

    public function testAlgolia(Request $request)
    {
        $group = 'integration.algolia';
        $rows = ThemeSetting::where('group', $group)->get()->keyBy('key');

        try {
            $appId = $this->maybeDecrypt($rows->get('app_id')?->value ?? '');
            $apiKey = $this->maybeDecrypt($rows->get('admin_api_key')?->value ?? '');

            if (! $appId || ! $apiKey) {
                return response()->json(['success' => false, 'message' => 'API credentials not configured. Save your keys first.']);
            }

            // Using Algolia Search Client
            $client = AlgoliaAlgoliaSearchSearchClient::create($appId, $apiKey);
            // Verify by fetching indices
            $client->listIndices();

            return response()->json(['success' => true, 'message' => 'Connection successful! Algolia credentials are valid.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Authentication failed. Check your App ID and Admin API Key.']);
        }
    }

    private function updateAlgolia(Request $request)
    {
        $request->validate([
            'app_id' => 'required|string',
            'admin_api_key' => 'required|string|min:4',
        ]);

        $group = 'integration.algolia';

        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'enabled'], ['value' => $request->boolean('enabled') ? '1' : '0']);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'app_id'], ['value' => Crypt::encryptString($request->app_id)]);

        if (! str_contains($request->admin_api_key, '****')) {
            ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'admin_api_key'], ['value' => Crypt::encryptString($request->admin_api_key)]);
        }

        return redirect()->back()->with('success', 'Algolia settings saved successfully.');
    }

    private function updateQikink(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:test,live',
            'client_id' => 'required|string',
            'client_secret' => 'required|string|min:4',
        ]);

        $group = 'integration.qikink';

        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'enabled'], ['value' => $request->boolean('enabled') ? '1' : '0']);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'mode'], ['value' => $request->mode]);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'client_id'], ['value' => Crypt::encryptString($request->client_id)]);

        if (! str_contains($request->client_secret, '****')) {
            ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'client_secret'], ['value' => Crypt::encryptString($request->client_secret)]);
        }

        return redirect()->back()->with('success', 'Qikink settings saved successfully.');
    }

    private function updateShiprocket(Request $request)
    {
        $request->validate([
            'shiprocket_email' => 'required|email',
            'shiprocket_password' => 'required|string',
        ]);

        $group = 'integration.shiprocket';

        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'enabled'], ['value' => $request->boolean('enabled') ? '1' : '0']);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'shiprocket_email'], ['value' => Crypt::encryptString($request->shiprocket_email)]);

        if (! str_contains($request->shiprocket_password, '****')) {
            ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'shiprocket_password'], ['value' => Crypt::encryptString($request->shiprocket_password)]);
        }

        return redirect()->back()->with('success', 'Shiprocket settings saved successfully.');
    }

    private function updateGoogleSearchConsole(Request $request)
    {
        $data = $request->validate([
            'enabled' => 'nullable|boolean',
            'site_verification_code' => 'nullable|string|max:255',
        ]);

        $enabled = $request->has('enabled') ? '1' : '0';

        ThemeSetting::updateOrCreate(
            ['group' => 'integration.google-search-console', 'key' => 'enabled'],
            ['value' => $enabled]
        );

        if (isset($data['site_verification_code'])) {
            ThemeSetting::updateOrCreate(
                ['group' => 'integration.google-search-console', 'key' => 'site_verification_code'],
                ['value' => Crypt::encryptString($data['site_verification_code'])]
            );
        }

        return redirect()->route('admin.online-store.integrations.index')
            ->with('success', 'Google Search Console settings updated successfully.');
    }

    private function updateGoogleAnalytics(Request $request)
    {
        $request->validate([
            'measurement_id' => 'required|string',
        ]);

        $group = 'integration.google-analytics';

        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'enabled'], ['value' => $request->boolean('enabled') ? '1' : '0']);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'measurement_id'], ['value' => Crypt::encryptString($request->measurement_id)]);

        return redirect()->back()->with('success', 'Google Analytics settings saved successfully.');
    }

    private function updateMetaPixel(Request $request)
    {
        $request->validate([
            'pixel_id' => 'required|string',
        ]);

        $group = 'integration.meta-pixel';

        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'enabled'], ['value' => $request->boolean('enabled') ? '1' : '0']);
        ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'pixel_id'], ['value' => Crypt::encryptString($request->pixel_id)]);

        if ($request->filled('access_token') && ! str_contains($request->access_token, '****')) {
            ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'access_token'], ['value' => Crypt::encryptString($request->access_token)]);
        }

        if ($request->filled('test_event_code')) {
            ThemeSetting::updateOrCreate(['group' => $group, 'key' => 'test_event_code'], ['value' => Crypt::encryptString($request->test_event_code)]);
        } else {
            ThemeSetting::where('group', $group)->where('key', 'test_event_code')->delete();
        }

        return redirect()->back()->with('success', 'Meta Pixel settings saved successfully.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function maybeDecrypt(?string $value): string
    {
        if (! $value) {
            return '';
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Exception) {
            return $value; // Return as-is if not encrypted
        }
    }

    private function maskedSecret(?string $encryptedValue): string
    {
        if (! $encryptedValue) {
            return '';
        }
        try {
            $plain = Crypt::decryptString($encryptedValue);

            return substr($plain, 0, 4).str_repeat('*', max(0, strlen($plain) - 8)).substr($plain, -4);
        } catch (\Exception) {
            return '****';
        }
    }
}
