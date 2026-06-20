<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle Meta's Webhook Verification Challenge
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $savedTokenStr = ThemeSetting::where('group', 'integration.whatsapp')->where('key', 'whatsapp_webhook_verify_token')->value('value');
        $savedToken = $savedTokenStr ? Crypt::decryptString($savedTokenStr) : null;

        if ($mode && $token) {
            if ($mode === 'subscribe' && $token === $savedToken) {
                return response($challenge, 200);
            }

            return response('Forbidden', 403);
        }

        return response('Bad Request', 400);
    }

    /**
     * Handle Incoming Messages and Events
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        if (isset($payload['object']) && $payload['object'] === 'whatsapp_business_account') {

            foreach ($payload['entry'] as $entry) {
                foreach ($entry['changes'] as $change) {
                    if ($change['value']['messaging_product'] === 'whatsapp') {

                        // Check if it's an incoming message
                        if (isset($change['value']['messages'])) {
                            $messages = $change['value']['messages'];
                            $contacts = $change['value']['contacts'] ?? [];

                            $customerName = $contacts[0]['profile']['name'] ?? 'Unknown';

                            foreach ($messages as $message) {
                                $customerPhone = $message['from'];
                                app(WhatsAppService::class)->processIncomingMessage($message, $customerPhone, $customerName);
                            }
                        }

                        // We can also handle delivery statuses here if needed
                        // if (isset($change['value']['statuses'])) { ... }
                    }
                }
            }

            return response('EVENT_RECEIVED', 200);
        }

        return response('Not Found', 404);
    }
}
