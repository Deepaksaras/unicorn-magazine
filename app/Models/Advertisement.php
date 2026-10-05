<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Advertisement extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'placement_id',
        'title',
        'slug',
        'description',
        'image',
        'url',
        'code',
        'button_text',
        'alt_text',
        'target',
        'impression_count',
        'click_count',
        'start_date',
        'end_date',
        'is_active',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'impression_count' => 'integer',
        'click_count' => 'integer',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Placement this advertisement belongs to.
     */
    public function placement(): BelongsTo
    {
        return $this->belongsTo(AdvertisementPlacement::class, 'placement_id');
    }

    /**
     * Statistics for this advertisement.
     */
    public function stats(): HasMany
    {
        return $this->hasMany(AdvertisingStat::class, 'advertisement_id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? \App\Support\Media::url($this->image) : null;
    }
}
