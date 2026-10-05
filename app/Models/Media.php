<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'user_id',
        'file_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'disk',
        'alt_text',
        'caption',
        'width',
        'height',
        'thumbnail_path',
        'webp_path',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    /**
     * User who uploaded the media.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full URL of the media file.
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Get the full URL of the thumbnail.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? asset('storage/' . $this->thumbnail_path) : null;
    }

    /**
     * Get the full URL of the WebP version.
     */
    public function getWebpUrlAttribute(): ?string
    {
        return $this->webp_path ? asset('storage/' . $this->webp_path) : null;
    }
}
