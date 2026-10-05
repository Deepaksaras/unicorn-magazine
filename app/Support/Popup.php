<?php

namespace App\Support;

use App\Models\Advertisement;
use App\Models\AdvertisementPlacement;

/**
 * Pop-up advertisement (Admin → Advertising → Pop-up Ad).
 * Settings live in the settings table (group "popup"); the ads themselves are
 * normal Advertisements in the "popup-ad" placement.
 */
class Popup
{
    public const PLACEMENT = 'popup-ad';

    /** Setting key => [label, default] */
    public const FIELDS = [
        'popup_enabled' => ['Show the pop-up', '0'],
        'popup_delay' => ['Show after (seconds)', '5'],
        'popup_scroll' => ['Or wait until the visitor scrolls (%)', '0'],
        'popup_close_after' => ['Show the close button after (seconds)', '3'],
        'popup_auto_close' => ['Close automatically after (seconds)', '0'],
        'popup_frequency' => ['How often per visitor', 'day'],
        'popup_pages' => ['Show on', 'all'],
        'popup_devices' => ['Devices', 'all'],
        'popup_backdrop_close' => ['Close by clicking outside / Esc', '1'],
        'popup_size' => ['Size', 'standard'],
    ];

    public const FREQUENCIES = ['always' => 'Every page', 'session' => 'Once per visit', 'day' => 'Once per day', 'week' => 'Once per week'];
    public const PAGES = ['all' => 'All pages', 'home' => 'Home page only', 'articles' => 'Article pages only'];
    public const DEVICES = ['all' => 'Desktop + mobile', 'desktop' => 'Desktop only', 'mobile' => 'Mobile only'];
    public const SIZES = ['standard' => 'Standard (600px)', 'large' => 'Large (800px)'];

    public static function settings(): array
    {
        $out = [];
        foreach (self::FIELDS as $key => [, $default]) {
            $out[$key] = (string) SiteSettings::get($key, $default);
        }

        return $out;
    }

    /** The "Pop-up Ad" placement (created the first time it's needed). */
    public static function placement(): ?AdvertisementPlacement
    {
        try {
            return AdvertisementPlacement::withDeleted()->firstOrCreate(
                ['slug' => self::PLACEMENT],
                ['name' => 'Pop-up Ad', 'description' => 'Pop-up window (settings: Advertising → Pop-up Ad)', 'dimensions' => '600x500', 'location' => 'popup', 'max_ads' => 1, 'is_active' => true, 'status' => 1]
            );
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * The ad to show on this page, or null. The view is counted by the browser
     * only when the pop-up really opens (POST /ad/{id}/view).
     */
    public static function forCurrentPage(): ?Advertisement
    {
        $s = self::settings();
        $preview = request()->boolean('popup_preview') && auth()->check();

        if ($s['popup_enabled'] !== '1' && !$preview) {
            return null;
        }

        if (!$preview) {
            if ($s['popup_pages'] === 'home' && !request()->routeIs('home')) {
                return null;
            }
            if ($s['popup_pages'] === 'articles' && !request()->routeIs('article')) {
                return null;
            }
        }

        return Ads::pick(self::PLACEMENT);
    }
}
