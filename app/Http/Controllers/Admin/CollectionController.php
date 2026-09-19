<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = Collection::latest()->get();
        $stats = [
            'total' => Collection::count(),
            'active' => Collection::where('is_active', true)->count(),
        ];

        return view('admin.collections.index', compact('collections', 'stats'));
    }

    public function create()
    {
        return view('admin.collections.create');
    }

    public function store(Request $request)
    {
        $request->strictValidate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:collections',
            'description' => 'nullable|string|max:5000',
            'is_active' => 'boolean',
            'social_title' => 'nullable|string|max:255',
            'social_description' => 'nullable|string|max:5000',
            'meta_keywords' => 'nullable|string|max:1000',
            'aeo_use_case' => 'nullable|string|max:255',
            'social_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'products' => 'nullable|array',
            'products.*' => 'string|max:255',
            'faqs' => 'nullable|array',
            'faqs.*.question' => 'required_with:faqs|string|max:1000',
            'faqs.*.answer' => 'required_with:faqs|string',
        ]);

        $data = $request->except(['social_image', 'faqs']);
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('social_image')) {
            $data['social_image'] = $this->uploadImageAsWebp($request->file('social_image'), 'social', 'storage/collections');
        }


        if ($request->hasFile('banner_image')) {
            $data['banner_image'] = $this->uploadImageAsWebp($request->file('banner_image'), 'banner', 'storage/collections');
        }
        $collection = Collection::create($data);

        if ($request->has('faqs') && is_array($request->faqs)) {
            foreach ($request->faqs as $index => $faqData) {
                if (!empty($faqData['question']) && !empty($faqData['answer'])) {
                    $collection->faqs()->create([
                        'question' => $faqData['question'],
                        'answer' => $faqData['answer'],
                        'sort_order' => $index
                    ]);
                }
            }
        }
        return redirect()->route('admin.collections.index')->with('success', 'Collection created successfully.');
    }

    public function edit(Collection $collection)
    {
        $collection->load('products');

        return view('admin.collections.edit', compact('collection'));
    }

    public function update(Request $request, Collection $collection)
    {
        $request->strictValidate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:collections,slug,'.$collection->id,
            'description' => 'nullable|string|max:5000',
            'is_active' => 'boolean',
            'social_title' => 'nullable|string|max:255',
            'social_description' => 'nullable|string|max:5000',
            'meta_keywords' => 'nullable|string|max:1000',
            'aeo_use_case' => 'nullable|string|max:255',
            'social_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'products' => 'nullable|array',
            'products.*' => 'string|max:255',
            'faqs' => 'nullable|array',
            'faqs.*.question' => 'required_with:faqs|string|max:1000',
            'faqs.*.answer' => 'required_with:faqs|string',
        ]);

        $data = [
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'social_title' => $request->social_title,
            'social_description' => $request->social_description,
            'meta_keywords' => $request->meta_keywords,
            'aeo_use_case' => $request->aeo_use_case,
        ];

        if ($request->hasFile('social_image')) {
            if ($collection->social_image) {
                $oldPath = public_path("/{$collection->social_image}");
                if (file_exists($oldPath)) @unlink($oldPath);
            }
            $data['social_image'] = $this->uploadImageAsWebp($request->file('social_image'), 'social', 'storage/collections');
        }


        if ($request->hasFile('banner_image')) {
            if ($collection->banner_image) {
                $oldPath = public_path("/{$collection->banner_image}");
                if (file_exists($oldPath)) @unlink($oldPath);
            }
            $data['banner_image'] = $this->uploadImageAsWebp($request->file('banner_image'), 'banner', 'storage/collections');
        }
        $collection->update($data);

        if ($request->has('products')) {
            $collection->products()->sync($request->products);
        } else {
            $collection->products()->detach();
        }

        if ($request->has('faqs')) {
            $collection->faqs()->delete();
            if (is_array($request->faqs)) {
                foreach ($request->faqs as $index => $faqData) {
                    if (!empty($faqData['question']) && !empty($faqData['answer'])) {
                        $collection->faqs()->create([
                            'question' => $faqData['question'],
                            'answer' => $faqData['answer'],
                            'sort_order' => $index
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.collections.index')->with('success', 'Collection updated successfully.');
    }

    public function destroy(Collection $collection)
    {
        $collection->delete();

        return redirect()->route('admin.collections.index')->with('success', 'Collection deleted successfully.');
    }

    protected function uploadImageAsWebp($file, $prefix, $relativePath)
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName = time() . '_' . $prefix . '_' . \Illuminate\Support\Str::slug($originalName) . '.webp';
        $destinationPath = public_path($relativePath);
        if (! file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        
        \Intervention\Image\Laravel\Facades\Image::read($file)->toWebp(80)->save($destinationPath . '/' . $fileName);
        
        return $relativePath . '/' . $fileName;
    }
}