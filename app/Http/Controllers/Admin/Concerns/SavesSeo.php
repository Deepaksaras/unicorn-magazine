<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\ImageUploader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Saves the <x-admin.seo> box of an edit form into the seo_meta table.
 *
 *   $request->validate([...] + $this->seoRules());
 *   $this->saveSeo($request, $post);
 *
 * Empty boxes are stored as NULL, so the website falls back to the item's own title / text / image.
 */
trait SavesSeo
{
    protected function seoRules(): array
    {
        return [
            'seo_title' => 'nullable|string|max:191',
            'seo_description' => 'nullable|string|max:191',
            'seo_keywords' => 'nullable|string|max:191',
            'seo_canonical' => 'nullable|url|max:191',
            'seo_og_title' => 'nullable|string|max:191',
            'seo_og_description' => 'nullable|string|max:191',
            'seo_og_image' => 'nullable|string|max:191',
            'seo_og_image_file' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ];
    }

    protected function saveSeo(Request $request, Model $model): void
    {
        if (!$request->has('seo_title') && !$request->has('seo_description') && !$request->has('seo_keywords')) {
            return; // form without an SEO box
        }

        $current = $model->seoMeta()->first();
        $text = fn (string $key) => ($value = trim((string) $request->input($key))) === '' ? null : $value;

        // Share image: new upload wins, "remove" clears, otherwise keep / typed URL
        $image = $current->og_image ?? null;
        if ($file = $request->file('seo_og_image_file')) {
            ImageUploader::delete($image);
            $image = ImageUploader::store($file, 'seo', 1200);
        } elseif ($request->boolean('seo_og_image_remove')) {
            ImageUploader::delete($image);
            $image = null;
        } elseif ($request->has('seo_og_image')) {
            $image = $text('seo_og_image');
        }

        $model->seoMeta()->updateOrCreate(
            ['seoable_id' => $model->getKey(), 'seoable_type' => $model->getMorphClass()],
            [
                'meta_title' => $text('seo_title'),
                'meta_description' => $text('seo_description'),
                'meta_keywords' => $text('seo_keywords'),
                'canonical_url' => $text('seo_canonical'),
                'og_title' => $text('seo_og_title'),
                'og_description' => $text('seo_og_description'),
                'og_image' => $image,
                'robots' => $request->boolean('seo_noindex') ? 'noindex, follow' : null,
                'status' => 1,
            ]
        );

        // Pages and categories also have their own meta columns – keep them the same
        foreach (['meta_title' => 'seo_title', 'meta_description' => 'seo_description'] as $column => $input) {
            if (array_key_exists($column, $model->getAttributes())) {
                $model->{$column} = $text($input);
            }
        }
        if ($model->isDirty(['meta_title', 'meta_description'])) {
            $model->saveQuietly();
        }
    }
}
