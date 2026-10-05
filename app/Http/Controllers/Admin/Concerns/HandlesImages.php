<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Support\ImageUploader;
use Illuminate\Http\Request;

trait HandlesImages
{
    /**
     * Works with <x-admin.image name="photo">:
     *  photo_file   – new upload (wins)
     *  photo_remove – clear the image
     *  photo        – typed URL / path (or unchanged hidden value)
     */
    protected function resolveImage(Request $request, string $field, ?string $current, string $directory, int $maxWidth = 1600): ?string
    {
        if ($file = $request->file($field . '_file')) {
            ImageUploader::delete($current);

            return ImageUploader::store($file, $directory, $maxWidth);
        }

        if ($request->boolean($field . '_remove')) {
            ImageUploader::delete($current);

            return null;
        }

        if ($request->has($field)) {
            $typed = trim((string) $request->input($field));

            return $typed === '' ? null : $typed;
        }

        return $current;
    }

    protected function imageRules(string ...$fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $rules[$field . '_file'] = 'nullable|image|mimes:jpeg,jpg,png,gif,webp,svg|max:5120';
            $rules[$field] = 'nullable|string|max:255';
        }

        return $rules;
    }
}
