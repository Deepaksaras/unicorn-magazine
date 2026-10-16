<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Small helpers shared by the ad counter and the short-link counter.
 */
class Visitor
{
    /**
     * Crawlers, link previews, scanners and monitoring tools (not counted).
     * Many of these never put the word "bot" in their name, so the plain
     * programming-library names have to be listed as well.
     */
    public const BOTS = '/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|telegram|embedly|preview|monitor'
        . '|curl|wget|python|java\/|headless|lighthouse|pingdom|uptime'
        . '|http-client|httpclient|okhttp|axios|node-fetch|got\/|apache-httpclient|libwww|lwp::|winhttp|restsharp'
        . '|dalvik|scrapy|guzzle|postman|insomnia|phantomjs|puppeteer|playwright|selenium|webdriver'
        . '|zgrab|masscan|nmap|nuclei|censys|expanse|netcraft|feedfetcher|go-http|okhttp|dart\/|cfnetwork/i';

    /**
     * Headers a browser sends when it is loading a link IN ADVANCE, before
     * anyone has clicked it. Those requests look completely normal otherwise,
     * so they have to be recognised here or they count as clicks.
     */
    public const PREFETCH_HEADERS = ['sec-purpose', 'purpose', 'x-purpose', 'x-moz'];

    /** Should this request be counted? No bots, no one logged in to the CMS. */
    public static function counts(?Request $request = null): bool
    {
        $request = $request ?: request();
        $ua = (string) $request->userAgent();

        return $ua !== ''
            && !preg_match(self::BOTS, $ua)
            && !static::isPrefetch($request)
            && !auth()->check();
    }

    /** True when the browser is only preloading the link, not opening it. */
    public static function isPrefetch(?Request $request = null): bool
    {
        $request = $request ?: request();

        foreach (self::PREFETCH_HEADERS as $header) {
            $value = strtolower((string) $request->header($header));

            if ($value !== '' && (str_contains($value, 'prefetch') || str_contains($value, 'prerender'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Has this browser ever opened a page on the site?
     *
     * The visitor cookie is handed out by TrackController the first time a
     * real page is loaded, so anyone who can see an advertisement already
     * carries it. A script that jumps straight to the click address does not,
     * which is exactly what we want to leave uncounted.
     */
    public static function hasVisited(?Request $request = null): bool
    {
        $request = $request ?: request();

        return (bool) preg_match('/^[A-Za-z0-9]{20,40}$/', (string) $request->cookie('um_vid'));
    }

    /** True when the click came from a page on our own site (or sent no referrer at all). */
    public static function fromOurSite(?Request $request = null): bool
    {
        $request = $request ?: request();
        $referer = (string) $request->header('referer');

        if ($referer === '') {
            return true; // many browsers strip it for privacy - do not punish those
        }

        $host = preg_replace('/^www\./', '', strtolower((string) parse_url($referer, PHP_URL_HOST)));

        return $host === preg_replace('/^www\./', '', strtolower($request->getHost()));
    }

    /** Anonymous id: the visitor-counter cookie, or a hash of IP + browser. */
    public static function id(?Request $request = null): string
    {
        $request = $request ?: request();
        $cookie = (string) $request->cookie('um_vid');

        if (preg_match('/^[A-Za-z0-9]{20,40}$/', $cookie)) {
            return $cookie;
        }

        return 'h' . substr(sha1($request->ip() . '|' . $request->userAgent()), 0, 31);
    }

    public static function device(?string $ua = null): string
    {
        $ua = $ua ?? (string) request()->userAgent();

        if (preg_match('/ipad|tablet|kindle|silk|(android(?!.*mobile))/i', $ua)) {
            return 'tablet';
        }

        return preg_match('/mobi|iphone|ipod|android|blackberry|opera mini|iemobile/i', $ua) ? 'mobile' : 'desktop';
    }
}
