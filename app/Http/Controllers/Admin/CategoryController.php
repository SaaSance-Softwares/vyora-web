<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->orderBy('sort_order', 'asc')
            ->with([
                'children' => function ($query) {
                    $query->orderBy('sort_order', 'asc');
                },
            ])
            ->get();

        $stats = [
            'total' => Category::count(),
            'active' => Category::where('is_active', true)->count(),
            'root' => $categories->count(),
        ];

        return view('admin.categories.index', compact('categories', 'stats'));
    }

    public function reorder(Request $request)
    {
        $request->strictValidate([
            'categories' => 'required|array',
        ]);

        $this->updateCategoryOrder($request->categories, null);

        return response()->json(['success' => true]);
    }

    private function updateCategoryOrder(array $categories, ?int $parentId)
    {
        foreach ($categories as $index => $categoryData) {
            $category = Category::find($categoryData['id']);
            if ($category) {
                $category->update([
                    'sort_order' => $index,
                    'parent_id' => $parentId,
                ]);

                if (isset($categoryData['children']) && ! empty($categoryData['children'])) {
                    $this->updateCategoryOrder($categoryData['children'], $category->id);
                }
            }
        }
    }

    public function create()
    {
        // Flatten list for parent selection (simple implementation for now)
        $categories = Category::all();

        return view('admin.categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->strictValidate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'required|string|max:255|unique:categories',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image',
            'banner_image' => 'nullable|image',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:5000',
            'meta_keywords' => 'nullable|string|max:1000',
            'aeo_use_case' => 'nullable|string|max:255',
            'social_image' => 'nullable|image',
            'is_active' => 'boolean',
            'faqs' => 'nullable|array',
            'faqs.*.question' => 'required_with:faqs|string|max:1000',
            'faqs.*.answer' => 'required_with:faqs|string',
        ]);

        $data = $request->except('faqs');

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImageAsWebp($request->file('image'), 'img', 'storage/categories');
        }

        if ($request->hasFile('social_image')) {
            $data['social_image'] = $this->uploadImageAsWebp($request->file('social_image'), 'social', 'storage/categories');
        }


        if ($request->hasFile('banner_image')) {
            $data['banner_image'] = $this->uploadImageAsWebp($request->file('banner_image'), 'banner', 'storage/categories');
        }
        $category = Category::create($data);

        if ($request->has('faqs') && is_array($request->faqs)) {
            foreach ($request->faqs as $index => $faqData) {
                if (!empty($faqData['question']) && !empty($faqData['answer'])) {
                    $category->faqs()->create([
                        'question' => $faqData['question'],
                        'answer' => $faqData['answer'],
                        'sort_order' => $index
                    ]);
                }
            }
        }
        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $categories = Category::where('id', '!=', $category->id)->get();

        return view('admin.categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        $request->strictValidate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'required|string|max:255|unique:categories,slug,'.$category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image',
            'banner_image' => 'nullable|image',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:5000',
            'meta_keywords' => 'nullable|string|max:1000',
            'aeo_use_case' => 'nullable|string|max:255',
            'social_image' => 'nullable|image',
            'is_active' => 'boolean',
            'faqs' => 'nullable|array',
            'faqs.*.question' => 'required_with:faqs|string|max:1000',
            'faqs.*.answer' => 'required_with:faqs|string',
        ]);

        $data = $request->except('faqs');
        if (!$request->hasFile('image')) {
            if ($request->remove_image == 1) {
                if ($category->image) {
                    $oldPath = public_path("/{$category->image}");
                    if (file_exists($oldPath)) @unlink($oldPath);
                }
                $data['image'] = null;
            } else {
                unset($data['image']);
            }
        }
        if (!$request->hasFile('social_image')) {
            if ($request->remove_social_image == 1) {
                if ($category->social_image) {
                    $oldPath = public_path("/{$category->social_image}");
                    if (file_exists($oldPath)) @unlink($oldPath);
                }
                $data['social_image'] = null;
            } else {
                unset($data['social_image']);
            }
        }
        if (!$request->hasFile('banner_image')) {
            if ($request->remove_banner_image == 1) {
                if ($category->banner_image) {
                    $oldPath = public_path("/{$category->banner_image}");
                    if (file_exists($oldPath)) @unlink($oldPath);
                }
                $data['banner_image'] = null;
            } else {
                unset($data['banner_image']);
            }
        }


        if ($request->hasFile('image')) {
            if ($category->image) {
                $oldPath = public_path("/{$category->image}");
                if (file_exists($oldPath)) @unlink($oldPath);
            }
            $data['image'] = $this->uploadImageAsWebp($request->file('image'), 'img', 'storage/categories');
        }

        if ($request->hasFile('social_image')) {
            if ($category->social_image) {
                $oldPath = public_path("/{$category->social_image}");
                if (file_exists($oldPath)) @unlink($oldPath);
            }
            $data['social_image'] = $this->uploadImageAsWebp($request->file('social_image'), 'social', 'storage/categories');
        }


        if ($request->hasFile('banner_image')) {
            if ($category->banner_image) {
                $oldPath = public_path("/{$category->banner_image}");
                if (file_exists($oldPath)) @unlink($oldPath);
            }
            $data['banner_image'] = $this->uploadImageAsWebp($request->file('banner_image'), 'banner', 'storage/categories');
        }
        $category->update($data);

        if ($request->has('faqs')) {
            $category->faqs()->delete();
            if (is_array($request->faqs)) {
                foreach ($request->faqs as $index => $faqData) {
                    if (!empty($faqData['question']) && !empty($faqData['answer'])) {
                        $category->faqs()->create([
                            'question' => $faqData['question'],
                            'answer' => $faqData['answer'],
                            'sort_order' => $index
                        ]);
                    }
                }
            }
        }
        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
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