{{--
    Dashboard → "Website visitors" (simple version: counts only)
    Data: App\Support\VisitorStats (collected by App\Http\Controllers\TrackController)
--}}
@php
    $range = $visitors->range;

    // Works with both the new and the older DashboardController
    $online = $visitorData['online'] ?? 0;
    $online = is_array($online) ? ($online['count'] ?? 0) : $online;
    $visitorCount = $visitorData['visitors'] ?? ($visitorData['summary']['visitors'] ?? 0);
    $viewCount = $visitorData['views'] ?? ($visitorData['summary']['views'] ?? 0);
    $adClicks = $visitorData['ad_clicks'] ?? null;
@endphp

@push('styles')
<style>
    .v-card .a-card-head { flex-wrap: wrap; gap: 12px; }
    .v-filter { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .v-seg { display: inline-flex; background: var(--line-2); border-radius: 999px; padding: 3px; }
    .v-seg a, .v-seg button { border: 0; background: transparent; color: var(--muted); font-size: 13px; font-weight: 600; padding: 6px 14px; border-radius: 999px; text-decoration: none; white-space: nowrap; }
    .v-seg a:hover, .v-seg button:hover { color: var(--text); }
    .v-seg .active { background: var(--card); color: var(--text); box-shadow: var(--shadow-sm); }
    .v-range { display: none; align-items: center; gap: 6px; }
    .v-range.show { display: flex; }
    .v-range input { height: 34px; font-size: 13px; padding: 4px 10px; width: 140px; }
    .v-tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; }
    .v-tile { border: 1px solid var(--line); border-radius: var(--radius-sm); padding: 16px 18px; background: var(--card); }
    .v-tile-label { font-size: 12.5px; color: var(--muted); font-weight: 600; display: flex; align-items: center; gap: 6px; }
    .v-tile-value { font-size: 28px; font-weight: 700; letter-spacing: -.5px; line-height: 1.15; margin-top: 6px; color: var(--text); }
    .v-tile-foot { font-size: 12px; color: var(--muted); margin-top: 4px; }
    .v-tile.live { background: linear-gradient(135deg, #0f1a14, #13261b); border-color: transparent; }
    .v-tile.live .v-tile-label, .v-tile.live .v-tile-foot { color: #9fd8b4; }
    .v-tile.live .v-tile-value { color: #fff; }
    .v-dot { width: 9px; height: 9px; border-radius: 50%; background: #22c55e; animation: vpulse 1.8s infinite; }
    @keyframes vpulse { 0% { box-shadow: 0 0 0 0 rgba(34,197,94,.55); } 70% { box-shadow: 0 0 0 9px rgba(34,197,94,0); } 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); } }
    @media (max-width: 575px) { .v-range input { width: 125px; } }
</style>
@endpush

<div class="a-card v-card" id="visitors">
    <div class="a-card-head">
        <div>
            <h3>Website visitors</h3>
            <div class="a-card-sub">{{ $visitors->label }}</div>
        </div>

        <form class="v-filter" method="GET" action="{{ route('admin.dashboard') }}#visitors">
            <div class="v-seg">
                <a href="{{ route('admin.dashboard', ['range' => 'today']) }}#visitors" class="{{ $range === 'today' ? 'active' : '' }}">Today</a>
                <a href="{{ route('admin.dashboard', ['range' => 'month']) }}#visitors" class="{{ $range === 'month' ? 'active' : '' }}">This month</a>
                <button type="button" class="{{ $range === 'custom' ? 'active' : '' }}" data-v-range-toggle><i class="ri-calendar-line me-1"></i>Date range</button>
            </div>
            <div class="v-range {{ $range === 'custom' ? 'show' : '' }}" data-v-range>
                <input type="hidden" name="range" value="custom">
                <input type="date" name="from" class="form-control" value="{{ $visitors->from->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" required aria-label="From date">
                <span class="text-muted small">to</span>
                <input type="date" name="to" class="form-control" value="{{ $visitors->to->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" required aria-label="To date">
                <button type="submit" class="btn btn-ink btn-sm">Apply</button>
            </div>
        </form>
    </div>

    <div class="a-card-body">
        @if(!$visitorData)
            <div class="alert alert-warning border-0 mb-0" style="border-radius:12px">
                <i class="ri-information-line me-1"></i>
                Visitor tracking isn't set up yet. Run <code>php artisan migrate</code> once, then visits start counting.
            </div>
        @else
            <div class="v-tiles">
                <div class="v-tile live">
                    <div class="v-tile-label"><span class="v-dot"></span> Online now</div>
                    <div class="v-tile-value" data-v-online>{{ number_format((int) $online) }}</div>
                    <div class="v-tile-foot">People on the website right now</div>
                </div>
                <div class="v-tile">
                    <div class="v-tile-label"><i class="ri-user-3-line"></i> Visitors</div>
                    <div class="v-tile-value">{{ number_format((int) $visitorCount) }}</div>
                    <div class="v-tile-foot">{{ $visitors->label }}</div>
                </div>
                <div class="v-tile">
                    <div class="v-tile-label"><i class="ri-eye-line"></i> Page views</div>
                    <div class="v-tile-value">{{ number_format((int) $viewCount) }}</div>
                    <div class="v-tile-foot">{{ $visitors->label }}</div>
                </div>
                @if(!is_null($adClicks))
                    <a href="{{ route('admin.advertisements.index', $range === 'today' ? ['period' => 'today'] : ($range === 'month' ? ['period' => 'month'] : ['period' => 'custom', 'from' => $visitors->from->format('Y-m-d'), 'to' => $visitors->to->format('Y-m-d')])) }}" class="v-tile text-decoration-none">
                        <div class="v-tile-label"><i class="ri-cursor-line"></i> Ad clicks</div>
                        <div class="v-tile-value">{{ number_format((int) $adClicks) }}</div>
                        <div class="v-tile-foot">{{ $visitors->label }} · see per ad <i class="ri-arrow-right-line"></i></div>
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
(function () {
    // "Date range" button shows the two date boxes
    var toggle = document.querySelector('[data-v-range-toggle]');
    var box = document.querySelector('[data-v-range]');
    if (toggle && box) toggle.addEventListener('click', function () { box.classList.toggle('show'); });

    // Refresh "Online now" every 20 seconds
    var num = document.querySelector('[data-v-online]');
    if (!num) return;
    setInterval(function () {
        if (document.visibilityState !== 'visible') return;
        fetch(@json(route('admin.dashboard.live')), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) { if (d) num.textContent = Number(d.count).toLocaleString(); })
            .catch(function () {});
    }, 20000);
})();
</script>
@endpush
