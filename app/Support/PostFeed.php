<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Small query helpers shared by the public controllers.
 */
class PostFeed
{
    public static function base(): Builder
    {
        return Post::query()
            ->published()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    /**
     * IDs of a category and its direct children (so "Business" also shows "Startups").
     */
    public static function categoryIds(?Category $category): array
    {
        if (!$category) {
            return [];
        }

        return Category::where('status', 1)
            ->where(fn ($q) => $q->where('id', $category->id)->orWhere('parent_id', $category->id))
            ->pluck('id')
            ->all();
    }

    public static function fromCategory(?string $slug, int $limit, array $exclude = []): Collection
    {
        $category = $slug ? Category::where('slug', $slug)->where('status', 1)->first() : null;

        if (!$category) {
            return collect();
        }

        return static::base()
            ->whereIn('category_id', static::categoryIds($category))
            ->when($exclude, fn ($q) => $q->whereNotIn('posts.id', $exclude))
            ->limit(max(1, $limit))
            ->get();
    }

    public static function featured(int $limit, array $exclude = []): Collection
    {
        return static::base()
            ->where('is_featured', true)
            ->when($exclude, fn ($q) => $q->whereNotIn('posts.id', $exclude))
            ->limit($limit)
            ->get();
    }
}
