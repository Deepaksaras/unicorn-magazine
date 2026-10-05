<?php

namespace App\Models;

use App\Models\Traits\HasShortLink;
use App\Models\Traits\HasSeo;
use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory, HasStatus, HasSeo, HasShortLink;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'content',
        'cover_image',
        'external_url',
        'category_label',
        'author_name',
        'is_exclusive',
        'file_path',
        'file_type',
        'file_size',
        'report_type',
        'report_date',
        'download_count',
        'view_count',
        'is_premium',
        'price',
        'currency',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'file_size' => 'integer',
        'download_count' => 'integer',
        'view_count' => 'integer',
        'is_premium' => 'boolean',
        'is_exclusive' => 'boolean',
        'price' => 'decimal:2',
        'report_date' => 'date',
    ];

    /**
     * User who uploaded the report.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCoverUrlAttribute(): string
    {
        return \App\Support\Media::url($this->cover_image, \App\Support\Media::placeholder());
    }

    /** Public report page: /report/{slug} */
    public function getLinkAttribute(): string
    {
        return $this->slug ? route('report', $this->slug) : route('reports.index');
    }

    /** Download / open link, or null when no file or external link is set. */
    public function getDownloadUrlAttribute(): ?string
    {
        return ($this->file_path || $this->external_url) && $this->slug ? route('reports.download', $this->slug) : null;
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->category_label ?: \Illuminate\Support\Str::headline((string) $this->report_type);
    }

    public function getDisplayDateAttribute()
    {
        return $this->report_date ?? $this->created_at;
    }

    /** "PDF · 2.4 MB" */
    public function getFileInfoAttribute(): ?string
    {
        if (!$this->file_path) {
            return $this->external_url ? 'External link' : null;
        }

        $type = strtoupper($this->file_type ?: pathinfo($this->file_path, PATHINFO_EXTENSION));
        $size = (int) $this->file_size;
        $sizeText = $size >= 1048576 ? number_format($size / 1048576, 1) . ' MB' : ($size ? max(1, round($size / 1024)) . ' KB' : null);

        return collect([$type, $sizeText])->filter()->implode(' · ');
    }
}
