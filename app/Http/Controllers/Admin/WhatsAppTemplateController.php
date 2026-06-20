<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappTemplate;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppTemplateController extends Controller
{
    public function index()
    {
        $templates = WhatsappTemplate::orderBy('name')->get();
        return view('admin.whatsapp.templates.index', compact('templates'));
    }

    public function sync(WhatsAppService $service)
    {
        $response = $service->getTemplatesFromMeta();

        if (isset($response['error'])) {
            return back()->with('error', $response['error']);
        }

        if (isset($response['data'])) {
            $syncedCount = 0;
            foreach ($response['data'] as $template) {
                WhatsappTemplate::updateOrCreate(
                    [
                        'name' => $template['name'],
                        'language' => $template['language']
                    ],
                    [
                        'category' => $template['category'],
                        'status' => $template['status'],
                        'components' => $template['components'] ?? [],
                    ]
                );
                $syncedCount++;
            }
            return back()->with('success', "Successfully synced {$syncedCount} templates from Meta.");
        }

        return back()->with('error', 'Failed to parse response from Meta.');
    }

    public function create()
    {
        return view('admin.whatsapp.templates.create');
    }

    public function store(Request $request, WhatsAppService $service)
    {
        $request->validate([
            'name' => 'required|string|regex:/^[a-z0-9_]+$/',
            'category' => 'required|string|in:MARKETING,UTILITY,AUTHENTICATION',
            'language' => 'required|string',
            'body_text' => 'required|string|max:1024',
            'header_type' => 'nullable|string|in:NONE,TEXT',
            'header_text' => 'nullable|string|max:60|required_if:header_type,TEXT',
            'footer_text' => 'nullable|string|max:60',
            'body_examples' => 'nullable|array',
            'buttons' => 'nullable|array',
        ]);

        $components = [];

        // Header Component
        if ($request->header_type === 'TEXT') {
            $header = [
                'type' => 'HEADER',
                'format' => 'TEXT',
                'text' => $request->header_text
            ];
            // Check for variable in header
            if (preg_match('/\{\{1\}\}/', $request->header_text) && $request->filled('header_example')) {
                $header['example'] = ['header_text' => [$request->header_example]];
            }
            $components[] = $header;
        }

        // Body Component
        $body = [
            'type' => 'BODY',
            'text' => $request->body_text
        ];
        if (!empty($request->body_examples)) {
            $body['example'] = ['body_text' => [array_values($request->body_examples)]];
        }
        $components[] = $body;

        // Footer Component
        if ($request->filled('footer_text')) {
            $components[] = [
                'type' => 'FOOTER',
                'text' => $request->footer_text
            ];
        }

        // Buttons Component
        if (!empty($request->buttons)) {
            $buttons = [];
            foreach ($request->buttons as $btn) {
                if ($btn['type'] === 'QUICK_REPLY') {
                    $buttons[] = [
                        'type' => 'QUICK_REPLY',
                        'text' => $btn['text']
                    ];
                } elseif ($btn['type'] === 'URL') {
                    $button = [
                        'type' => 'URL',
                        'text' => $btn['text'],
                        'url' => $btn['url']
                    ];
                    // If URL is dynamic (contains {{1}}), add example
                    if (preg_match('/\{\{1\}\}/', $btn['url']) && !empty($btn['url_example'])) {
                        $button['example'] = [$btn['url_example']];
                    }
                    $buttons[] = $button;
                } elseif ($btn['type'] === 'PHONE_NUMBER') {
                    $buttons[] = [
                        'type' => 'PHONE_NUMBER',
                        'text' => $btn['text'],
                        'phone_number' => $btn['phone_number']
                    ];
                }
            }
            if (!empty($buttons)) {
                $components[] = [
                    'type' => 'BUTTONS',
                    'buttons' => $buttons
                ];
            }
        }

        $payload = [
            'name' => $request->name,
            'language' => $request->language,
            'category' => $request->category,
            'components' => $components
        ];

        $response = $service->createTemplateToMeta($payload);

        if (isset($response['error'])) {
            return back()->with('error', $response['error'])->withInput();
        }

        $service->getTemplatesFromMeta(); 
        
        return redirect()->route('admin.whatsapp.templates.index')->with('success', 'Template created successfully and submitted to Meta for approval. Please Sync to see updates.');
    }
}
