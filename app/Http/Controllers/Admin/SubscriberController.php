<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Support\DateRange;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    /** Shared by the list and the CSV export, so the export follows the same filters. */
    protected function query(Request $request)
    {
        $query = Subscriber::where('status', '!=', 4)
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('email', 'like', "%{$t}%")->orWhere('name', 'like', "%{$t}%")->orWhere('phone', 'like', "%{$t}%")))
            ->when($request->filled('status'), fn ($q) => $q->where('status', (int) $request->status));

        // Today / This month / Date range → by the date they joined
        DateRange::fromRequest($request)->apply($query, 'COALESCE(subscribed_at, created_at)');

        return $query->latest();
    }

    public function index(Request $request)
    {
        $range = DateRange::fromRequest($request);
        $subscribers = $this->query($request)->paginate(25)->withQueryString();

        // "Joined" card follows the date filter (last 7 days when no filter is chosen)
        $joined = Subscriber::where('status', '!=', 4);
        $range->active()
            ? $range->apply($joined, 'COALESCE(subscribed_at, created_at)')
            : $joined->where('created_at', '>=', now()->subDays(7));

        $totals = [
            'active' => Subscriber::where('status', 1)->count(),
            'unsubscribed' => Subscriber::where('status', 0)->count(),
            'joined' => $joined->count(),
            'joined_label' => $range->active() ? 'Joined · ' . $range->label : 'Joined in the last 7 days',
        ];

        return view('admin.subscribers.index', compact('subscribers', 'totals', 'range'));
    }

    public function export(Request $request)
    {
        $rows = $this->query($request)->get();
        $range = DateRange::fromRequest($request);
        $filename = 'subscribers-' . ($range->active() ? $range->fromValue() . '-to-' . $range->toValue() : now()->format('Y-m-d')) . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Email', 'Name', 'Phone', 'Source', 'Status', 'Subscribed at']);
            foreach ($rows as $s) {
                fputcsv($out, [$s->email, $s->name, $s->phone, $s->source, $s->status == 1 ? 'Active' : 'Unsubscribed', optional($s->subscribed_at ?? $s->created_at)->format('Y-m-d H:i')]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function toggle(Subscriber $subscriber)
    {
        $active = (int) $subscriber->status !== 1;
        $subscriber->update([
            'status' => $active ? 1 : 0,
            'unsubscribed_at' => $active ? null : now(),
        ]);

        return back()->with('success', $active ? 'Subscriber re-activated.' : 'Subscriber unsubscribed.');
    }

    public function destroy(Subscriber $subscriber)
    {
        $subscriber->update(['status' => 4]);

        return back()->with('success', 'Subscriber deleted.');
    }
}
