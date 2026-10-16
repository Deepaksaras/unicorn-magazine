<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Support\Ads;
use App\Support\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * /ad/{id}/click?p=/page – counts the click, then sends the visitor to the advertiser.
 *
 * The visitor is ALWAYS forwarded. The checks below only decide whether the
 * click is added to the statistics, so a real person is never blocked even if
 * one of the tests is wrong about them.
 *
 * Not counted:
 *   - bots and monitoring tools (by browser name)
 *   - a browser preloading the link before anyone clicked it
 *   - anyone who has never opened a page on the site (no visitor cookie)
 *   - a click arriving from somebody else's website
 *   - people logged in to the CMS
 *   - the same visitor clicking the same ad again within a few hours
 */
class AdClickController extends Controller
{
    /** The same visitor clicking the same ad again inside this window is counted once. */
    public const REPEAT_MINUTES = 360; // 6 hours

    public function __invoke(Request $request, int $id)
    {
        $ad = Advertisement::where('status', 1)->findOrFail($id);
        $target = $ad->url ? (Str::startsWith($ad->url, ['http://', 'https://']) ? $ad->url : url($ad->url)) : url('/');

        if ($this->isRealClick($request)) {
            try {
                $visitor = Visitor::id($request);
                $repeat = DB::table('ad_clicks')
                    ->where('advertisement_id', $ad->id)
                    ->where('visitor_id', $visitor)
                    ->where('created_at', '>=', now()->subMinutes(self::REPEAT_MINUTES))
                    ->exists();

                if (!$repeat) {
                    DB::table('ad_clicks')->insert([
                        'advertisement_id' => $ad->id,
                        'visitor_id' => $visitor,
                        'path' => Str::limit('/' . ltrim((string) parse_url((string) $request->query('p', '/'), PHP_URL_PATH), '/'), 185, ''),
                        'device' => Visitor::device($request->userAgent()),
                        'created_at' => now(),
                    ]);
                    Advertisement::whereKey($ad->id)->increment('click_count');
                    Ads::addToday($ad->id, 'clicks');

                    if (random_int(1, 200) === 1) {
                        DB::table('ad_clicks')->where('created_at', '<', now()->subDays(400))->delete();
                    }
                }
            } catch (\Throwable $e) {
                report($e); // never block the visitor because of statistics
            }
        }

        return redirect()->away($target);
    }

    /**
     * Was this a person clicking the advertisement?
     *
     * Visitor::counts() already removes bots, preloaded links and CMS users.
     * The cookie test is the one that matters most: it is handed out the first
     * time a real page is opened, so every person who can see the ad has it,
     * while a script that calls this address directly does not.
     */
    protected function isRealClick(Request $request): bool
    {
        return Visitor::counts($request)
            && Visitor::hasVisited($request)
            && Visitor::fromOurSite($request);
    }
}
