<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Page extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'template',
        'featured_image',
        'meta_title',
        'meta_description',
        'published_at',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'published_at' => 'datetime',
    ];

    /**
     * User who created the page.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Sections of the page.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class);
    }

    /**
     * SEO meta for the page.
     */
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }
}
