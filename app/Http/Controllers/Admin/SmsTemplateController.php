<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsTemplate;
use Illuminate\Http\Request;

class SmsTemplateController extends Controller
{
    public function index()
    {
        $templates = SmsTemplate::latest()->paginate(10);
        return view('admin.sms-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.sms-templates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'status' => 'boolean',
        ]);

        $variables = $this->extractVariables($request->content);

        SmsTemplate::create([
            'name' => $request->name,
            'content' => $request->content,
            'variables' => $variables,
            'status' => $request->has('status'),
        ]);

        return redirect()->route('admin.sms-templates.index')->with('success', 'SMS Template created successfully.');
    }

    public function edit(SmsTemplate $smsTemplate)
    {
        return view('admin.sms-templates.edit', compact('smsTemplate'));
    }

    public function update(Request $request, SmsTemplate $smsTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'status' => 'boolean',
        ]);

        $variables = $this->extractVariables($request->content);

        $smsTemplate->update([
            'name' => $request->name,
            'content' => $request->content,
            'variables' => $variables,
            'status' => $request->has('status'),
        ]);

        return redirect()->route('admin.sms-templates.index')->with('success', 'SMS Template updated successfully.');
    }

    public function destroy(SmsTemplate $smsTemplate)
    {
        $smsTemplate->delete();
        return redirect()->route('admin.sms-templates.index')->with('success', 'SMS Template deleted successfully.');
    }

    private function extractVariables($content)
    {
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $content, $matches);
        return array_values(array_unique($matches[1] ?? []));
    }
}
