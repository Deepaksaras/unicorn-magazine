<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvertisingEnquiry;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Subscriber;
use App\Support\AdStats;
use App\Support\VisitorStats;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'published' => Post::where('status', 1)->count(),
            'drafts' => Post::where('status', 0)->count(),
            'views' => (int) Post::where('status', '!=', 4)->sum('view_count'),
            'subscribers' => Subscriber::where('status', 1)->count(),
            'messages' => ContactMessage::where('status', '!=', 4)->where('is_read', 0)->count(),
            'enquiries' => AdvertisingEnquiry::where('status', '!=', 4)->where('is_read', 0)->count(),
            'categories' => Category::where('status', 1)->count(),
        ];

        $recentPosts = Post::with(['category:id,name', 'author:id,name'])
            ->where('status', '!=', 4)
            ->latest('updated_at')
            ->limit(6)
            ->get();

        $topPosts = Post::where('status', 1)->orderByDesc('view_count')->limit(5)->get(['id', 'title', 'slug', 'view_count']);

        $messages = ContactMessage::where('status', '!=', 4)->latest()->limit(5)->get();
        $enquiries = AdvertisingEnquiry::where('status', '!=', 4)->latest()->limit(5)->get();

        // Subscribers per day for the last 14 days
        $days = collect(range(13, 0))->map(fn ($i) => now()->subDays($i)->toDateString());
        $counts = Subscriber::where('status', '!=', 4)
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');
        $subscriberTrend = $days->map(fn ($d) => ['date' => $d, 'count' => (int) ($counts[$d] ?? 0)]);

        // Website visitors (filter: Today / This month / Date range)
        $visitors = VisitorStats::fromRequest($request);
        try {
            $summary = $visitors->summary();
            $visitorData = [
                'online' => VisitorStats::online()['count'],   // people on the site right now
                'visitors' => $summary['visitors'],            // unique visitors in the period
                'views' => $summary['views'],                  // page views in the period
                'ad_clicks' => AdStats::clicks($visitors->from, $visitors->to), // ad clicks in the period
            ];
        } catch (\Throwable $e) {
            $visitorData = null; // tables not created yet → run: php artisan migrate
        }

        return view('admin.dashboard', compact('stats', 'recentPosts', 'topPosts', 'messages', 'enquiries', 'subscriberTrend', 'visitors', 'visitorData'));
    }

    /**
     * "Online now" number, refreshed by the dashboard every 20 seconds.
     */
    public function live()
    {
        return response()->json(VisitorStats::online());
    }
}
