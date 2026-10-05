<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Numbers for Admin → Advertising and the Dashboard "Ad clicks" box.
 * Daily totals: advertising_stats. Single clicks: ad_clicks.
 */
class AdStats
{
    /** [ad id => ['views' => n, 'clicks' => n]] for the given dates. */
    public static function totals(array $adIds, ?Carbon $from, ?Carbon $to): Collection
    {
        if (!$adIds) {
            return collect();
        }

        try {
            return DB::table('advertising_stats')
                ->whereIn('advertisement_id', $adIds)
                ->when($from && $to, fn ($q) => $q->whereBetween('stat_date', [$from->toDateString(), $to->toDateString()]))
                ->selectRaw('advertisement_id, SUM(impressions) as views, SUM(clicks) as clicks')
                ->groupBy('advertisement_id')
                ->get()
                ->keyBy('advertisement_id')
                ->map(fn ($r) => ['views' => (int) $r->views, 'clicks' => (int) $r->clicks]);
        } catch (\Throwable $e) {
            return collect();
        }
    }

    /** Total ad clicks on the whole site in a period (Dashboard). */
    public static function clicks(Carbon $from, Carbon $to): int
    {
        try {
            return (int) DB::table('advertising_stats')
                ->whereBetween('stat_date', [$from->toDateString(), $to->toDateString()])
                ->sum('clicks');
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /** One row per day: date, views, clicks, ctr (newest first). */
    public static function daily(int $adId, ?Carbon $from, ?Carbon $to): Collection
    {
        return DB::table('advertising_stats')
            ->where('advertisement_id', $adId)
            ->when($from && $to, fn ($q) => $q->whereBetween('stat_date', [$from->toDateString(), $to->toDateString()]))
            ->orderByDesc('stat_date')
            ->get(['stat_date', 'impressions', 'clicks'])
            ->map(fn ($r) => [
                'date' => Carbon::parse($r->stat_date),
                'views' => (int) $r->impressions,
                'clicks' => (int) $r->clicks,
                'ctr' => self::ctr((int) $r->clicks, (int) $r->impressions),
            ]);
    }

    /** Pages where the ad was clicked most. */
    public static function pages(int $adId, ?Carbon $from, ?Carbon $to, int $limit = 8): Collection
    {
        $rows = self::clickQuery($adId, $from, $to)
            ->selectRaw('path, COUNT(*) as c')
            ->groupBy('path')
            ->orderByDesc('c')
            ->limit($limit)
            ->pluck('c', 'path');

        $labels = VisitorStats::labels($rows->keys());

        return $rows->map(fn ($c, $path) => ['path' => $path, 'label' => $labels[$path] ?? $path, 'clicks' => (int) $c])->values();
    }

    /** Clicks by device. */
    public static function devices(int $adId, ?Carbon $from, ?Carbon $to): Collection
    {
        $rows = self::clickQuery($adId, $from, $to)->selectRaw('device, COUNT(*) as c')->groupBy('device')->pluck('c', 'device');
        $total = max(1, $rows->sum());

        return collect(['desktop' => 'Desktop', 'mobile' => 'Mobile', 'tablet' => 'Tablet'])
            ->map(fn ($label, $key) => ['label' => $label, 'count' => (int) ($rows[$key] ?? 0), 'percent' => round(($rows[$key] ?? 0) / $total * 100)]);
    }

    public static function ctr(int $clicks, int $views): string
    {
        return $views ? number_format($clicks / $views * 100, 2) . '%' : '—';
    }

    protected static function clickQuery(int $adId, ?Carbon $from, ?Carbon $to)
    {
        return DB::table('ad_clicks')
            ->where('advertisement_id', $adId)
            ->when($from && $to, fn ($q) => $q->whereBetween('created_at', [$from, $to]));
    }
}
