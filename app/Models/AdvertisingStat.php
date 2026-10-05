<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertisingStat extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'advertisement_id',
        'stat_date',
        'impressions',
        'clicks',
        'ctr',
        'revenue',
        'currency',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'ctr' => 'decimal:2',
        'revenue' => 'decimal:2',
        'stat_date' => 'date',
    ];

    /**
     * Advertisement this stat belongs to.
     */
    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class, 'advertisement_id');
    }
}
