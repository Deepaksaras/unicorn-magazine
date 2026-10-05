<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'email',
        'name',
        'phone',
        'source',
        'ip_address',
        'subscribed_at',
        'unsubscribed_at',
        'unsubscribe_reason',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];
}
