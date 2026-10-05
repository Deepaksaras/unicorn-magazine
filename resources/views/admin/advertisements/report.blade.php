@extends('layouts.admin')
@section('title', 'Ad report')
@section('breadcrumb')
    <a href="{{ route('admin.advertisements.index') }}">Advertisements</a> <i class="ri-arrow-right-s-line"></i> Report
@endsection
@section('page_title', $ad->title)
@section('page_subtitle', 'Views and clicks · ' . $range->label . ($ad->placement ? ' · ' . $ad->placement->name : ''))
@section('page_actions')
    <a href="{{ route('admin.advertisements.export', array_merge(['advertisement' => $ad], request()->only(['period', 'from', 'to']))) }}" class="btn btn-soft"><i class="ri-download-2-line me-1"></i> Export CSV</a>
    <a href="{{ route('admin.advertisements.edit', $ad) }}" class="btn btn-ink"><i class="ri-edit-line me-1"></i> Edit ad</a>
@endsection

@section('content')
@if($ad->code)
    <div class="alert alert-info border-0 shadow-sm" style="border-radius:12px">
        <i class="ri-information-line me-1"></i> This is a code (AdSense / HTML) ad: views are counted here, but clicks happen inside Google's frame and are reported in your AdSense account.
    </div>
@endif

<div class="a-card">
    <x-admin.date-filter :range="$range" />

    <div class="a-card-body">
        <div class="row g-3">
            @foreach([
                ['ri-eye-line', number_format($views), 'Views', 'Times the ad was shown'],
                ['ri-cursor-line', number_format($clicks), 'Clicks', 'Visitors who opened the ad link'],
                ['ri-percent-line', $ctr, 'CTR', 'Clicks ÷ views'],
            ] as [$icon, $value, $label, $help])
                <div class="col-md-4">
                    <div class="a-stat h-100">
                        <span class="a-stat-icon {{ $loop->index === 1 ? 'ink' : '' }}"><i class="{{ $icon }}"></i></span>
                        <div><div class="a-stat-value">{{ $value }}</div><div class="a-stat-label">{{ $label }} · <span class="text-muted">{{ $help }}</span></div></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <div class="a-card">
            <div class="a-card-head"><div><h3>Day by day</h3><div class="a-card-sub">Newest first</div></div></div>
            @if($daily->count())
                <div class="table-responsive">
                    <table class="a-table">
                        <thead><tr><th>Date</th><th class="text-end">Views</th><th class="text-end">Clicks</th><th class="text-end">CTR</th></tr></thead>
                        <tbody>
                            @foreach($daily as $row)
                                <tr>
                                    <td>{{ $row['date']->format('D, d M Y') }}</td>
                                    <td class="text-end">{{ number_format($row['views']) }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($row['clicks']) }}</td>
                                    <td class="text-end text-muted">{{ $row['ctr'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-admin.empty icon="ri-bar-chart-2-line" title="No data for these dates" text="Views and clicks are recorded from the day tracking was switched on." />
            @endif
        </div>
    </div>

    <div class="col-xl-5">
        <div class="a-card">
            <div class="a-card-head"><h3>Clicked on these pages</h3></div>
            <div class="a-card-body">
                @forelse($pages as $p)
                    <div class="d-flex justify-content-between gap-3 py-2 {{ $loop->last ? '' : 'border-bottom' }}" style="border-color:var(--line-2)!important">
                        <span class="text-truncate" title="{{ $p['path'] }}">{{ $p['label'] }}</span>
                        <strong>{{ number_format($p['clicks']) }}</strong>
                    </div>
                @empty
                    <div class="text-muted small">No clicks in this period.</div>
                @endforelse
            </div>
        </div>

        <div class="a-card">
            <div class="a-card-head"><h3>Devices</h3></div>
            <div class="a-card-body">
                @foreach($devices as $key => $d)
                    <div class="{{ $loop->last ? '' : 'mb-3' }}">
                        <div class="d-flex justify-content-between small mb-1">
                            <span><i class="{{ ['desktop' => 'ri-computer-line', 'mobile' => 'ri-smartphone-line', 'tablet' => 'ri-tablet-line'][$key] }} me-1 text-muted"></i>{{ $d['label'] }}</span>
                            <span><strong>{{ $d['percent'] }}%</strong> <span class="text-muted">({{ number_format($d['count']) }})</span></span>
                        </div>
                        <div style="height:8px;background:var(--line-2);border-radius:99px;overflow:hidden"><span style="display:block;height:100%;width:{{ $d['percent'] }}%;background:var(--gold);border-radius:99px"></span></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
