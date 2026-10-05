<?php

namespace App\Support;

use App\Models\Menu;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Menus
{
    protected static array $cache = [];

    /**
     * Active top-level items (with active children) of a menu by slug.
     */
    public static function items(string $slug): Collection
    {
        if (isset(static::$cache[$slug])) {
            return static::$cache[$slug];
        }

        try {
            $menu = Menu::where('slug', $slug)->where('status', 1)->first();
        } catch (\Throwable $e) {
            $menu = null;
        }

        return static::$cache[$slug] = $menu
            ? $menu->items()
                ->where('status', 1)
                ->whereNull('parent_id')
                ->with(['children' => fn ($q) => $q->where('status', 1)->orderBy('position')])
                ->orderBy('position')
                ->get()
            : collect();
    }

    public static function name(string $slug, string $default): string
    {
        try {
            return Menu::where('slug', $slug)->value('name') ?: $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function url(?string $url): string
    {
        $url = trim((string) $url);

        if ($url === '' || $url === '#') {
            return '#';
        }

        if (Str::startsWith($url, ['http://', 'https://', 'mailto:', 'tel:', '#'])) {
            return $url;
        }

        return url($url);
    }

    /**
     * Is the menu link the current page (or a parent section of it)?
     */
    public static function isActive(?string $url): bool
    {
        $url = trim((string) $url);

        if ($url === '' || Str::startsWith($url, ['#', 'mailto:', 'tel:'])) {
            return false;
        }

        $path = trim(parse_url(static::url($url), PHP_URL_PATH) ?? '', '/');
        $base = trim(parse_url(url('/'), PHP_URL_PATH) ?? '', '/');
        $current = trim(request()->path(), '/');

        if ($base !== '' && Str::startsWith($path, $base)) {
            $path = trim(Str::after($path, $base), '/');
        }

        if ($path === '') {
            return $current === '' || $current === '/';
        }

        return $current === $path;
    }
}
