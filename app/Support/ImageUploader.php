<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Stores uploaded images on the "public" disk.
 * Raster images are resized + converted to WebP with a thumbnail;
 * SVG/GIF (or anything Intervention cannot read) are stored as-is.
 */
class ImageUploader
{
    public static function store(UploadedFile $file, string $directory, int $maxWidth = 1600): string
    {
        $directory = trim($directory, '/');
        $disk = Storage::disk('public');
        $disk->makeDirectory($directory . '/thumbs');

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());

        if (in_array($extension, ['svg', 'gif', 'ico']) || !extension_loaded('gd')) {
            return $file->store($directory, 'public');
        }

        try {
            $name = now()->format('YmdHis') . '_' . Str::random(8) . '.webp';
            $manager = new ImageManager(new Driver());

            $image = $manager->read($file->getRealPath());
            $image->scaleDown(width: $maxWidth);
            $disk->put($directory . '/' . $name, (string) $image->toWebp(82));

            $thumb = $manager->read($file->getRealPath());
            $thumb->scaleDown(width: 480);
            $disk->put($directory . '/thumbs/' . $name, (string) $thumb->toWebp(75));

            return $directory . '/' . $name;
        } catch (\Throwable $e) {
            Log::warning('Image processing failed, storing original: ' . $e->getMessage());

            return $file->store($directory, 'public');
        }
    }

    /**
     * Store any file (PDF, etc.) without processing.
     */
    public static function storeFile(UploadedFile $file, string $directory): string
    {
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . Str::random(6) . '.' . $file->getClientOriginalExtension();

        return $file->storeAs(trim($directory, '/'), $name, 'public');
    }

    public static function delete(?string $path): void
    {
        if (!Media::isLocalUpload($path)) {
            return;
        }

        $disk = Storage::disk('public');
        $disk->delete($path);
        $disk->delete(dirname($path) . '/thumbs/' . basename($path));
    }
}
