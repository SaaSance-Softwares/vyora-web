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
            'social_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'products' => 'nullable|array',
            'products.*' => 'string|max:255',
        ]);

        $data = $request->except(['social_image']);
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('social_image')) {
            $file = $request->file('social_image');
            $fileName = time().'_'.$file->getClientOriginalName();
            $relativePath = 'storage/collections/social';
            $destinationPath = public_path($relativePath);
            if (! file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $fileName);
            $data['social_image'] = "{$relativePath}/{$fileName}";
        }

        Collection::create($data);

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
            'social_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'products' => 'nullable|array',
            'products.*' => 'string|max:255',
        ]);

        $data = [
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
            'social_title' => $request->social_title,
            'social_description' => $request->social_description,
        ];

        if ($request->hasFile('social_image')) {
            // Delete old image if exists
            if ($collection->social_image) {
                $oldPath = public_path("/{$collection->social_image}");
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $file = $request->file('social_image');
            $fileName = time().'_'.$file->getClientOriginalName();
            $relativePath = 'storage/collections/social';
            $destinationPath = public_path($relativePath);
            if (! file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $fileName);
            $data['social_image'] = "{$relativePath}/{$fileName}";
        }

        $collection->update($data);

        if ($request->has('products')) {
            $collection->products()->sync($request->products);
        } else {
            $collection->products()->detach();
        }

        return redirect()->route('admin.collections.index')->with('success', 'Collection updated successfully.');
    }

    public function destroy(Collection $collection)
    {
        $collection->delete();

        return redirect()->route('admin.collections.index')->with('success', 'Collection deleted successfully.');
    }
}
