<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\PostFeed;
use App\Support\Seo;

class ArticleController extends Controller
{
    /**
     * /article/{slug}
     */
    public function show(string $slug)
    {
        $post = Post::published()
            ->with(['author', 'category.parent', 'tags' => fn ($q) => $q->where('tags.status', 1), 'seoMeta'])
            ->where('slug', $slug)
            ->firstOrFail();

        Post::whereKey($post->id)->increment('view_count');

        // SEO: the article's SEO box (Admin → Articles), else title / excerpt / featured image / tags
        Seo::model($post, [
            'title' => $post->title,
            'description' => $post->excerpt ?: $post->content,
            'keywords' => $post->tags->pluck('name')->implode(', '),
            'image' => $post->image_url,
            'type' => 'article',
            'published' => optional($post->display_date)->toIso8601String(),
            'modified' => optional($post->updated_at)->toIso8601String(),
            'author' => $post->author_name,
        ]);

        $related = collect();
        if ($post->category) {
            $related = PostFeed::base()
                ->whereIn('category_id', PostFeed::categoryIds($post->category->parent ?? $post->category))
                ->where('posts.id', '!=', $post->id)
                ->limit(8)
                ->get();
        }
        if ($related->count() < 8) {
            $related = $related->concat(
                PostFeed::base()
                    ->whereNotIn('posts.id', $related->pluck('id')->push($post->id))
                    ->limit(8 - $related->count())
                    ->get()
            );
        }

        $featured = PostFeed::featured(6, [$post->id]);

        return view('article', compact('post', 'related', 'featured'));
    }
}
