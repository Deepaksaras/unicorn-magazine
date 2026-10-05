<?php

namespace App\Http\Controllers;

use App\Rules\RealEmail;
use App\Models\Profile;
use App\Models\Report;
use App\Models\Subscriber;
use App\Support\PageContent;
use App\Support\PostFeed;
use App\Support\Seo;
use App\Support\Subscription;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Home page. Section titles, category sources and static blocks come
     * from Admin → Pages → Home Page; posts/profiles/reports from their modules.
     */
    public function index()
    {
        $content = PageContent::for('home');

        // SEO: Admin → Pages → Home Page → SEO box (else the site defaults from System → SEO)
        Seo::model($content->page);

        // Latest News: 1 big + 2 small featured, topped up with newest posts
        $hero = PostFeed::featured(3);
        if ($hero->count() < 3) {
            $hero = $hero->concat(PostFeed::base()->whereNotIn('posts.id', $hero->pluck('id'))->limit(3 - $hero->count())->get());
        }
        $strip = PostFeed::base()
            ->whereNotIn('posts.id', $hero->pluck('id'))
            ->limit((int) $content->get('latest.strip_count', 6))
            ->get();

        $funding = PostFeed::fromCategory($content->get('funding.category'), (int) $content->get('funding.count', 4));
        $business = PostFeed::fromCategory($content->get('business.category'), (int) $content->get('business.count', 5));
        $unicorn = PostFeed::fromCategory($content->get('unicorn.category'), 4);
        $startup = PostFeed::fromCategory($content->get('startup.category'), (int) $content->get('startup.count', 5));
        $founders = PostFeed::fromCategory($content->get('entrepreneurs.category'), 3);
        $billionaires = PostFeed::fromCategory($content->get('billionaires.category'), (int) $content->get('billionaires.count', 5));

        $profiles = Profile::with('post:id,slug,status')
            ->where('status', 1)
            ->orderBy('position')
            ->latest('id')
            ->limit((int) $content->get('profiles.count', 5))
            ->get();

        $reports = Report::where('status', 1)
            ->orderByDesc('report_date')
            ->latest('id')
            ->limit((int) $content->get('reports.count', 5))
            ->get();

        $exclusiveReport = Report::where('status', 1)->where('is_exclusive', true)->latest('id')->first();

        return view('home', compact(
            'content', 'hero', 'strip', 'funding', 'business', 'unicorn',
            'startup', 'founders', 'billionaires', 'profiles', 'reports', 'exclusiveReport'
        ));
    }

    /**
     * Newsletter subscription (footer, modal, about page). JSON for AJAX.
     */
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'max:191', new RealEmail()],
            'mobile' => 'nullable|string|max:20',
            'name' => 'nullable|string|max:191',
            'source' => 'nullable|string|max:50',
        ]);

        // Source shown in Admin → Subscribers: modal / footer / about … (Google and LinkedIn use their own route)
        [, $already] = Subscription::subscribe($data['email'], $data['source'] ?? 'website', [
            'name' => $data['name'] ?? null,
            'phone' => $data['mobile'] ?? null,
        ]);

        $message = $already
            ? 'You are already subscribed — thank you!'
            : 'Thank you for subscribing to ' . \App\Support\SiteSettings::get('site_name', 'The Unicorn Magazine') . '!';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'subscribed' => true, 'already' => $already]);
        }

        return back()->with('subscribed', $message);
    }
}
