<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Report;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;

/**
 * /sitemap.xml – the list of pages Google should crawl.
 * Built automatically from published articles, categories, tags, profiles,
 * reports and CMS pages, so nothing has to be updated by hand.
 * Pages marked "Hide from Google" in their SEO box are left out.
 */
class SitemapController extends Controller
{
    public function __invoke()
    {
        // rebuilt at most every 30 minutes
        $xml = Cache::remember('sitemap.xml', 1800, fn () => $this->build());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    protected function build(): string
    {
        $urls = [];
        $add = function (string $loc, $lastmod = null, string $freq = 'weekly', string $priority = '0.6') use (&$urls) {
            $urls[$loc] = [$lastmod ? $lastmod->toAtomString() : null, $freq, $priority];
        };
        $hidden = fn ($model) => $model->seoMeta && str_contains((string) $model->seoMeta->robots, 'noindex');

        $latest = $this->safe(fn () => Post::published()->max('updated_at'));
        $latest = $latest ? \Illuminate\Support\Carbon::parse($latest) : null;

        // Main pages
        $add(route('home'), $latest, 'hourly', '1.0');
        $add(route('latest'), $latest, 'hourly', '0.9');
        $add(route('profiles.index'), null, 'weekly', '0.7');
        $add(route('reports.index'), null, 'weekly', '0.7');
        $add(route('archive'), null, 'weekly', '0.3');

        // Company pages (Admin → Pages); draft pages are skipped
        $pages = $this->safe(fn () => Page::with('seoMeta')->get()->keyBy('slug')) ?: collect();
        foreach ((array) config('page_blueprints') as $key => $blueprint) {
            if (in_array($key, ['home', 'not-found'], true) || empty($blueprint['route'])) {
                continue;
            }
            $page = $pages->get($blueprint['slug'] ?? '');
            if ($page && ((int) $page->status !== 1 || $hidden($page))) {
                continue;
            }
            $add(route($blueprint['route']), $page->updated_at ?? null, 'monthly', '0.5');
        }

        // Categories and tags that have published articles
        foreach ($this->safe(fn () => Category::with('seoMeta')->where('status', 1)->get()) ?: [] as $category) {
            if (!$hidden($category)) {
                $add(route('category', $category->slug), null, 'daily', '0.8');
            }
        }
        foreach ($this->safe(fn () => Tag::with('seoMeta')->where('status', 1)->whereHas('posts', fn ($q) => $q->published())->get()) ?: [] as $tag) {
            if (!$hidden($tag)) {
                $add(route('tag', $tag->slug), null, 'weekly', '0.4');
            }
        }

        // Articles
        foreach ($this->safe(fn () => Post::published()->with('seoMeta')->orderByDesc('published_at')->limit(20000)->get(['id', 'slug', 'updated_at', 'published_at'])) ?: [] as $post) {
            if (!$hidden($post)) {
                $add(route('article', $post->slug), $post->updated_at ?: $post->published_at, 'weekly', '0.8');
            }
        }

        // Profiles and reports
        foreach ($this->safe(fn () => Profile::with('seoMeta')->where('status', 1)->get(['id', 'slug', 'updated_at'])) ?: [] as $profile) {
            if (!$hidden($profile)) {
                $add(route('profile', $profile->slug), $profile->updated_at, 'monthly', '0.6');
            }
        }
        foreach ($this->safe(fn () => Report::with('seoMeta')->where('status', 1)->get(['id', 'slug', 'updated_at'])) ?: [] as $report) {
            if (!$hidden($report)) {
                $add(route('report', $report->slug), $report->updated_at, 'monthly', '0.6');
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $loc => [$lastmod, $freq, $priority]) {
            $xml .= '  <url><loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>'
                . ($lastmod ? '<lastmod>' . $lastmod . '</lastmod>' : '')
                . '<changefreq>' . $freq . '</changefreq><priority>' . $priority . '</priority></url>' . "\n";
        }

        return $xml . '</urlset>' . "\n";
    }

    /** One broken table must not break the whole sitemap. */
    protected function safe(callable $callback)
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }
}
