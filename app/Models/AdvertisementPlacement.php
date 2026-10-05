<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdvertisementPlacement extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'dimensions',
        'location',
        'max_ads',
        'is_active',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'max_ads' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Advertisements in this placement.
     */
    public function advertisements(): HasMany
    {
        return $this->hasMany(Advertisement::class, 'placement_id');
    }
}
