<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Profile;
use App\Models\Report;
use App\Support\Visitor;

/**
 * /s/{code} – short link for an article, profile or report.
 * Counts the open (short_clicks), then goes to the full page.
 */
class ShortLinkController extends Controller
{
    public function __invoke(string $code)
    {
        $item = Post::published()->where('short_code', $code)->first()
            ?: Profile::where('status', 1)->where('short_code', $code)->first()
            ?: Report::where('status', 1)->where('short_code', $code)->first();

        abort_unless($item, 404);

        if (Visitor::counts()) {
            $item->newQuery()->whereKey($item->id)->increment('short_clicks', 1, ['updated_at' => $item->updated_at]);
        }

        $target = $item->url ?? $item->link;
        $query = request()->getQueryString();              // keeps ?utm_source=… etc.

        return redirect()->to($target . ($query ? '?' . $query : ''), 302);
    }
}
