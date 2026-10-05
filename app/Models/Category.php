<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'parent_id',
        'position',
        'meta_title',
        'meta_description',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'position' => 'integer',
    ];

    /**
     * Parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Posts in this category.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * SEO meta for this category.
     */
    public function seoMeta(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function getUrlAttribute(): string
    {
        return route('category', $this->slug);
    }
}
