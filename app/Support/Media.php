<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Turns whatever is stored in an image column into a usable URL.
 *
 *  - full URLs (https://...) are returned untouched
 *  - files that live in /public (e.g. "img/no1.webp") use asset()
 *  - everything else is treated as a file on the "public" storage disk
 */
class Media
{
    public static function url(?string $path, ?string $fallback = null): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return $fallback;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//', 'data:'])) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (Str::startsWith($path, 'storage/')) {
            return asset($path);
        }

        if (file_exists(public_path($path))) {
            return asset($path);
        }

        return asset('storage/' . $path);
    }

    /**
     * Thumbnail version of an uploaded image (created by ImageUploader), if present.
     */
    public static function thumb(?string $path, ?string $fallback = null): ?string
    {
        $path = trim((string) $path);

        if ($path !== '' && !Str::contains($path, '://')) {
            $thumb = dirname($path) . '/thumbs/' . basename($path);

            if (Storage::disk('public')->exists($thumb)) {
                return asset('storage/' . $thumb);
            }
        }

        return static::url($path, $fallback);
    }

    public static function placeholder(): string
    {
        return asset('img/placeholder.svg');
    }

    public static function avatar(?string $name): string
    {
        return 'https://ui-avatars.com/api/?size=400&background=151515&color=c6a65b&name=' . urlencode($name ?: 'U M');
    }

    /**
     * True when the stored value is a file we uploaded (so it can be deleted).
     */
    public static function isLocalUpload(?string $path): bool
    {
        $path = trim((string) $path);

        return $path !== ''
            && !Str::startsWith($path, ['http://', 'https://', '//', 'data:', 'img/', 'css/', 'js/'])
            && Storage::disk('public')->exists($path);
    }
}
