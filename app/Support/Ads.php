<?php

namespace App\Support;

use App\Models\Advertisement;
use Illuminate\Support\Facades\DB;

class Ads
{
    /**
     * One live ad for a placement slug (random when several are live).
     * Each time an ad is shown it counts as 1 view (total + today's row in advertising_stats).
     */
    public static function for(string $placementSlug): ?Advertisement
    {
        $ad = static::pick($placementSlug);

        if ($ad && Visitor::counts()) {
            try {
                Advertisement::whereKey($ad->id)->increment('impression_count');
                static::addToday($ad->id, 'impressions');
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $ad;
    }

    /** One live ad for a placement, without counting a view. */
    public static function pick(string $placementSlug): ?Advertisement
    {
        try {
            return Advertisement::query()
                ->where('status', 1)
                ->where('is_active', true)
                ->where(fn ($q) => $q->whereNull('start_date')->orWhere('start_date', '<=', now()->toDateString()))
                ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString()))
                ->whereHas('placement', fn ($q) => $q->where('slug', $placementSlug)->where('status', 1)->where('is_active', true))
                ->inRandomOrder()
                ->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Count one view (used by the pop-up, which reports when it really opened). */
    public static function countView(Advertisement $ad): void
    {
        if (!Visitor::counts()) {
            return;
        }

        try {
            Advertisement::whereKey($ad->id)->increment('impression_count');
            static::addToday($ad->id, 'impressions');
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Link that counts the click, then opens the advertiser's page. */
    public static function link(Advertisement $ad): string
    {
        if (!$ad->url) {
            return '#';
        }

        return route('ad.click', ['id' => $ad->id, 'p' => '/' . ltrim(request()->path(), '/')]);
    }

    /** +1 view or click on today's row for this ad (advertising_stats). */
    public static function addToday(int $adId, string $column): void
    {
        if (!in_array($column, ['impressions', 'clicks'], true)) {
            return;
        }

        try {
            $now = now();
            DB::statement(
                "INSERT INTO advertising_stats (advertisement_id, stat_date, impressions, clicks, ctr, revenue, currency, status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, 0, 0, 'INR', 1, ?, ?)
                 ON DUPLICATE KEY UPDATE {$column} = {$column} + 1,
                     ctr = ROUND(clicks / GREATEST(impressions, 1) * 100, 2),
                     updated_at = VALUES(updated_at)",
                [$adId, $now->toDateString(), $column === 'impressions' ? 1 : 0, $column === 'clicks' ? 1 : 0, $now, $now]
            );
        } catch (\Throwable $e) {
            report($e); // statistics must never break a page
        }
    }
}
