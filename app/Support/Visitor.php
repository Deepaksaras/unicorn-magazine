<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Small helpers shared by the ad counter and the short-link counter.
 */
class Visitor
{
    /** Crawlers, link previews and monitoring tools (not counted). */
    public const BOTS = '/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|telegram|embedly|preview|monitor|curl|wget|python|java\/|headless|lighthouse|pingdom|uptime/i';

    /** Should this request be counted? No bots, no one logged in to the CMS. */
    public static function counts(?Request $request = null): bool
    {
        $request = $request ?: request();
        $ua = (string) $request->userAgent();

        return $ua !== '' && !preg_match(self::BOTS, $ua) && !auth()->check();
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
