<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImages;
use App\Http\Controllers\Admin\Concerns\SavesSeo;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Support\DateRange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    use HandlesImages, SavesSeo;

    public function index(Request $request)
    {
        // Date filter (All time / Today / This month / Date range) → by publish date
        $range = DateRange::fromRequest($request);
        $sort = in_array($request->sort, ['latest', 'newest', 'views', 'period_views'], true) ? $request->sort : 'latest';

        $query = Post::with(['category:id,name', 'author:id,name'])
            ->where('posts.status', '!=', 4)
            ->when($request->q, fn ($q, $term) => $q->where('title', 'like', "%{$term}%"))
            // Status filter: 1 = Published (already live), scheduled = published but with a future date, 0 = Draft
            ->when($request->status === 'scheduled', fn ($q) => $q->where('posts.status', 1)->where('posts.published_at', '>', now()))
            ->when($request->status === '1', fn ($q) => $q->where('posts.status', 1)
                ->where(fn ($w) => $w->whereNull('posts.published_at')->orWhere('posts.published_at', '<=', now())))
            ->when($request->status === '0', fn ($q) => $q->where('posts.status', 0))
            ->when($request->category, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->filter === 'featured', fn ($q) => $q->where('is_featured', true));
        $range->apply($query, 'COALESCE(posts.published_at, posts.created_at)');

        match ($sort) {
            'newest' => $query->orderByRaw('COALESCE(posts.published_at, posts.created_at) DESC'),
            'views' => $query->orderByDesc('view_count'),
            'period_views' => $query->orderByDesc('period_views')->orderByDesc('view_count'),
            default => $query->latest('updated_at'),
        };

        if ($sort === 'period_views') {
            $query->select('posts.*')->selectSub($this->periodViewsSub($range), 'period_views');
        }

        $posts = $query->paginate(15)->withQueryString();
        $categories = Category::where('status', '!=', 4)->orderBy('name')->pluck('name', 'id');

        // Views counted in the chosen period (from the visitor tracker)
        $periodViews = $range->active() ? $this->periodViews($range, $posts->pluck('slug')) : collect();

        // "Top viewed" strip at the top of the page
        $topViewed = $this->topViewed($range);

        return view('admin.articles.index', compact('posts', 'categories', 'range', 'sort', 'periodViews', 'topViewed'));
    }

    /**
     * Most read articles. All time = lifetime view counter;
     * a period = page views recorded by the visitor tracker in those dates.
     */
    protected function topViewed(DateRange $range, int $limit = 5)
    {
        if (!$range->active()) {
            return Post::where('status', 1)->where('view_count', '>', 0)->orderByDesc('view_count')->limit($limit)
                ->get(['id', 'title', 'slug', 'view_count'])
                ->map(fn ($p) => ['post' => $p, 'views' => (int) $p->view_count]);
        }

        try {
            $rows = $range->apply(DB::table('site_visits'), 'created_at')
                ->where('path', 'like', '/article/%')
                ->selectRaw('path, COUNT(*) as c')
                ->groupBy('path')
                ->orderByDesc('c')
                ->limit($limit * 2)
                ->pluck('c', 'path');
        } catch (\Throwable $e) {
            return collect(); // tracker tables not installed yet
        }

        $slugs = $rows->keys()->map(fn ($p) => Str::after($p, '/article/'));
        $posts = Post::whereIn('slug', $slugs)->where('status', '!=', 4)->get(['id', 'title', 'slug', 'view_count'])->keyBy('slug');

        return $rows->map(fn ($c, $path) => ['post' => $posts->get(Str::after($path, '/article/')), 'views' => (int) $c])
            ->filter(fn ($row) => $row['post'])
            ->take($limit)
            ->values();
    }

    /** Views in the period for the articles on this page: [slug => views]. */
    protected function periodViews(DateRange $range, $slugs)
    {
        if ($slugs->isEmpty()) {
            return collect();
        }

        try {
            return $range->apply(DB::table('site_visits'), 'created_at')
                ->whereIn('path', $slugs->map(fn ($s) => '/article/' . $s))
                ->selectRaw('path, COUNT(*) as c')
                ->groupBy('path')
                ->pluck('c', 'path')
                ->mapWithKeys(fn ($c, $path) => [Str::after($path, '/article/') => (int) $c]);
        } catch (\Throwable $e) {
            return collect();
        }
    }

    /** Sub-query used to sort by "most viewed in this period". */
    protected function periodViewsSub(DateRange $range)
    {
        $sub = DB::table('site_visits')
            ->selectRaw('COUNT(*)')
            ->whereRaw("site_visits.path = CONCAT('/article/', posts.slug)");

        return $range->apply($sub, 'site_visits.created_at');
    }

    public function create()
    {
        return view('admin.articles.form', $this->formData(new Post([
            'status' => 1,
            'allow_comments' => true,
            'published_at' => now(),
            'user_id' => auth()->id(),
        ])));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['featured_image'] = $this->resolveImage($request, 'featured_image', null, 'posts');

        $post = Post::create($data);
        $post->tags()->sync($this->tagIds($request));
        $this->saveSeo($request, $post);

        return $this->redirectAfterSave($request, $post, 'Article created.');
    }

    public function show(Post $article)
    {
        return redirect()->route('admin.articles.edit', $article);
    }

    public function edit(Post $article)
    {
        return view('admin.articles.form', $this->formData($article->load(['tags', 'seoMeta'])));
    }

    public function update(Request $request, Post $article)
    {
        $data = $this->validated($request, $article);
        $data['featured_image'] = $this->resolveImage($request, 'featured_image', $article->featured_image, 'posts');

        $article->update($data);
        $article->tags()->sync($this->tagIds($request));
        $this->saveSeo($request, $article);

        return $this->redirectAfterSave($request, $article, 'Article updated.');
    }

    public function destroy(Post $article)
    {
        $article->update(['status' => 4]);

        return redirect()->route('admin.articles.index')->with('success', 'Article moved to trash.');
    }

    /* ----------------------------------------------------------------- */

    protected function formData(Post $post): array
    {
        return [
            'post' => $post,
            'categories' => Category::where('status', '!=', 4)->orderBy('position')->orderBy('name')->get(['id', 'name', 'parent_id']),
            'tags' => Tag::where('status', '!=', 4)->orderBy('name')->get(['id', 'name']),
            'authors' => User::where('status', 1)->orderBy('name')->pluck('name', 'id'),
        ];
    }

    protected function validated(Request $request, ?Post $post = null): array
    {
        $request->validate($this->seoRules());   // SEO box

        $data = $request->validate([
            'title' => 'required|string|max:191',
            'slug' => ['nullable', 'string', 'max:191', Rule::unique('posts', 'slug')->ignore($post?->id)],
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'user_id' => 'required|exists:users,id',
            'badge' => 'nullable|string|max:50',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
            'status' => 'required|in:0,1',
            'published_at' => 'nullable|date',
            'is_featured' => 'boolean',
            'is_breaking' => 'boolean',
            'is_trending' => 'boolean',
            'allow_comments' => 'boolean',
        ] + $this->imageRules('featured_image'));

        $slug = Str::slug($data['slug'] ?? '') ?: Str::slug($data['title']);
        $data['slug'] = $this->uniqueSlug($slug, $post?->id);
        $data['reading_time'] = max(1, (int) ceil(str_word_count(strip_tags($data['content'])) / 200));
        $data['published_at'] = $data['published_at'] ?? ($data['status'] == 1 ? now() : null);

        foreach (['is_featured', 'is_breaking', 'is_trending', 'allow_comments'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        return collect($data)->only([
            'title', 'slug', 'excerpt', 'content', 'category_id', 'user_id', 'badge', 'status',
            'published_at', 'is_featured', 'is_breaking', 'is_trending', 'allow_comments', 'reading_time',
        ])->all();
    }

    protected function uniqueSlug(string $slug, ?int $ignoreId): string
    {
        $base = $slug ?: 'article';
        $i = 2;

        while (Post::withDeleted()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /**
     * Tags come as ids or new names (typed in the tag box).
     */
    protected function tagIds(Request $request): array
    {
        return collect($request->input('tags', []))
            ->filter()
            ->map(function ($value) {
                if (ctype_digit((string) $value) && Tag::whereKey($value)->exists()) {
                    return (int) $value;
                }

                $name = trim($value);
                $tag = Tag::withDeleted()->firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name, 'status' => 1]
                );
                if ($tag->status == 4) {
                    $tag->update(['status' => 1]);
                }

                return $tag->id;
            })
            ->unique()
            ->values()
            ->all();
    }

    protected function redirectAfterSave(Request $request, Post $post, string $message)
    {
        if ($request->input('after') === 'new') {
            return redirect()->route('admin.articles.create')->with('success', $message);
        }

        return redirect()->route('admin.articles.edit', $post)->with('success', $message);
    }
}
