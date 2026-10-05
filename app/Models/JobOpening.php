<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOpening extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'title',
        'slug',
        'department',
        'employment_type',
        'location',
        'summary',
        'responsibilities',
        'requirements',
        'apply_url',
        'position',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'position' => 'integer',
        'responsibilities' => 'array',
        'requirements' => 'array',
    ];

    /**
     * "Editorial · Full Time · Mumbai / Hybrid"
     */
    public function getMetaLineAttribute(): string
    {
        return collect([$this->department, $this->employment_type, $this->location])
            ->filter()
            ->implode(' · ');
    }
}
