<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * All settings as a key => value array, cached so views can call
 * Setting::getValue() freely without a query per call.
 */
class SiteSettings
{
    protected static ?array $memo = null;

    public static function all(): array
    {
        if (static::$memo !== null) {
            return static::$memo;
        }

        try {
            static::$memo = Cache::remember('site_settings', 3600, function () {
                if (!Schema::hasTable('settings')) {
                    return [];
                }

                return Setting::query()
                    ->where('status', 1)
                    ->pluck('value', 'key')
                    ->all();
            });
        } catch (\Throwable $e) {
            static::$memo = [];
        }

        return static::$memo;
    }

    public static function get(string $key, $default = null)
    {
        $value = static::all()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public static function flush(): void
    {
        static::$memo = null;
        Cache::forget('site_settings');
    }
}
