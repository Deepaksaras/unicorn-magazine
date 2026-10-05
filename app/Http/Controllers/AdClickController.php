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
 * Not counted: bots, people logged in to the CMS, and a repeat click on the same ad
 * by the same visitor within 30 minutes.
 */
class AdClickController extends Controller
{
    public function __invoke(Request $request, int $id)
    {
        $ad = Advertisement::where('status', 1)->findOrFail($id);
        $target = $ad->url ? (Str::startsWith($ad->url, ['http://', 'https://']) ? $ad->url : url($ad->url)) : url('/');

        if (Visitor::counts($request)) {
            try {
                $visitor = Visitor::id($request);
                $repeat = DB::table('ad_clicks')
                    ->where('advertisement_id', $ad->id)
                    ->where('visitor_id', $visitor)
                    ->where('created_at', '>=', now()->subMinutes(30))
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
}
