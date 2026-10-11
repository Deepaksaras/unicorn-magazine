<?php

namespace App\Http\Controllers;

use App\Support\Media;
use App\Support\PostFeed;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * /feed – the newest stories as RSS.
 *
 * Why it matters: news aggregators, Feedly/Inoreader readers, Google Alerts,
 * Zapier/IFTTT auto-posting to social accounts, and anyone syndicating the
 * magazine all read this one address. It also gives Google another route to
 * discover a new article minutes after it is published.
 *
 * Only the headline, excerpt and picture go out – not the full article –
 * so other sites cannot simply republish the whole story.
 */
class FeedController extends Controller
{
    public const LIMIT = 30;

    public function __invoke()
    {
        $xml = Cache::remember('feed.rss', 900, fn () => $this->build());

        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }

    protected function build(): string
    {
        $site = SiteSettings::get('site_name', 'The Unicorn Magazine');
        $description = SiteSettings::get('site_description', 'Startup, business and technology stories from India.');

        try {
            $posts = PostFeed::base()->limit(self::LIMIT)->get();
        } catch (\Throwable $e) {
            report($e);
            $posts = collect();
        }

        $updated = $posts->first()?->display_date ?? now();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:media="http://search.yahoo.com/mrss/">' . "\n";
        $xml .= "  <channel>\n";
        $xml .= '    <title>' . $this->esc($site) . "</title>\n";
        $xml .= '    <link>' . $this->esc(url('/')) . "</link>\n";
        $xml .= '    <description>' . $this->esc($description) . "</description>\n";
        $xml .= "    <language>en</language>\n";
        $xml .= '    <lastBuildDate>' . $updated->toRfc2822String() . "</lastBuildDate>\n";
        $xml .= '    <atom:link href="' . $this->esc(route('feed')) . '" rel="self" type="application/rss+xml"/>' . "\n";

        if ($logo = Media::url(SiteSettings::get('site_logo'))) {
            $xml .= "    <image>\n";
            $xml .= '      <url>' . $this->esc($logo) . "</url>\n";
            $xml .= '      <title>' . $this->esc($site) . "</title>\n";
            $xml .= '      <link>' . $this->esc(url('/')) . "</link>\n";
            $xml .= "    </image>\n";
        }

        foreach ($posts as $post) {
            $summary = $this->summary($post);

            $xml .= "    <item>\n";
            $xml .= '      <title>' . $this->esc($post->title) . "</title>\n";
            $xml .= '      <link>' . $this->esc($post->url) . "</link>\n";
            $xml .= '      <guid isPermaLink="true">' . $this->esc($post->url) . "</guid>\n";
            $xml .= '      <pubDate>' . $post->display_date->toRfc2822String() . "</pubDate>\n";
            $xml .= '      <description>' . $this->esc($summary) . "</description>\n";

            if ($category = $post->category->name ?? null) {
                $xml .= '      <category>' . $this->esc($category) . "</category>\n";
            }

            if ($image = $post->image_url) {
                $xml .= '      <media:content url="' . $this->esc($image) . '" medium="image"/>' . "\n";
            }

            $xml .= "    </item>\n";
        }

        return $xml . "  </channel>\n</rss>\n";
    }

    protected function summary($post): string
    {
        $text = $post->excerpt ?: strip_tags((string) $post->content);

        return Str::limit(trim(preg_replace('/\s+/u', ' ', $text)), 400, '…');
    }

    protected function esc(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
