<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'section_key',
        'title',
        'big_text',
        'description',
        'image',
        'link_text',
        'link_url',
        'position',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'position' => 'integer',
    ];
}
