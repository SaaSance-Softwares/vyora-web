<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappTemplate;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

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
                        'language' => $template['language'],
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
        $variablesMapping = [];

        // Header Component
        if ($request->header_type === 'TEXT') {
            $headerText = $request->header_text;
            $header = [
                'type' => 'HEADER',
                'format' => 'TEXT',
                'text' => $headerText,
            ];

            // Check for variable in header
            if (preg_match('/\{([a-zA-Z0-9_]+)\}/', $headerText, $matches)) {
                $varName = $matches[1];
                $variablesMapping['header'] = [$varName];
                $header['text'] = str_replace('{' . $varName . '}', '{{1}}', $headerText);

                if ($request->filled('header_example')) {
                    $header['example'] = ['header_text' => [$request->header_example]];
                }
            }
            $components[] = $header;
        }

        // Body Component
        $bodyText = $request->body_text;
        $bodyVars = [];
        
        if (preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $bodyText, $matches)) {
            $uniqueVars = array_values(array_unique($matches[1]));
            $varIndex = 1;
            foreach ($uniqueVars as $varName) {
                $bodyVars[] = $varName;
                $bodyText = str_replace('{' . $varName . '}', '{{' . $varIndex . '}}', $bodyText);
                $varIndex++;
            }
            $variablesMapping['body'] = $bodyVars;
        }

        $body = [
            'type' => 'BODY',
            'text' => $bodyText,
        ];

        if (! empty($request->body_examples) && ! empty($bodyVars)) {
            $examplesArray = [];
            foreach ($bodyVars as $varName) {
                $examplesArray[] = $request->body_examples[$varName] ?? 'Example';
            }
            if (!empty($examplesArray)) {
                $body['example'] = ['body_text' => [$examplesArray]];
            }
        }
        $components[] = $body;

        // Footer Component
        if ($request->filled('footer_text')) {
            $components[] = [
                'type' => 'FOOTER',
                'text' => $request->footer_text,
            ];
        }

        // Buttons Component
        if (! empty($request->buttons)) {
            $buttons = [];
            $btnIndex = 0;
            foreach ($request->buttons as $btn) {
                if ($btn['type'] === 'QUICK_REPLY') {
                    $buttons[] = [
                        'type' => 'QUICK_REPLY',
                        'text' => $btn['text'],
                    ];
                } elseif ($btn['type'] === 'URL') {
                    $btnUrl = $btn['url'];
                    $button = [
                        'type' => 'URL',
                        'text' => $btn['text'],
                        'url' => $btnUrl,
                    ];
                    
                    if (preg_match('/\{([a-zA-Z0-9_]+)\}/', $btnUrl, $matches)) {
                        $varName = $matches[1];
                        if (!isset($variablesMapping['buttons'])) {
                            $variablesMapping['buttons'] = [];
                        }
                        $variablesMapping['buttons'][$btnIndex] = [$varName];
                        $button['url'] = str_replace('{' . $varName . '}', '{{1}}', $btnUrl);
                        
                        if (! empty($btn['url_example'])) {
                            $button['example'] = [$btn['url_example']];
                        }
                    }
                    $buttons[] = $button;
                } elseif ($btn['type'] === 'PHONE_NUMBER') {
                    $buttons[] = [
                        'type' => 'PHONE_NUMBER',
                        'text' => $btn['text'],
                        'phone_number' => $btn['phone_number'],
                    ];
                }
                $btnIndex++;
            }
            if (! empty($buttons)) {
                $components[] = [
                    'type' => 'BUTTONS',
                    'buttons' => $buttons,
                ];
            }
        }

        $payload = [
            'name' => $request->name,
            'language' => $request->language,
            'category' => $request->category,
            'components' => $components,
        ];

        $response = $service->createTemplateToMeta($payload);

        if (isset($response['error'])) {
            return back()->with('error', $response['error'])->withInput();
        }

        // Save mapping to database so it's not lost when we sync
        WhatsappTemplate::updateOrCreate(
            [
                'name' => $request->name,
                'language' => $request->language,
            ],
            [
                'category' => $request->category,
                'status' => 'PENDING',
                'components' => $components,
                'variables_mapping' => empty($variablesMapping) ? null : $variablesMapping,
            ]
        );

        return redirect()->route('admin.whatsapp.templates.index')->with('success', 'Template created successfully and submitted to Meta for approval.');
    }
}
