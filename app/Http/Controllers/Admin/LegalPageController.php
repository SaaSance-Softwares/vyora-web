<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalPage;
use App\Models\LegalPageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LegalPageController extends Controller
{
    public function index()
    {
        $pages = LegalPage::latest('updated_at')->get();
        return view('admin.legal_pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.legal_pages.create');
    }

    public function store(Request $request)
    {
        $data = $request->strictValidate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:legal_pages,slug',
            'content' => 'required|string|max:2000000',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:5000',
            'meta_image' => 'nullable|image|max:2048',
            'is_published' => 'boolean',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if ($request->hasFile('meta_image')) {
            $data['meta_image'] = $request->file('meta_image')->store('legal', 'public');
        }

        $data['is_published'] = $request->boolean('is_published');
        $data['is_mandatory'] = false; // Manually created pages are not mandatory by default

        $page = LegalPage::create($data);

        LegalPageLog::create([
            'legal_page_id' => $page->id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'content_snapshot' => $page->content,
        ]);

        return redirect()->route('admin.legal-pages.index')->with('success', 'Page created successfully.');
    }

    public function edit(LegalPage $legalPage)
    {
        return view('admin.legal_pages.edit', compact('legalPage'));
    }

    public function update(Request $request, LegalPage $legalPage)
    {
        $data = $request->strictValidate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:legal_pages,slug,' . $legalPage->id,
            'content' => 'required|string|max:2000000',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:5000',
            'meta_image' => 'nullable|image|max:2048',
            'is_published' => 'boolean',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        if ($request->hasFile('meta_image')) {
            $data['meta_image'] = $request->file('meta_image')->store('legal', 'public');
        }

        $data['is_published'] = $request->boolean('is_published');

        $oldContent = $legalPage->content;
        $legalPage->update($data);

        if ($oldContent !== $legalPage->content) {
            LegalPageLog::create([
                'legal_page_id' => $legalPage->id,
                'user_id' => auth()->id(),
                'action' => 'updated',
                'content_snapshot' => $legalPage->content,
            ]);
        }

        return redirect()->route('admin.legal-pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy(LegalPage $legalPage)
    {
        if ($legalPage->is_mandatory) {
            return redirect()->route('admin.legal-pages.index')->with('error', 'Mandatory DPDP pages cannot be deleted.');
        }

        $legalPage->delete();
        return redirect()->route('admin.legal-pages.index')->with('success', 'Page deleted successfully.');
    }

    public function logs(LegalPage $legalPage)
    {
        $logs = $legalPage->logs()->with('user')->get();
        return view('admin.legal_pages.logs', compact('legalPage', 'logs'));
    }
}
