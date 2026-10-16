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
        // An advertisement that was deleted or switched off is not an error for
        // the reader - a page cached on their phone can still hold the old link.
        // Send them to the homepage instead of a 404.
        $ad = Advertisement::where('status', 1)->find($id);

        if (!$ad) {
            return redirect()->to(url('/'));
        }

        $target = $ad->url ? (Str::startsWith($ad->url, ['http://', 'https://']) ? $ad->url : url($ad->url)) : url('/');

        // Everything about counting sits inside one try/catch. A statistic is
        // never worth a broken page: if anything in here goes wrong the visitor
        // still reaches the advertiser.
        try {
            if ($this->isRealClick($request)) {
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
            }
        } catch (\Throwable $e) {
            report($e); // counted or not, the visitor must still get through
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
        if (!Visitor::counts($request)) {
            return false;
        }

        // method_exists keeps this working with either version of
        // App\Support\Visitor, so uploading one file without the other can
        // never take the site down - it only means fewer checks are applied.
        foreach (['hasVisited', 'fromOurSite'] as $check) {
            if (method_exists(Visitor::class, $check) && !Visitor::$check($request)) {
                return false;
            }
        }

        return true;
    }
}
