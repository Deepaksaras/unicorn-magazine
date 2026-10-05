<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImages;
use App\Http\Controllers\Admin\Concerns\SavesSeo;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    use HandlesImages, SavesSeo;

    public function index(Request $request)
    {
        $categories = Category::with('parent:id,name')
            ->withCount(['posts' => fn ($q) => $q->where('status', 1)])
            ->where('status', '!=', 4)
            ->when($request->q, fn ($q, $t) => $q->where('name', 'like', "%{$t}%"))
            ->orderByRaw('COALESCE(parent_id, id), parent_id IS NOT NULL, position, name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => new Category(['status' => 1, 'position' => 0]), 'parents' => $this->parents()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['image'] = $this->resolveImage($request, 'image', null, 'categories');
        $category = Category::create($data);
        $this->saveSeo($request, $category);

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function show(Category $category)
    {
        return redirect()->route('admin.categories.edit', $category);
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', ['category' => $category, 'parents' => $this->parents($category->id)]);
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request, $category);
        $data['image'] = $this->resolveImage($request, 'image', $category->image, 'categories');
        $category->update($data);
        $this->saveSeo($request, $category);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        if ($category->posts()->where('status', '!=', 4)->exists()) {
            return back()->with('error', 'Move or delete the articles in “' . $category->name . '” first.');
        }

        Category::where('parent_id', $category->id)->update(['parent_id' => null]);
        $category->update(['status' => 4, 'slug' => $category->slug . '-deleted-' . $category->id]);

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }

    protected function parents(?int $except = null)
    {
        return Category::where('status', '!=', 4)->whereNull('parent_id')
            ->when($except, fn ($q) => $q->where('id', '!=', $except))
            ->orderBy('name')->pluck('name', 'id');
    }

    protected function validated(Request $request, ?Category $category = null): array
    {
        $request->validate($this->seoRules());   // SEO box

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => ['nullable', 'string', 'max:191'],
            'description' => 'nullable|string|max:2000',
            'parent_id' => 'nullable|exists:categories,id',
            'position' => 'nullable|integer',
            'status' => 'required|in:0,1',
        ] + $this->imageRules('image'));

        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['position'] = (int) ($data['position'] ?? 0);

        $request->merge(['slug' => $data['slug']])->validate([
            'slug' => [Rule::unique('categories', 'slug')->ignore($category?->id)],
        ]);

        unset($data['image_file'], $data['image']);

        return $data;
    }
}
