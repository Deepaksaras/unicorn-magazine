<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SavesSeo;
use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    use SavesSeo;

    public function index(Request $request)
    {
        $tags = Tag::withCount('posts')
            ->where('status', '!=', 4)
            ->when($request->q, fn ($q, $t) => $q->where('name', 'like', "%{$t}%"))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.tags.index', compact('tags'));
    }

    public function create()
    {
        return view('admin.tags.form', ['tag' => new Tag(['status' => 1])]);
    }

    public function store(Request $request)
    {
        $tag = Tag::create($this->validated($request));
        $this->saveSeo($request, $tag);

        return redirect()->route('admin.tags.index')->with('success', 'Tag created.');
    }

    public function show(Tag $tag)
    {
        return redirect()->route('admin.tags.edit', $tag);
    }

    public function edit(Tag $tag)
    {
        return view('admin.tags.form', compact('tag'));
    }

    public function update(Request $request, Tag $tag)
    {
        $tag->update($this->validated($request, $tag));
        $this->saveSeo($request, $tag);

        return redirect()->route('admin.tags.index')->with('success', 'Tag updated.');
    }

    public function destroy(Tag $tag)
    {
        $tag->posts()->detach();
        $tag->update(['status' => 4, 'slug' => $tag->slug . '-deleted-' . $tag->id]);

        return redirect()->route('admin.tags.index')->with('success', 'Tag deleted.');
    }

    protected function validated(Request $request, ?Tag $tag = null): array
    {
        $request->validate($this->seoRules());   // SEO box

        $request->merge(['slug' => Str::slug($request->input('slug') ?: $request->input('name'))]);

        return $request->validate([
            'name' => 'required|string|max:191',
            'slug' => ['required', 'string', 'max:191', Rule::unique('tags', 'slug')->ignore($tag?->id)],
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:0,1',
        ]);
    }
}
