<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMeta extends Model
{
    use HasFactory, HasStatus;

    protected $table = 'seo_meta';

    protected $fillable = [
        'seoable_id',
        'seoable_type',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'schema_markup',
        'robots',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'schema_markup' => 'array',
    ];

    /**
     * Get the parent SEO model.
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
