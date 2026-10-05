<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSection extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'page_id',
        'section_name',
        'content',
        'layout',
        'settings',
        'position',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'position' => 'integer',
        'settings' => 'array',
    ];

    /**
     * Page this section belongs to.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
