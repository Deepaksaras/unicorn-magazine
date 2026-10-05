<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisingEnquiry extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'website',
        'industry',
        'message',
        'budget',
        'duration',
        'placement_interest',
        'enquiry_status',
        'is_read',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'is_read' => 'integer',
    ];
}
