<?php

namespace App\Models;

use App\Models\Traits\HasShortLink;
use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Post extends Model
{
    use HasFactory, HasStatus, HasShortLink;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'type',
        'format',
        'badge',
        'reading_time',
        'view_count',
        'like_count',
        'comment_count',
        'is_featured',
        'is_breaking',
        'is_trending',
        'allow_comments',
        'published_at',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'reading_time' => 'integer',
        'view_count' => 'integer',
        'like_count' => 'integer',
        'comment_count' => 'integer',
        'is_featured' => 'boolean',
        'is_breaking' => 'boolean',
        'is_trending' => 'boolean',
        'allow_comments' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Author of the post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Category of the post.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Tags for the post.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag')->withPivot('status')->withTimestamps();
    }

    /**
     * Media for the post.
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * SEO meta for the post.
     */
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    /**
     * Only published posts whose publish date has passed.
     */
    public function scopePublished($query)
    {
        return $query->where('posts.status', Status::ACTIVE)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function getImageUrlAttribute(): string
    {
        return \App\Support\Media::url($this->featured_image, \App\Support\Media::placeholder());
    }

    public function getThumbUrlAttribute(): string
    {
        return \App\Support\Media::thumb($this->featured_image, \App\Support\Media::placeholder());
    }

    public function getUrlAttribute(): string
    {
        return route('article', $this->slug);
    }

    public function getDisplayDateAttribute()
    {
        return $this->published_at ?? $this->created_at;
    }

    public function getAuthorNameAttribute(): string
    {
        return $this->author->name ?? 'Editorial Team';
    }
}
