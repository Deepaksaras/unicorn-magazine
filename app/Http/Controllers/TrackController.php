<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Receives the small "I'm here" signals sent by the public pages
 * (see the script at the bottom of layouts/front.blade.php).
 *
 *  POST /t/hit   – a page was opened   → counts a page view + marks the visitor online
 *  POST /t/ping  – page still open      → keeps the visitor "online now"
 */
class TrackController extends Controller
{
    /** Cookie that recognises a returning browser (random id, no personal data). */
    public const COOKIE = 'um_vid';

    /** Crawlers and monitoring tools that should not be counted. */
    protected const BOTS = '/bot|crawl|spider|slurp|facebookexternalhit|embedly|preview|monitor|curl|wget|python|java\/|headless|lighthouse|pingdom|uptime/i';

    public function hit(Request $request)
    {
        if (!$this->shouldTrack($request)) {
            return response()->noContent();
        }

        [$visitorId, $isNew] = $this->visitorId($request);
        $path = $this->cleanPath($request->input('p'));

        try {
            DB::table('site_visits')->insert([
                'visitor_id' => $visitorId,
                'path' => $path,
                'referrer_host' => $this->referrerHost($request->input('r'), $request),
                'device' => $this->device((string) $request->userAgent()),
                'created_at' => now(),
            ]);

            $this->touch($visitorId, $path);

            // Keep the tables small: now and then remove very old rows.
            if (random_int(1, 200) === 1) {
                DB::table('site_visits')->where('created_at', '<', now()->subDays(400))->delete();
                DB::table('visitor_presence')->where('last_seen_at', '<', now()->subDay())->delete();
            }
        } catch (\Throwable $e) {
            report($e); // never break the website because of statistics
        }

        return $this->respond($visitorId, $isNew);
    }

    public function ping(Request $request)
    {
        if (!$this->shouldTrack($request)) {
            return response()->noContent();
        }

        [$visitorId, $isNew] = $this->visitorId($request);

        try {
            $this->touch($visitorId, $this->cleanPath($request->input('p')));
        } catch (\Throwable $e) {
            report($e);
        }

        return $this->respond($visitorId, $isNew);
    }

    /* ------------------------------------------------------------------ */

    /** Skip bots and people logged in to the CMS (so staff don't inflate the numbers). */
    protected function shouldTrack(Request $request): bool
    {
        $ua = (string) $request->userAgent();

        if ($ua === '' || preg_match(self::BOTS, $ua)) {
            return false;
        }

        return !auth()->check();
    }

    /** @return array{0:string,1:bool} [visitor id, is it new?] */
    protected function visitorId(Request $request): array
    {
        $id = (string) $request->cookie(self::COOKIE);

        if (preg_match('/^[A-Za-z0-9]{20,40}$/', $id)) {
            return [$id, false];
        }

        return [Str::random(32), true];
    }

    protected function touch(string $visitorId, string $path): void
    {
        DB::table('visitor_presence')->upsert(
            [['visitor_id' => $visitorId, 'path' => $path, 'last_seen_at' => now()]],
            ['visitor_id'],
            ['path', 'last_seen_at']
        );
    }

    protected function respond(string $visitorId, bool $isNew)
    {
        $response = response()->noContent();

        if ($isNew) {
            // 400 days, first-party, not readable by JavaScript
            $response->withCookie(Cookie::make(self::COOKIE, $visitorId, 60 * 24 * 400, null, null, null, true, false, 'Lax'));
        }

        return $response;
    }

    /** "/test/article/abc?x=1" → "/article/abc" (removes the /test folder and query string). */
    protected function cleanPath($path): string
    {
        $path = '/' . ltrim((string) parse_url((string) $path, PHP_URL_PATH), '/');
        $base = rtrim(request()->getBaseUrl(), '/');

        if ($base !== '' && Str::startsWith($path, $base)) {
            $path = '/' . ltrim(Str::after($path, $base), '/');
        }

        return Str::limit($path, 185, '');
    }

    protected function referrerHost($referrer, Request $request): ?string
    {
        $host = strtolower((string) parse_url((string) $referrer, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host);

        if ($host === '' || $host === preg_replace('/^www\./', '', strtolower($request->getHost()))) {
            return null; // direct visit or a click inside our own site
        }

        return Str::limit($host, 185, '');
    }

    protected function device(string $ua): string
    {
        if (preg_match('/ipad|tablet|kindle|silk|(android(?!.*mobile))/i', $ua)) {
            return 'tablet';
        }

        return preg_match('/mobi|iphone|ipod|android|blackberry|opera mini|iemobile/i', $ua) ? 'mobile' : 'desktop';
    }
}
