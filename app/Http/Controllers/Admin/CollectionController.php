<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = \App\Models\Collection::latest()->get();
        $stats = [
            'total' => \App\Models\Collection::count(),
            'active' => \App\Models\Collection::where('is_active', true)->count(),
        ];
        return view('admin.collections.index', compact('collections', 'stats'));
    }

    public function create()
    {
        return view('admin.collections.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:collections',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        \App\Models\Collection::create($request->all());

        return redirect()->route('admin.collections.index')->with('success', 'Collection created successfully.');
    }

    public function edit(\App\Models\Collection $collection)
    {
        $collection->load('products');
        return view('admin.collections.edit', compact('collection'));
    }

    public function update(Request $request, \App\Models\Collection $collection)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:collections,slug,' . $collection->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $collection->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        if ($request->has('products')) {
            $collection->products()->sync($request->products);
        } else {
            $collection->products()->detach();
        }

        return redirect()->route('admin.collections.index')->with('success', 'Collection updated successfully.');
    }

    public function destroy(\App\Models\Collection $collection)
    {
        $collection->delete();
        return redirect()->route('admin.collections.index')->with('success', 'Collection deleted successfully.');
    }
}
