<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakingNews extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'title',
        'url',
        'position',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'position' => 'integer',
    ];
}
