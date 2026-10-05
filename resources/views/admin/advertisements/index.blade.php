@extends('layouts.admin')
@section('title', 'Advertisements')
@section('breadcrumb', 'Advertisements')
@section('page_title', 'Advertisements')
@section('page_subtitle', 'Views = times an ad was shown, Clicks = visitors who opened the ad link (AdSense code ads are counted by Google, not here). Banners shown in the ad slots. Use an image, AdSense/HTML code, or just text for the built-in design. With no live ad, a “Your Advertisement Here” placeholder links to the Advertise page.')
@section('page_actions')
    <a href="{{ route('admin.advertisement-placements.index') }}" class="btn btn-soft"><i class="ri-layout-masonry-line me-1"></i> Placements</a>
    <a href="{{ route('admin.advertisements.create') }}" class="btn btn-ink"><i class="ri-add-line me-1"></i> New ad</a>
@endsection
@section('content')
<div class="a-card">
    <x-admin.date-filter :range="$range" :count="$ads->total()" noun="ads" />

    <x-admin.toolbar placeholder="Search ads…">
        <select name="placement" class="form-select" data-autosubmit>
            <option value="">All placements</option>
            @foreach($placements as $id => $name)<option value="{{ $id }}" @selected(request('placement') == $id)>{{ $name }}</option>@endforeach
        </select>
    </x-admin.toolbar>
    @if($ads->count())
        <div class="table-responsive">
            <table class="a-table">
                <thead><tr><th style="width:72px"></th><th>Ad</th><th>Placement</th><th>Schedule</th><th class="text-end">Views</th><th class="text-end">Clicks</th><th class="text-end">CTR</th><th>Live</th><th></th></tr></thead>
                <tbody>
                @foreach($ads as $ad)
                    @php
                        $live = $ad->status == 1 && $ad->is_active
                            && (!$ad->start_date || $ad->start_date->lte(today()))
                            && (!$ad->end_date || $ad->end_date->gte(today()));
                    @endphp
                    <tr>
                        <td>
                            @if($ad->image_url)<img src="{{ $ad->image_url }}" class="a-thumb" alt="">
                            @else<span class="a-thumb d-inline-grid place-items-center text-center small fw-bold" style="line-height:42px">{{ $ad->code ? '</>' : 'AD' }}</span>@endif
                        </td>
                        <td class="a-title-cell"><strong>{{ $ad->title }}</strong><small>{{ $ad->code ? 'HTML / AdSense code' : ($ad->image ? 'Image banner' : 'Text (built-in design)') }}</small></td>
                        <td><span class="a-kbd">{{ $ad->placement->slug ?? '—' }}</span></td>
                        <td class="small text-muted text-nowrap">{{ $ad->start_date?->format('M d') ?? 'Now' }} → {{ $ad->end_date?->format('M d, Y') ?? 'No end' }}</td>
                        @php
                            $v = $range->active() ? ($stats[$ad->id]['views'] ?? 0) : (int) $ad->impression_count;
                            $c = $range->active() ? ($stats[$ad->id]['clicks'] ?? 0) : (int) $ad->click_count;
                        @endphp
                        <td class="text-end">{{ number_format($v) }}</td>
                        <td class="text-end fw-semibold">{{ number_format($c) }}</td>
                        <td class="text-end text-muted small">{{ \App\Support\AdStats::ctr($c, $v) }}</td>
                        <td>@if($live)<span class="a-pill success">Live</span>@else<span class="a-pill muted">Off</span>@endif</td>
                        <td><div class="a-row-actions">
                            <a href="{{ route('admin.advertisements.report', array_merge(['advertisement' => $ad], request()->only(['period', 'from', 'to']))) }}" class="btn btn-soft btn-icon btn-sm" title="Report"><i class="ri-bar-chart-2-line"></i></a>
                            <x-admin.edit-link :href="route('admin.advertisements.edit', $ad)" />
                            <x-admin.delete :action="route('admin.advertisements.destroy', $ad)" message="Delete this advertisement?" />
                        </div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['items' => $ads])
    @else
        <x-admin.empty icon="ri-advertisement-line" title="No advertisements" :action="route('admin.advertisements.create')" actionLabel="New ad" />
    @endif
</div>
@endsection
