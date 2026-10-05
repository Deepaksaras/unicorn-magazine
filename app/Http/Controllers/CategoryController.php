<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;
use App\Support\PostFeed;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public const PER_PAGE = 12;

    /**
     * /category/{slug}
     */
    public function show(Request $request, string $slug)
    {
        $category = Category::where('slug', $slug)->where('status', 1)->firstOrFail();

        $query = PostFeed::base()->whereIn('category_id', PostFeed::categoryIds($category));

        // SEO: the category's SEO box (Admin → Categories), else its name + description
        Seo::model($category, ['title' => $category->name, 'description' => $category->description]);

        return $this->listing($request, $query, [
            'title' => $category->name,
            'bigText' => Str::upper(Str::before($category->name, ' ')),
            'metaTitle' => $category->meta_title ?: $category->name,
            'metaDescription' => $category->meta_description ?: $category->description,
            'breadcrumb' => array_filter([
                $category->parent->name ?? null => $category->parent ? route('category', $category->parent->slug) : null,
                $category->name => null,
            ], fn ($v, $k) => $k !== '', ARRAY_FILTER_USE_BOTH),
            'intro' => $category->description,
        ]);
    }

    /**
     * /latest — every published post.
     */
    public function latest(Request $request)
    {
        Seo::page('latest');   // Admin → System → SEO

        return $this->listing($request, PostFeed::base(), [
            'title' => 'Latest News',
            'bigText' => 'LATEST',
            'breadcrumb' => ['Latest News' => null],
        ]);
    }

    /**
     * /tag/{slug}
     */
    public function tag(Request $request, string $slug)
    {
        $tag = Tag::where('slug', $slug)->where('status', 1)->firstOrFail();

        $query = PostFeed::base()->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id));

        Seo::model($tag, ['title' => '#' . $tag->name, 'description' => $tag->description ?: 'Stories tagged ' . $tag->name . '.']);

        return $this->listing($request, $query, [
            'title' => '#' . $tag->name,
            'bigText' => Str::upper(Str::before($tag->name, ' ')),
            'breadcrumb' => ['Tags' => null, $tag->name => null],
            'intro' => $tag->description,
        ]);
    }

    /**
     * /archive — all posts, optionally filtered by ?month=2026-08
     */
    public function archive(Request $request)
    {
        $query = PostFeed::base();
        $title = 'Archive';

        if (preg_match('/^(\d{4})-(\d{2})$/', (string) $request->query('month'), $m)) {
            $query->whereYear('published_at', $m[1])->whereMonth('published_at', $m[2]);
            $title = 'Archive: ' . \Carbon\Carbon::create($m[1], $m[2])->format('F Y');
        }

        Seo::page('archive', ['title' => $title]);

        return $this->listing($request, $query, [
            'title' => $title,
            'bigText' => 'ARCHIVE',
            'breadcrumb' => ['Archive' => null],
        ]);
    }

    /**
     * /search?q=
     */
    public function search(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        $query = PostFeed::base();

        if ($term === '') {
            $query->whereRaw('1 = 0');
        } else {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';
            $query->where(fn ($q) => $q->where('title', 'like', $like)
                ->orWhere('excerpt', 'like', $like)
                ->orWhere('content', 'like', $like));
        }

        // Search result pages are not useful in Google → noindex
        Seo::page('search', ['title' => $term === '' ? 'Search' : 'Search: ' . $term, 'robots' => 'noindex, follow']);

        return $this->listing($request, $query, [
            'title' => $term === '' ? 'Search' : 'Results for “' . $term . '”',
            'bigText' => 'SEARCH',
            'breadcrumb' => ['Search' => null],
            'emptyText' => $term === '' ? 'Type something in the search box to find stories.' : 'No stories matched “' . $term . '”. Try another word.',
            'noHero' => true,
        ]);
    }

    /**
     * Shared renderer for every list page (matches category.html).
     */
    protected function listing(Request $request, Builder $query, array $meta)
    {
        $hero = null;

        if (empty($meta['noHero'])) {
            $hero = (clone $query)->first();
        }

        $posts = (clone $query)
            ->when($hero, fn ($q) => $q->where('posts.id', '!=', $hero->id))
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('partials.post-grid-items', ['posts' => $posts])->render(),
                'next' => $posts->nextPageUrl(),
            ]);
        }

        $featured = PostFeed::featured(6, array_filter([$hero->id ?? null]));

        return view('listing', array_merge([
            'hero' => $hero,
            'posts' => $posts,
            'featured' => $featured,
            'intro' => null,
            'emptyText' => 'No stories have been published here yet.',
        ], $meta));
    }
}
