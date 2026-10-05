@extends('layouts.admin')
@section('title', 'Subscribers')
@section('breadcrumb', 'Subscribers')
@section('page_title', 'Subscribers')
@section('page_subtitle', 'People who joined via the footer, the Subscribe pop-up or the About page.')
@section('page_actions')
    <a href="{{ route('admin.subscribers.export', request()->query()) }}" class="btn btn-soft"><i class="ri-download-2-line me-1"></i> Export CSV{{ $range->active() ? ' · ' . $range->label : '' }}</a>
@endsection
@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="a-stat"><span class="a-stat-icon ink"><i class="ri-mail-star-line"></i></span><div><div class="a-stat-value">{{ number_format($totals['active']) }}</div><div class="a-stat-label">Active subscribers</div></div></div></div>
    <div class="col-md-4"><div class="a-stat"><span class="a-stat-icon"><i class="ri-user-add-line"></i></span><div><div class="a-stat-value">{{ number_format($totals['joined']) }}</div><div class="a-stat-label">{{ $totals['joined_label'] }}</div></div></div></div>
    <div class="col-md-4"><div class="a-stat"><span class="a-stat-icon"><i class="ri-user-unfollow-line"></i></span><div><div class="a-stat-value">{{ number_format($totals['unsubscribed']) }}</div><div class="a-stat-label">Unsubscribed</div></div></div></div>
</div>
<div class="a-card">
    <x-admin.date-filter :range="$range" :count="$subscribers->total()" noun="subscribers" column="by date joined" />

    <x-admin.toolbar placeholder="Search email, name or phone…">
        <select name="status" class="form-select" data-autosubmit>
            <option value="">Any status</option>
            <option value="1" @selected(request('status') === '1')>Active</option>
            <option value="0" @selected(request('status') === '0')>Unsubscribed</option>
        </select>
    </x-admin.toolbar>
    @if($subscribers->count())
        <div class="table-responsive">
            <table class="a-table">
                <thead><tr><th>Email</th><th>Phone</th><th>Source</th><th>Status</th><th>Joined</th><th></th></tr></thead>
                <tbody>
                @foreach($subscribers as $s)
                    <tr>
                        <td class="a-title-cell"><strong>{{ $s->email }}</strong>@if($s->name)<small>{{ $s->name }}</small>@endif</td>
                        <td>{{ $s->phone ?: '—' }}</td>
                        <td><span class="a-pill muted">{{ $s->source ?: 'website' }}</span></td>
                        <td><x-admin.pill :status="$s->status" off="Unsubscribed" /></td>
                        <td class="small text-muted text-nowrap">{{ ($s->subscribed_at ?? $s->created_at)->format('M d, Y') }}</td>
                        <td><div class="a-row-actions">
                            <form method="POST" action="{{ route('admin.subscribers.toggle', $s) }}">@csrf @method('PATCH')
                                <button class="btn btn-soft btn-sm">{{ $s->status == 1 ? 'Unsubscribe' : 'Re-activate' }}</button></form>
                            <x-admin.delete :action="route('admin.subscribers.destroy', $s)" message="Delete {{ $s->email }}?" />
                        </div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['items' => $subscribers])
    @else
        <x-admin.empty icon="ri-mail-star-line" title="No subscribers yet" />
    @endif
</div>
@endsection
