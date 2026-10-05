<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Support\Ads;

/**
 * POST /ad/{id}/view – sent by the pop-up when it actually opens,
 * so a pop-up that was never shown is not counted as a view.
 */
class AdViewController extends Controller
{
    public function __invoke(int $id)
    {
        $ad = Advertisement::where('status', 1)->find($id);

        if ($ad) {
            Ads::countView($ad);
        }

        return response()->noContent();
    }
}
