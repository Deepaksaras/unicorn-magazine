<?php

namespace App\Models;

use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'label',
        'description',
        'is_public',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'is_public' => 'boolean',
    ];

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, $default = null)
    {
        $value = \App\Support\SiteSettings::all()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    /**
     * Set a setting value.
     */
    public static function setValue(string $key, $value, string $group = 'general'): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'status' => Status::ACTIVE]
        );

        \App\Support\SiteSettings::flush();
    }
}
