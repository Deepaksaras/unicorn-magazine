<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * One place for every page's SEO tags (title, description, keywords, canonical, Open Graph, Twitter).
 *
 * Controllers say what the page is:
 *     Seo::model($post, ['title' => $post->title, 'description' => $post->excerpt, 'image' => $post->image_url, 'type' => 'article']);
 *     Seo::page('latest', ['title' => 'Latest News']);
 *
 * The layout prints the tags:   {!! \App\Support\Seo::render([...fallbacks from the view...]) !!}
 *
 * Admin controls the values:
 *   - Articles, categories, tags, profiles, reports, pages → "SEO" box on each edit screen (seo_meta table)
 *   - Listing pages (Latest, Reports, Profiles, Search, Archive) + site defaults → Admin → System → SEO
 * Anything left empty falls back to the page's own title / text, so a tag is never empty.
 */
class Seo
{
    /** Listing pages managed in Admin → System → SEO:  key => [label, path, default title, default description] */
    public const PAGES = [
        'latest' => ['Latest News', '/latest', 'Latest News', 'The newest startup, funding and business stories.'],
        'reports' => ['Reports (list)', '/reports', 'Reports', 'Research, market studies and industry reports.'],
        'profiles' => ['Stories & Profiles (list)', '/profiles', 'Stories & Profiles', 'Profiles of the founders, entrepreneurs, executives and billionaires shaping business.'],
        'search' => ['Search results', '/search', 'Search', ''],
        'archive' => ['Archive', '/archive', 'Archive', 'Browse every story by month.'],
    ];

    protected static array $data = [];

    /** Set values directly (lowest level). Later calls override earlier ones; empty values are ignored. */
    public static function set(array $values): void
    {
        foreach ($values as $key => $value) {
            if ($value !== null && $value !== '') {
                static::$data[$key] = $value;
            }
        }
    }

    /**
     * A page that belongs to a record (article, category, tag, profile, report, CMS page).
     * $fallback = what to use when the admin left the SEO box empty.
     */
    public static function model(?Model $model, array $fallback = []): void
    {
        static::$data = [];
        static::set($fallback);

        if (!$model) {
            return;
        }

        try {
            $meta = $model->relationLoaded('seoMeta') ? $model->seoMeta : $model->seoMeta()->first();
        } catch (\Throwable $e) {
            $meta = null;
        }

        // Older columns on pages / categories still count when the SEO box is empty
        static::set([
            'seo_title' => $model->getAttribute('meta_title'),
            'description' => $model->getAttribute('meta_description'),
        ]);

        if ($meta && (int) $meta->status === 1) {
            static::set([
                'seo_title' => $meta->meta_title,
                'description' => $meta->meta_description,
                'keywords' => $meta->meta_keywords,
                'canonical' => $meta->canonical_url,
                'og_title' => $meta->og_title,
                'og_description' => $meta->og_description,
                'image' => $meta->og_image ? Media::url($meta->og_image) : null,
                'robots' => $meta->robots,
            ]);
        }
    }

    /** A listing page managed in Admin → System → SEO (latest, reports, profiles, search, archive, tag). */
    public static function page(string $key, array $fallback = []): void
    {
        static::$data = [];
        [, , $title, $description] = self::PAGES[$key] ?? ['', '', '', ''];
        static::set(['title' => $title, 'description' => $description]);
        static::set($fallback);
        static::set([
            'seo_title' => SiteSettings::get("seo_{$key}_title"),
            'og_title' => SiteSettings::get("seo_{$key}_title"),
            'description' => SiteSettings::get("seo_{$key}_description"),
            'keywords' => SiteSettings::get("seo_{$key}_keywords"),
            'image' => ($image = SiteSettings::get("seo_{$key}_image")) ? Media::url($image) : null,
        ]);
    }

    /** Forget everything set so far (used by the 404 page). */
    public static function reset(): void
    {
        static::$data = [];
    }

    public static function get(string $key, $default = null)
    {
        return static::$data[$key] ?? $default;
    }

    /**
     * The finished values for the current page.
     * $viewFallback: title / description / image that a Blade view set with @section (used only if nothing else is set).
     */
    public static function resolve(array $viewFallback = []): array
    {
        $site = SiteSettings::get('site_name', 'The Unicorn Magazine');
        $d = static::$data;
        // values that came from a Blade @section are already HTML-escaped → turn them back into plain text
        $viewFallback = array_map(fn ($v) => html_entity_decode(trim((string) $v), ENT_QUOTES | ENT_HTML5, 'UTF-8'), $viewFallback);

        // <title>: admin SEO title as typed; otherwise "Page title - Site name"
        $pageTitle = $d['title'] ?? null;
        $title = $d['seo_title'] ?? null;
        if (!$title) {
            $fromView = trim((string) ($viewFallback['title'] ?? ''));
            $title = $pageTitle ? $pageTitle . ' - ' . $site
                : ($fromView ?: SiteSettings::get('default_meta_title', $site));
        }

        $description = $d['description'] ?? trim((string) ($viewFallback['description'] ?? ''))
            ?: SiteSettings::get('default_meta_description', SiteSettings::get('site_description', ''));
        $description = Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) $description))), 300, '…');

        // the "no picture" placeholder is not a share image
        foreach ([&$d, &$viewFallback] as &$source) {
            if (Str::contains((string) ($source['image'] ?? ''), 'placeholder.')) {
                unset($source['image']);
            }
        }
        unset($source);
        $image = $d['image'] ?? trim((string) ($viewFallback['image'] ?? ''))
            ?: (($default = SiteSettings::get('default_og_image')) ? Media::url($default) : null)
            ?: (($logo = SiteSettings::get('site_logo')) ? Media::url($logo) : null);

        $canonical = $d['canonical'] ?? null;
        if (!$canonical) {
            // current address without tracking parameters; keeps ?page= and ?type= so listings stay distinct
            $keep = array_filter(request()->only(['page', 'type', 'q']), fn ($v) => $v !== null && $v !== '' && !is_array($v));
            if (($keep['page'] ?? null) == 1) {
                unset($keep['page']);
            }
            $canonical = url()->current() . ($keep ? '?' . http_build_query($keep) : '');
        }

        return [
            'title' => trim((string) $title),
            'description' => $description,
            'keywords' => trim((string) ($d['keywords'] ?? SiteSettings::get('default_meta_keywords', ''))),
            'canonical' => $canonical,
            'robots' => $d['robots'] ?? null,
            'og_title' => trim((string) ($d['og_title'] ?? $pageTitle ?? $title)),
            'og_description' => Str::limit(trim(strip_tags((string) ($d['og_description'] ?? $description))), 300, '…'),
            'og_type' => $d['type'] ?? 'website',
            'image' => $image,
            'site' => $site,
            'twitter' => SiteSettings::get('seo_twitter_site'),
            'published' => $d['published'] ?? null,
            'modified' => $d['modified'] ?? null,
            'author' => $d['author'] ?? null,
        ];
    }

    /** All SEO tags for <head>. */
    public static function render(array $viewFallback = []): string
    {
        $s = static::resolve($viewFallback);
        $e = fn ($v) => e($v);
        $tags = [];

        $tags[] = '<title>' . $e($s['title']) . '</title>';
        if ($s['description'] !== '') {
            $tags[] = '<meta name="description" content="' . $e($s['description']) . '">';
        }
        if ($s['keywords'] !== '') {
            $tags[] = '<meta name="keywords" content="' . $e($s['keywords']) . '">';
        }
        if ($s['robots']) {
            $tags[] = '<meta name="robots" content="' . $e($s['robots']) . '">';
        }
        $tags[] = '<link rel="canonical" href="' . $e($s['canonical']) . '">';

        // Open Graph (Facebook, LinkedIn, WhatsApp)
        $tags[] = '<meta property="og:site_name" content="' . $e($s['site']) . '">';
        $tags[] = '<meta property="og:type" content="' . $e($s['og_type']) . '">';
        $tags[] = '<meta property="og:title" content="' . $e($s['og_title']) . '">';
        if ($s['og_description'] !== '') {
            $tags[] = '<meta property="og:description" content="' . $e($s['og_description']) . '">';
        }
        $tags[] = '<meta property="og:url" content="' . $e($s['canonical']) . '">';
        if ($s['image']) {
            $tags[] = '<meta property="og:image" content="' . $e($s['image']) . '">';
        }
        if ($s['og_type'] === 'article') {
            if ($s['published']) {
                $tags[] = '<meta property="article:published_time" content="' . $e($s['published']) . '">';
            }
            if ($s['modified']) {
                $tags[] = '<meta property="article:modified_time" content="' . $e($s['modified']) . '">';
            }
            if ($s['author']) {
                $tags[] = '<meta property="article:author" content="' . $e($s['author']) . '">';
            }
        }

        // Twitter / X card
        $tags[] = '<meta name="twitter:card" content="' . ($s['image'] ? 'summary_large_image' : 'summary') . '">';
        $tags[] = '<meta name="twitter:title" content="' . $e($s['og_title']) . '">';
        if ($s['og_description'] !== '') {
            $tags[] = '<meta name="twitter:description" content="' . $e($s['og_description']) . '">';
        }
        if ($s['image']) {
            $tags[] = '<meta name="twitter:image" content="' . $e($s['image']) . '">';
        }
        if ($s['twitter']) {
            $tags[] = '<meta name="twitter:site" content="' . $e('@' . ltrim($s['twitter'], '@')) . '">';
        }

        return implode("\n    ", $tags);
    }
}
