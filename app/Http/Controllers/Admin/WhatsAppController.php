<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function index()
    {
        $conversations = WhatsappConversation::orderBy('last_message_at', 'desc')->get();
        $templates = \App\Models\WhatsappTemplate::where('status', 'APPROVED')->get();
        return view('admin.whatsapp.index', compact('conversations', 'templates'));
    }

    public function messages(WhatsappConversation $conversation)
    {
        // Mark inbound messages as read
        $conversation->messages()->where('direction', 'inbound')->where('status', '!=', 'read')->update(['status' => 'read']);

        $messages = $conversation->messages()->orderBy('created_at', 'asc')->get();
        
        return response()->json([
            'messages' => $messages->map(function($msg) {
                return [
                    'id' => $msg->id,
                    'direction' => $msg->direction,
                    'type' => $msg->type,
                    'body' => $msg->body,
                    'status' => $msg->status,
                    'created_at' => $msg->created_at->format('M d, H:i'),
                ];
            })
        ]);
    }

    public function sendMessage(Request $request, WhatsappConversation $conversation)
    {
        $request->validate([
            'message' => 'required|string'
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
        $request->validate([
            'template_name' => 'required|string'
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
        
        if (!$fetchAll && strlen($query) < 2) return response()->json([]);

        $customersQuery = \App\Models\User::where('role', 'user')
            ->whereNotNull('phone');
            
        if (!$fetchAll) {
            $customersQuery->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            });
        }

        $customers = $customersQuery->select('id', 'name', 'phone')
            ->orderBy('id', 'desc')
            ->limit($fetchAll ? 50 : 10)
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone
                ];
            });

        return response()->json($customers);
    }

    public function startConversation(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'name' => 'required|string',
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
                'phone' => $conversation->phone_number
            ]
        ]);
    }

    public function unreadCount()
    {
        $count = \App\Models\WhatsappMessage::where('direction', 'inbound')->where('status', '!=', 'read')->count();
        return response()->json(['count' => $count]);
    }
}
