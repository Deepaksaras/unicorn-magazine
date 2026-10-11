<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Post;
use App\Models\Profile;
use App\Models\Report;
use App\Models\Tag;
use Illuminate\Support\Str;

/**
 * Structured data (JSON-LD) for Google.
 *
 * The meta tags written by App\Support\Seo tell Google what to SHOW.
 * This class tells Google what the page IS: a news article, a person,
 * a list of stories, who published it and when. Google uses it to decide
 * whether a page can appear in Top stories, Discover and rich results.
 *
 * Printed once in <head> by resources/views/layouts/front.blade.php:
 *     {!! \App\Support\Schema::render() !!}
 *
 * Everything is taken from values the admin already fills in, so there is
 * nothing extra to maintain.
 */
class Schema
{
    /** Extra nodes a controller or view wants to add. */
    protected static array $extra = [];

    public static function add(array $node): void
    {
        static::$extra[] = $node;
    }

    /** The whole <script type="application/ld+json"> block. */
    public static function render(): string
    {
        try {
            $graph = static::graph();
        } catch (\Throwable $e) {
            report($e);   // statistics / SEO must never break a page

            return '';
        }

        if (!$graph) {
            return '';
        }

        $json = json_encode(
            ['@context' => 'https://schema.org', '@graph' => array_values($graph)],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG
        );

        return '<script type="application/ld+json">' . $json . '</script>';
    }

    /* ------------------------------------------------------------------ */

    protected static function graph(): array
    {
        $seo = Seo::resolved();

        if (!$seo) {
            return [];
        }

        $siteUrl = rtrim(url('/'), '/') . '/';
        $graph = [static::organization($siteUrl), static::website($siteUrl)];

        // Pages that are hidden from Google (search results, 404) get no page node
        if (Str::contains((string) ($seo['robots'] ?? ''), 'noindex')) {
            return $graph;
        }

        $page = static::webPage($seo, $siteUrl);
        $graph[] = $page;

        if ($main = static::mainEntity($seo, $page['@id'])) {
            $graph[] = $main;
        }

        return array_merge($graph, static::$extra);
    }

    /** The publisher: shown as the source in Google News and rich results. */
    protected static function organization(string $siteUrl): array
    {
        $name = SiteSettings::get('site_name', 'The Unicorn Magazine');
        $logo = Media::url(SiteSettings::get('site_logo'), asset('img/logo.svg'));

        $social = collect(['linkedin_url', 'facebook_url', 'instagram_url', 'twitter_url', 'youtube_url'])
            ->map(fn ($key) => trim((string) SiteSettings::get($key)))
            ->filter(fn ($url) => Str::startsWith($url, ['http://', 'https://']))
            ->values()
            ->all();

        $node = [
            '@type' => 'NewsMediaOrganization',
            '@id' => $siteUrl . '#organization',
            'name' => $name,
            'url' => $siteUrl,
            'logo' => [
                '@type' => 'ImageObject',
                '@id' => $siteUrl . '#logo',
                'url' => $logo,
                'contentUrl' => $logo,
                'caption' => $name,
            ],
            'image' => ['@id' => $siteUrl . '#logo'],
        ];

        if ($description = SiteSettings::get('site_description')) {
            $node['description'] = static::text($description, 300);
        }

        if ($social) {
            $node['sameAs'] = $social;
        }

        if ($email = SiteSettings::get('contact_email')) {
            $node['contactPoint'] = [
                '@type' => 'ContactPoint',
                'contactType' => 'customer support',
                'email' => $email,
            ];
        }

        return $node;
    }

    /** The website itself + the search box Google can offer under the brand name. */
    protected static function website(string $siteUrl): array
    {
        $node = [
            '@type' => 'WebSite',
            '@id' => $siteUrl . '#website',
            'url' => $siteUrl,
            'name' => SiteSettings::get('site_name', 'The Unicorn Magazine'),
            'publisher' => ['@id' => $siteUrl . '#organization'],
            'inLanguage' => 'en',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => route('search') . '?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];

        if ($description = SiteSettings::get('site_description')) {
            $node['description'] = static::text($description, 300);
        }

        return $node;
    }

    /** The page being looked at. */
    protected static function webPage(array $seo, string $siteUrl): array
    {
        $url = $seo['canonical'];
        $model = Seo::subject();

        $node = [
            '@type' => static::pageType($model),
            '@id' => $url . '#webpage',
            'url' => $url,
            'name' => $seo['title'],
            'isPartOf' => ['@id' => $siteUrl . '#website'],
            'inLanguage' => 'en',
        ];

        if ($seo['description'] !== '') {
            $node['description'] = $seo['description'];
        }

        if ($seo['image']) {
            $node['primaryImageOfPage'] = ['@type' => 'ImageObject', 'url' => $seo['image']];
        }

        if ($model && $model->updated_at) {
            $node['dateModified'] = $model->updated_at->toIso8601String();
        }

        if ($seo['published']) {
            $node['datePublished'] = $seo['published'];
        }

        return $node;
    }

    protected static function pageType($model): string
    {
        if ($model instanceof Profile) {
            return 'ProfilePage';
        }

        if ($model instanceof Post || $model instanceof Report) {
            return 'ItemPage';
        }

        if ($model instanceof Category || $model instanceof Tag) {
            return 'CollectionPage';
        }

        // Listing pages have no record behind them – decide from the route
        $route = (string) optional(request()->route())->getName();

        if (in_array($route, ['home', 'latest', 'archive', 'reports.index', 'profiles.index'], true)) {
            return 'CollectionPage';
        }

        return 'WebPage';
    }

    /** The thing the page is about: the article, the report or the person. */
    protected static function mainEntity(array $seo, string $pageId): ?array
    {
        $model = Seo::subject();

        if ($model instanceof Post) {
            return static::article($model, $seo, $pageId, 'NewsArticle');
        }

        if ($model instanceof Report) {
            return static::article($model, $seo, $pageId, 'Report');
        }

        if ($model instanceof Profile) {
            return static::person($model, $seo, $pageId);
        }

        return null;
    }

    /** An article or a report. */
    protected static function article($model, array $seo, string $pageId, string $type): array
    {
        $url = $seo['canonical'];
        $title = (string) $model->title;
        $body = strip_tags((string) ($model->content ?? ''));

        $node = [
            '@type' => $type,
            '@id' => $url . '#article',
            'isPartOf' => ['@id' => $pageId],
            'mainEntityOfPage' => ['@id' => $pageId],
            // Google News wants the headline under 110 characters
            'headline' => static::text($title, 110),
            'name' => static::text($title, 300),
            'publisher' => ['@id' => rtrim(url('/'), '/') . '/#organization'],
            'inLanguage' => 'en',
            'isAccessibleForFree' => true,
        ];

        if ($seo['description'] !== '') {
            $node['description'] = $seo['description'];
        }

        if ($seo['image']) {
            $node['image'] = ['@type' => 'ImageObject', 'url' => $seo['image']];
            $node['thumbnailUrl'] = $seo['image'];
        }

        if ($seo['published']) {
            $node['datePublished'] = $seo['published'];
        }

        $node['dateModified'] = $seo['modified'] ?: ($model->updated_at?->toIso8601String() ?: ($seo['published'] ?: null));

        if ($author = static::authorName($model, $seo)) {
            $node['author'] = ['@type' => 'Person', 'name' => $author];
        }

        if ($section = static::section($model)) {
            $node['articleSection'] = $section;
        }

        if ($keywords = static::keywords($model)) {
            $node['keywords'] = $keywords;
        }

        if ($words = str_word_count($body)) {
            $node['wordCount'] = $words;
        }

        if (!empty($model->reading_time)) {
            $node['timeRequired'] = 'PT' . (int) $model->reading_time . 'M';
        }

        return array_filter($node, fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    /** A profile page (founder, executive, billionaire). */
    protected static function person(Profile $profile, array $seo, string $pageId): array
    {
        $node = [
            '@type' => 'Person',
            '@id' => $seo['canonical'] . '#person',
            'mainEntityOfPage' => ['@id' => $pageId],
            'name' => $profile->display_name,
            'url' => $seo['canonical'],
        ];

        if ($seo['description'] !== '') {
            $node['description'] = $seo['description'];
        }

        if ($seo['image']) {
            $node['image'] = ['@type' => 'ImageObject', 'url' => $seo['image']];
        }

        if ($profile->designation) {
            $node['jobTitle'] = $profile->designation;
        }

        if ($profile->company_name) {
            $node['worksFor'] = ['@type' => 'Organization', 'name' => $profile->company_name];
        }

        $sameAs = collect([$profile->website, $profile->linkedin_url, $profile->twitter_url])
            ->map(fn ($u) => trim((string) $u))
            ->filter(fn ($u) => Str::startsWith($u, ['http://', 'https://']))
            ->values()
            ->all();

        if ($sameAs) {
            $node['sameAs'] = $sameAs;
        }

        return $node;
    }

    /* ------------------------------------------------------------------ */

    protected static function authorName($model, array $seo): ?string
    {
        if (!empty($seo['author'])) {
            return $seo['author'];
        }

        $name = $model->author_name ?? ($model->author->name ?? null);

        return $name ? (string) $name : null;
    }

    protected static function section($model): ?string
    {
        if ($model instanceof Post) {
            return $model->category->name ?? null;
        }

        if ($model instanceof Report) {
            return $model->type_label ?: null;
        }

        return null;
    }

    protected static function keywords($model): array
    {
        if ($model instanceof Post) {
            try {
                return $model->tags->pluck('name')->filter()->values()->all();
            } catch (\Throwable $e) {
                return [];
            }
        }

        return [];
    }

    /** Plain one-line text, cut to a safe length. */
    protected static function text(?string $value, int $limit): string
    {
        $clean = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $value)));

        return Str::limit($clean, $limit, '…');
    }
}
