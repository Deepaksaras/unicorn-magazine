<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'name',
        'slug',
        'location',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    /**
     * Items in this menu.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }
}
