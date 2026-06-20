<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = \App\Models\Category::whereNull('parent_id')
            ->orderBy('sort_order', 'asc')
            ->with([
                'children' => function ($query) {
                    $query->orderBy('sort_order', 'asc');
                }
            ])
            ->get();

        $stats = [
            'total' => \App\Models\Category::count(),
            'active' => \App\Models\Category::where('is_active', true)->count(),
            'root' => $categories->count()
        ];

        return view('admin.categories.index', compact('categories', 'stats'));
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
        ]);

        $this->updateCategoryOrder($request->categories, null);

        return response()->json(['success' => true]);
    }

    private function updateCategoryOrder(array $categories, ?int $parentId)
    {
        foreach ($categories as $index => $categoryData) {
            $category = \App\Models\Category::find($categoryData['id']);
            if ($category) {
                $category->update([
                    'sort_order' => $index,
                    'parent_id' => $parentId
                ]);

                if (isset($categoryData['children']) && !empty($categoryData['children'])) {
                    $this->updateCategoryOrder($categoryData['children'], $category->id);
                }
            }
        }
    }

    public function create()
    {
        // Flatten list for parent selection (simple implementation for now)
        $categories = \App\Models\Category::all();
        return view('admin.categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'social_image' => 'nullable|image',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $relativePath = "storage/categories";
            $destinationPath = public_path($relativePath);
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $fileName);
            $data['image'] = "{$relativePath}/{$fileName}";
        }

        if ($request->hasFile('social_image')) {
            $file = $request->file('social_image');
            $fileName = time() . '_social_' . $file->getClientOriginalName();
            $relativePath = "storage/categories";
            $destinationPath = public_path($relativePath);
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $fileName);
            $data['social_image'] = "{$relativePath}/{$fileName}";
        }

        \App\Models\Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(\App\Models\Category $category)
    {
        $categories = \App\Models\Category::where('id', '!=', $category->id)->get();
        return view('admin.categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, \App\Models\Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'social_image' => 'nullable|image',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image) {
                $oldPath = public_path("/{$category->image}");
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $relativePath = "storage/categories";
            $destinationPath = public_path($relativePath);
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $fileName);
            $data['image'] = "{$relativePath}/{$fileName}";
        }

        if ($request->hasFile('social_image')) {
            if ($category->social_image) {
                $oldPath = public_path("/{$category->social_image}");
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $file = $request->file('social_image');
            $fileName = time() . '_social_' . $file->getClientOriginalName();
            $relativePath = "storage/categories";
            $destinationPath = public_path($relativePath);
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $fileName);
            $data['social_image'] = "{$relativePath}/{$fileName}";
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(\App\Models\Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}
