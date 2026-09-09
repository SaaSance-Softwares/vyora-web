<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function index()
    {
        $conversations = WhatsappConversation::orderBy('last_message_at', 'desc')->get();
        $templates = WhatsappTemplate::where('status', 'APPROVED')->get();

        return view('admin.whatsapp.index', compact('conversations', 'templates'));
    }

    public function conversations()
    {
        $conversations = WhatsappConversation::withCount(['messages' => function ($query) {
            $query->where('direction', 'inbound')->where('status', '!=', 'read');
        }])->orderBy('last_message_at', 'desc')->get();

        return response()->json(['conversations' => $conversations]);
    }

    public function messages(WhatsappConversation $conversation)
    {
        // Mark inbound messages as read
        $conversation->messages()->where('direction', 'inbound')->where('status', '!=', 'read')->update(['status' => 'read']);

        $messages = $conversation->messages()->orderBy('created_at', 'asc')->get();
        $templates = WhatsappTemplate::get()->keyBy('name');
        
        $otpTemplateName = \App\Models\ThemeSetting::where('group', 'auth')->where('key', 'whatsapp_template')->value('value') ?? 'user_verification_otp';

        return response()->json([
            'messages' => $messages->map(function ($msg) use ($templates, $otpTemplateName) {
                $body = $msg->body;
                
                // Hide sensitive OTP messages from the admin chat interface
                // We check if the body contains common OTP template phrases or matches the template name
                if ($msg->type === 'template' && (
                    $msg->body === $otpTemplateName ||
                    stripos($msg->body, 'is your verification code') !== false ||
                    stripos($msg->body, 'do not share this code') !== false ||
                    stripos($msg->body, 'login OTP') !== false
                )) {
                    return [
                        'id' => $msg->id,
                        'direction' => $msg->direction,
                        'type' => 'template',
                        'body' => '🔒 Authentication OTP message sent (hidden for security)',
                        'status' => $msg->status,
                        'created_at' => $msg->created_at->format('M d, H:i'),
                    ];
                }
                
                // If it's a past message where we only stored the template ID
                if ($msg->type === 'template' && isset($templates[$msg->body])) {
                    $components = $templates[$msg->body]->components ?? [];
                    $templateText = '';
                    foreach ($components as $component) {
                        if (in_array($component['type'] ?? '', ['HEADER', 'BODY', 'FOOTER']) && !empty($component['text'])) {
                            $templateText .= $component['text'] . "\n\n";
                        }
                    }
                    if (trim($templateText)) {
                        $body = trim($templateText);
                    }
                }

                // Clean up unresolved variables (e.g., {{1}}) for past messages
                if ($msg->type === 'template') {
                    $body = preg_replace('/\{\{\d+\}\}/', '[Variable]', $body);
                }

                return [
                    'id' => $msg->id,
                    'direction' => $msg->direction,
                    'type' => $msg->type,
                    'body' => $body,
                    'status' => $msg->status,
                    'created_at' => $msg->created_at->format('M d, H:i'),
                ];
            }),
        ]);
    }

    public function sendMessage(Request $request, WhatsappConversation $conversation)
    {
        $request->strictValidate([
            'message' => 'required|string|max:5000',
        ]);

        $service = app(WhatsAppService::class);
        $success = $service->sendText($conversation->phone_number, $request->message);

        if ($success) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'error' => 'Failed to send message.'], 500);
    }

    public function sendTemplate(Request $request, WhatsappConversation $conversation)
    {
        $request->strictValidate([
            'template_name' => 'required|string|max:255',
        ]);

        $service = app(WhatsAppService::class);
        // By default, manual templates are sent in en_US without components.
        // More complex manual templates would require a form to fill variables.
        $success = $service->sendTemplate($conversation->phone_number, $request->template_name, 'en_US', []);

        if ($success) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'error' => 'Failed to send template.'], 500);
    }

    public function searchCustomers(Request $request)
    {
        $query = $request->get('q');
        $fetchAll = $request->get('all') == '1';

        if (! $fetchAll && strlen($query) < 2) {
            return response()->json([]);
        }

        $customersQuery = User::where('role', 'user')
            ->whereNotNull('phone');

        if (! $fetchAll) {
            $customersQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            });
        }

        $customers = $customersQuery->select('id', 'name', 'phone')
            ->orderBy('id', 'desc')
            ->limit($fetchAll ? 50 : 10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                ];
            });

        return response()->json($customers);
    }

    public function startConversation(Request $request)
    {
        $request->strictValidate([
            'phone' => 'required|string|max:255',
            'name' => 'required|string|max:255',
        ]);

        // Clean phone number (just in case)
        $phone = preg_replace('/[^0-9]/', '', $request->phone);

        $conversation = WhatsappConversation::firstOrCreate(
            ['phone_number' => $phone],
            ['customer_name' => $request->name, 'last_message_at' => now()]
        );

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'name' => $conversation->customer_name,
                'phone' => $conversation->phone_number,
            ],
        ]);
    }

    public function unreadCount()
    {
        $count = WhatsappMessage::where('direction', 'inbound')->where('status', '!=', 'read')->count();

        return response()->json(['count' => $count]);
    }
}
