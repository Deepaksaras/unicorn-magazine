<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Numbers for Admin → Dashboard → "Website visitors".
 * Data is collected by App\Http\Controllers\TrackController.
 */
class VisitorStats
{
    /** A visitor counts as "online" if their page pinged within this many seconds. */
    public const ONLINE_SECONDS = 90;

    public string $range;   // today | month | custom
    public Carbon $from;
    public Carbon $to;
    public string $label;   // "Today", "September 2026", "01 Sep – 15 Sep 2026"
    public string $group;   // hour | day | month (chart buckets)

    public static function fromRequest(Request $request): self
    {
        $stats = new self();
        $now = now();
        $range = in_array($request->query('range'), ['today', 'month', 'custom'], true) ? $request->query('range') : 'today';

        if ($range === 'custom') {
            try {
                $from = Carbon::parse($request->query('from'))->startOfDay();
                $to = Carbon::parse($request->query('to') ?: $request->query('from'))->endOfDay();
            } catch (\Throwable $e) {
                $range = 'today';
            }
        }

        if ($range === 'custom') {
            if ($to->lt($from)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }
            if ($to->gt($now)) {
                $to = $now->copy();
            }
            if ($from->diffInDays($to) > 366) {             // keep queries fast: max 1 year
                $from = $to->copy()->subDays(366)->startOfDay();
            }
            $stats->label = $from->isSameDay($to)
                ? $from->format('d M Y')
                : $from->format('d M') . ' – ' . $to->format('d M Y');
            $days = $from->diffInDays($to) + 1;
            $stats->group = $days <= 1 ? 'hour' : ($days <= 62 ? 'day' : 'month');
        } elseif ($range === 'month') {
            $from = $now->copy()->startOfMonth();
            $to = $now->copy();
            $stats->label = $now->format('F Y');
            $stats->group = 'day';
        } else {
            $from = $now->copy()->startOfDay();
            $to = $now->copy();
            $stats->label = 'Today';
            $stats->group = 'hour';
        }

        $stats->range = $range;
        $stats->from = $from;
        $stats->to = $to;

        return $stats;
    }

    /* ------------------------------------------------------------------
     | Live
     ------------------------------------------------------------------ */

    public static function online(): array
    {
        try {
            $since = now()->subSeconds(self::ONLINE_SECONDS);
            $count = DB::table('visitor_presence')->where('last_seen_at', '>=', $since)->count();
            $pages = DB::table('visitor_presence')
                ->where('last_seen_at', '>=', $since)
                ->selectRaw('path, COUNT(*) as c')
                ->groupBy('path')
                ->orderByDesc('c')
                ->limit(5)
                ->get();

            $labels = self::labels($pages->pluck('path'));

            return [
                'count' => $count,
                'pages' => $pages->map(fn ($p) => ['path' => $p->path, 'label' => $labels[$p->path] ?? $p->path, 'count' => (int) $p->c])->values()->all(),
                'time' => now()->format('H:i:s'),
            ];
        } catch (\Throwable $e) {
            return ['count' => 0, 'pages' => [], 'time' => now()->format('H:i:s'), 'error' => true];
        }
    }

    /* ------------------------------------------------------------------
     | Totals for the chosen period (+ the period just before it)
     ------------------------------------------------------------------ */

    public function summary(): array
    {
        [$visitors, $views] = $this->totals($this->from, $this->to);

        // Same length of time just before, e.g. Today 00:00–21:00 vs Yesterday 00:00–21:00
        $length = $this->from->diffInSeconds($this->to);
        $prevTo = $this->from->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subSeconds($length);
        if ($this->range === 'month') {
            $prevFrom = $this->from->copy()->subMonthNoOverflow()->startOfMonth();
            $prevTo = $prevFrom->copy()->addSeconds($length)->min($this->from->copy()->subSecond());
        }
        [$prevVisitors, $prevViews] = $this->totals($prevFrom, $prevTo);

        return [
            'visitors' => $visitors,
            'views' => $views,
            'per_visitor' => $visitors ? round($views / $visitors, 1) : 0,
            'visitors_change' => self::change($visitors, $prevVisitors),
            'views_change' => self::change($views, $prevViews),
            'compare_label' => match ($this->range) {
                'today' => 'vs yesterday',
                'month' => 'vs same days last month',
                default => 'vs previous ' . ($this->from->diffInDays($this->to) + 1) . ' days',
            },
        ];
    }

    /** Chart: visitors + views per hour / day / month. */
    public function trend(): Collection
    {
        $format = match ($this->group) {
            'hour' => '%H',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $rows = $this->base()
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as b, COUNT(*) as views, COUNT(DISTINCT visitor_id) as visitors")
            ->groupBy('b')
            ->get()
            ->keyBy('b');

        $buckets = collect();
        if ($this->group === 'hour') {
            $last = $this->to->isToday() ? (int) now()->format('H') : 23;
            foreach (range(0, $last) as $h) {
                $key = str_pad($h, 2, '0', STR_PAD_LEFT);
                $buckets->push(['key' => $key, 'label' => $key . ':00', 'short' => $h % 3 === 0 ? $key : '']);
            }
        } elseif ($this->group === 'month') {
            for ($d = $this->from->copy()->startOfMonth(); $d->lte($this->to); $d->addMonth()) {
                $buckets->push(['key' => $d->format('Y-m'), 'label' => $d->format('M Y'), 'short' => $d->format('M')]);
            }
        } else {
            $total = $this->from->diffInDays($this->to) + 1;
            $step = $total > 20 ? 5 : ($total > 10 ? 2 : 1);
            $i = 0;
            for ($d = $this->from->copy()->startOfDay(); $d->lte($this->to); $d->addDay(), $i++) {
                $buckets->push(['key' => $d->format('Y-m-d'), 'label' => $d->format('D, d M'), 'short' => $i % $step === 0 ? $d->format('d') : '']);
            }
        }

        return $buckets->map(function ($b) use ($rows) {
            $row = $rows->get($b['key']);

            return $b + ['views' => (int) ($row->views ?? 0), 'visitors' => (int) ($row->visitors ?? 0)];
        });
    }

    /** Most viewed pages. */
    public function topPages(int $limit = 8): Collection
    {
        $rows = $this->base()
            ->selectRaw('path, COUNT(*) as views, COUNT(DISTINCT visitor_id) as visitors')
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit($limit)
            ->get();

        $labels = self::labels($rows->pluck('path'));

        return $rows->map(fn ($r) => [
            'path' => $r->path,
            'label' => $labels[$r->path] ?? $r->path,
            'views' => (int) $r->views,
            'visitors' => (int) $r->visitors,
        ]);
    }

    /** Visitors by device type. */
    public function devices(): Collection
    {
        $rows = $this->base()
            ->selectRaw('device, COUNT(DISTINCT visitor_id) as c')
            ->groupBy('device')
            ->pluck('c', 'device');

        $total = max(1, $rows->sum());

        return collect(['desktop' => 'Desktop', 'mobile' => 'Mobile', 'tablet' => 'Tablet'])
            ->map(fn ($label, $key) => [
                'label' => $label,
                'count' => (int) ($rows[$key] ?? 0),
                'percent' => round(($rows[$key] ?? 0) / $total * 100),
            ]);
    }

    /** Where visitors came from (Google, Facebook … or Direct). */
    public function sources(int $limit = 6): Collection
    {
        return $this->base()
            ->selectRaw("COALESCE(referrer_host, 'Direct / bookmark') as host, COUNT(DISTINCT visitor_id) as c")
            ->groupBy('host')
            ->orderByDesc('c')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => ['host' => $r->host, 'count' => (int) $r->c]);
    }

    /* ------------------------------------------------------------------ */

    protected function base()
    {
        return DB::table('site_visits')->whereBetween('created_at', [$this->from, $this->to]);
    }

    protected function totals(Carbon $from, Carbon $to): array
    {
        $row = DB::table('site_visits')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('COUNT(DISTINCT visitor_id) as visitors, COUNT(*) as views')
            ->first();

        return [(int) ($row->visitors ?? 0), (int) ($row->views ?? 0)];
    }

    protected static function change(int $now, int $before): ?int
    {
        if ($before === 0) {
            return $now > 0 ? null : 0; // null = "new" (nothing to compare with)
        }

        return (int) round(($now - $before) / $before * 100);
    }

    /** Turn "/article/some-slug" into the article title, "/category/x" into its name, etc. */
    public static function labels(Collection $paths): array
    {
        $labels = [];
        $fixed = [
            '/' => 'Home', '/latest' => 'Latest News', '/about' => 'About Us', '/contact' => 'Contact',
            '/career' => 'Careers', '/advertise-with-us' => 'Advertise with Us', '/reports' => 'Reports',
            '/privacy-policy' => 'Privacy Policy', '/terms-conditions' => 'Terms & Conditions',
            '/archive' => 'Archive', '/search' => 'Search results', '/profiles' => 'Stories & Profiles',
        ];

        $articleSlugs = [];
        $categorySlugs = [];
        $profileSlugs = [];
        $reportSlugs = [];
        foreach ($paths as $path) {
            if (isset($fixed[$path])) {
                $labels[$path] = $fixed[$path];
            } elseif (Str::startsWith($path, '/article/')) {
                $articleSlugs[$path] = Str::after($path, '/article/');
            } elseif (Str::startsWith($path, '/category/')) {
                $categorySlugs[$path] = Str::after($path, '/category/');
            } elseif (Str::startsWith($path, '/profile/')) {
                $profileSlugs[$path] = Str::after($path, '/profile/');
            } elseif (Str::startsWith($path, '/report/')) {
                $reportSlugs[$path] = Str::after($path, '/report/');
            } elseif (Str::startsWith($path, '/tag/')) {
                $labels[$path] = '#' . Str::headline(Str::after($path, '/tag/'));
            }
        }

        try {
            if ($articleSlugs) {
                $titles = Post::withoutGlobalScopes()->whereIn('slug', array_values($articleSlugs))->pluck('title', 'slug');
                foreach ($articleSlugs as $path => $slug) {
                    $labels[$path] = $titles[$slug] ?? $path;
                }
            }
            if ($profileSlugs) {
                $names = \App\Models\Profile::withoutGlobalScopes()->whereIn('slug', array_values($profileSlugs))->pluck('name', 'slug');
                foreach ($profileSlugs as $path => $slug) {
                    $labels[$path] = isset($names[$slug]) ? $names[$slug] . ' (profile)' : $path;
                }
            }
            if ($reportSlugs) {
                $titles = \App\Models\Report::withoutGlobalScopes()->whereIn('slug', array_values($reportSlugs))->pluck('title', 'slug');
                foreach ($reportSlugs as $path => $slug) {
                    $labels[$path] = isset($titles[$slug]) ? $titles[$slug] . ' (report)' : $path;
                }
            }
            if ($categorySlugs) {
                $names = Category::withoutGlobalScopes()->whereIn('slug', array_values($categorySlugs))->pluck('name', 'slug');
                foreach ($categorySlugs as $path => $slug) {
                    $labels[$path] = isset($names[$slug]) ? $names[$slug] . ' (category)' : $path;
                }
            }
        } catch (\Throwable $e) {
            // fall back to raw paths
        }

        return $labels;
    }
}
