<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImages;
use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\AdvertisementPlacement;
use App\Support\AdStats;
use App\Support\DateRange;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdvertisementController extends Controller
{
    use HandlesImages;

    public function index(Request $request)
    {
        $range = DateRange::fromRequest($request); // All time / Today / This month / Date range

        $ads = Advertisement::with('placement:id,name,slug,dimensions')
            ->where('status', '!=', 4)
            ->when($request->q, fn ($q, $t) => $q->where('title', 'like', "%{$t}%"))
            ->when($request->placement, fn ($q, $p) => $q->where('placement_id', $p))
            ->latest('id')
            ->paginate(20)->withQueryString();

        $placements = AdvertisementPlacement::where('status', '!=', 4)->orderBy('name')->pluck('name', 'id');

        // Views & clicks in the chosen dates (all time = the running totals on each ad)
        $stats = $range->active() ? AdStats::totals($ads->pluck('id')->all(), $range->from, $range->to) : collect();

        return view('admin.advertisements.index', compact('ads', 'placements', 'range', 'stats'));
    }

    /**
     * Report for one ad: totals, clicks per day, pages and devices (+ CSV export).
     */
    public function report(Request $request, Advertisement $advertisement)
    {
        $range = DateRange::fromRequest($request);
        [$from, $to] = [$range->from, $range->to];

        $daily = AdStats::daily($advertisement->id, $from, $to);
        $views = $range->active() ? $daily->sum('views') : (int) $advertisement->impression_count;
        $clicks = $range->active() ? $daily->sum('clicks') : (int) $advertisement->click_count;

        return view('admin.advertisements.report', [
            'ad' => $advertisement->load('placement:id,name,slug'),
            'range' => $range,
            'daily' => $daily,
            'views' => $views,
            'clicks' => $clicks,
            'ctr' => AdStats::ctr($clicks, $views),
            'pages' => AdStats::pages($advertisement->id, $from, $to),
            'devices' => AdStats::devices($advertisement->id, $from, $to),
        ]);
    }

    public function export(Request $request, Advertisement $advertisement)
    {
        $range = DateRange::fromRequest($request);
        $daily = AdStats::daily($advertisement->id, $range->from, $range->to);
        $name = 'ad-report-' . Str::slug($advertisement->title) . '-' . ($range->active() ? $range->fromValue() . '-to-' . $range->toValue() : 'all-time') . '.csv';

        return response()->streamDownload(function () use ($daily, $advertisement) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Advertisement', $advertisement->title]);
            fputcsv($out, []);
            fputcsv($out, ['Date', 'Views', 'Clicks', 'CTR']);
            foreach ($daily as $row) {
                fputcsv($out, [$row['date']->format('Y-m-d'), $row['views'], $row['clicks'], $row['ctr']]);
            }
            fputcsv($out, ['Total', $daily->sum('views'), $daily->sum('clicks'), AdStats::ctr($daily->sum('clicks'), $daily->sum('views'))]);
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv']);
    }

    public function create(Request $request)
    {
        // ?placement=popup-ad opens the form with that placement already chosen
        $placementId = $request->placement
            ? AdvertisementPlacement::where('slug', $request->placement)->orWhere('id', $request->placement)->value('id')
            : null;

        return view('admin.advertisements.form', [
            'ad' => new Advertisement(['status' => 1, 'is_active' => true, 'target' => '_blank', 'placement_id' => $placementId]),
            'placements' => $this->placements(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['image'] = $this->resolveImage($request, 'image', null, 'ads', 1600);
        Advertisement::create($data);

        return redirect()->route('admin.advertisements.index')->with('success', 'Advertisement created.');
    }

    public function show(Advertisement $advertisement)
    {
        return redirect()->route('admin.advertisements.edit', $advertisement);
    }

    public function edit(Advertisement $advertisement)
    {
        return view('admin.advertisements.form', ['ad' => $advertisement, 'placements' => $this->placements()]);
    }

    public function update(Request $request, Advertisement $advertisement)
    {
        $data = $this->validated($request, $advertisement);
        $data['image'] = $this->resolveImage($request, 'image', $advertisement->image, 'ads', 1600);
        $advertisement->update($data);

        return redirect()->route('admin.advertisements.index')->with('success', 'Advertisement updated.');
    }

    public function destroy(Advertisement $advertisement)
    {
        $advertisement->update(['status' => 4, 'is_active' => false, 'slug' => $advertisement->slug . '-deleted-' . $advertisement->id]);

        return redirect()->route('admin.advertisements.index')->with('success', 'Advertisement deleted.');
    }

    protected function placements()
    {
        return AdvertisementPlacement::where('status', '!=', 4)->orderBy('name')->get()
            ->mapWithKeys(fn ($p) => [$p->id => $p->name . ($p->dimensions ? " ({$p->dimensions})" : '')]);
    }

    protected function validated(Request $request, ?Advertisement $ad = null): array
    {
        $data = $request->validate([
            'placement_id' => 'required|exists:advertisement_placements,id',
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:50',
            'url' => 'nullable|string|max:191',
            'target' => 'required|in:_blank,_self',
            'alt_text' => 'nullable|string|max:191',
            'code' => 'nullable|string|max:20000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
            'status' => 'required|in:0,1',
        ] + $this->imageRules('image'));

        unset($data['image'], $data['image_file']);

        $slug = Str::slug($data['title']); $base = $slug; $i = 2;
        while (Advertisement::withDeleted()->where('slug', $slug)->when($ad, fn ($q) => $q->where('id', '!=', $ad->id))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return array_merge($data, ['slug' => $slug, 'is_active' => $request->boolean('is_active')]);
    }
}
