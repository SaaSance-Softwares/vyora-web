<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::latest()->paginate(10);
        return view('admin.email-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.email-templates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'status' => 'boolean',
        ]);

        $variables = $this->extractVariables($request->body);

        EmailTemplate::create([
            'name' => $request->name,
            'subject' => $request->subject,
            'body' => $request->body,
            'variables' => $variables,
            'status' => $request->has('status'),
        ]);

        return redirect()->route('admin.email-templates.index')->with('success', 'Email Template created successfully.');
    }

    public function edit(EmailTemplate $emailTemplate)
    {
        return view('admin.email-templates.edit', compact('emailTemplate'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'status' => 'boolean',
        ]);

        $variables = $this->extractVariables($request->body);

        $emailTemplate->update([
            'name' => $request->name,
            'subject' => $request->subject,
            'body' => $request->body,
            'variables' => $variables,
            'status' => $request->has('status'),
        ]);

        return redirect()->route('admin.email-templates.index')->with('success', 'Email Template updated successfully.');
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();
        return redirect()->route('admin.email-templates.index')->with('success', 'Email Template deleted successfully.');
    }

    private function extractVariables($content)
    {
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $content, $matches);
        return array_values(array_unique($matches[1] ?? []));
    }
}
