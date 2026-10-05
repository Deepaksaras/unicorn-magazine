<?php

namespace App\Models\Traits;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * SEO box (title, description, keywords, canonical, share title/text/image) for a record.
 * Stored in the seo_meta table; edited with <x-admin.seo> on the admin forms.
 */
trait HasSeo
{
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }
}
