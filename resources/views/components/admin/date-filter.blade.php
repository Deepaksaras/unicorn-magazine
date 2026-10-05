{{--
    Date filter bar for admin lists:  All time | Today | This month | Date range
    <x-admin.date-filter :range="$range" :count="$items->total()" noun="messages" />
    Keeps the other filters (search, status …) when you change the dates.
--}}
@props(['range', 'count' => null, 'noun' => 'items', 'column' => null])
@php
    $keep = collect(request()->except(['period', 'from', 'to', 'page']))->filter(fn ($v) => $v !== null && $v !== '' && !is_array($v));
    $link = fn (string $period) => url()->current() . '?' . http_build_query($keep->merge($period === 'all' ? [] : ['period' => $period])->all());
@endphp
<div class="a-datebar">
    <div class="a-seg" role="group" aria-label="Date filter">
        <a href="{{ $link('all') }}" class="{{ $range->period === 'all' ? 'active' : '' }}">All time</a>
        <a href="{{ $link('today') }}" class="{{ $range->period === 'today' ? 'active' : '' }}">Today</a>
        <a href="{{ $link('month') }}" class="{{ $range->period === 'month' ? 'active' : '' }}">This month</a>
        <button type="button" class="{{ $range->period === 'custom' ? 'active' : '' }}" data-daterange-toggle><i class="ri-calendar-line me-1"></i>Date range</button>
    </div>

    <form method="GET" class="a-daterange {{ $range->period === 'custom' ? 'show' : '' }}" data-daterange>
        @foreach($keep as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <input type="hidden" name="period" value="custom">
        <input type="date" name="from" class="form-control" value="{{ $range->fromValue() }}" max="{{ now()->format('Y-m-d') }}" required aria-label="From date">
        <span class="text-muted small">to</span>
        <input type="date" name="to" class="form-control" value="{{ $range->toValue() }}" max="{{ now()->format('Y-m-d') }}" required aria-label="To date">
        <button type="submit" class="btn btn-ink btn-sm">Apply</button>
    </form>

    @if(!is_null($count))
        <div class="a-datebar-count">
            <strong>{{ number_format($count) }}</strong> {{ $noun }}
            @if($range->active())<span class="text-muted">· {{ $range->label }}</span>@endif
            @if($column)<span class="text-muted d-none d-md-inline">({{ $column }})</span>@endif
        </div>
    @endif
</div>
