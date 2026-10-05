@extends('layouts.admin')
@section('title', 'Reports')
@section('breadcrumb', 'Reports')
@section('page_title', 'Reports')
@section('page_subtitle', 'Downloadable research shown on the Home page and at /reports. Mark one as “Exclusive” for the home sidebar card.')
@section('page_actions')
    <a href="{{ route('admin.reports.create') }}" class="btn btn-ink"><i class="ri-add-line me-1"></i> New report</a>
@endsection
@section('content')
<div class="a-card">
    <x-admin.toolbar placeholder="Search reports…" />
    @if($reports->count())
        <div class="table-responsive">
            <table class="a-table">
                <thead><tr><th style="width:72px"></th><th>Title</th><th>Date</th><th>File</th><th class="text-end">Downloads</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach($reports as $r)
                    <tr>
                        <td><img src="{{ $r->cover_url }}" class="a-thumb" alt=""></td>
                        <td class="a-title-cell">
                            <strong>{{ $r->title }} @if($r->is_exclusive)<span class="a-pill gold ms-1">Exclusive</span>@endif</strong>
                            <small>{{ $r->category_label ?: \Illuminate\Support\Str::headline($r->report_type) }} · {{ $r->author_name ?: '—' }}</small>
                        </td>
                        <td class="small text-nowrap">{{ optional($r->report_date)->format('M d, Y') }}</td>
                        <td class="small">@if($r->file_path)<span class="a-pill info">{{ strtoupper($r->file_type) }}</span>@elseif($r->external_url)<span class="a-pill muted">Link</span>@else — @endif</td>
                        <td class="text-end">{{ number_format($r->download_count) }}</td>
                        <td><x-admin.pill :status="$r->status" /></td>
                        <td><div class="a-row-actions">
                                    @if($r->status == 1)<button type="button" class="btn btn-soft btn-icon btn-sm" data-copy="{{ $r->short_url }}" title="Copy short link"><i class="ri-link"></i></button>@endif
                            @if($r->status == 1 && $r->slug)<a href="{{ route('report', $r->slug) }}" target="_blank" class="btn btn-soft btn-icon btn-sm" title="View on site"><i class="ri-external-link-line"></i></a>@endif
                            <x-admin.edit-link :href="route('admin.reports.edit', $r)" />
                            <x-admin.delete :action="route('admin.reports.destroy', $r)" message="Delete “{{ $r->title }}”?" />
                        </div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['items' => $reports])
    @else
        <x-admin.empty icon="ri-file-chart-line" title="No reports yet" :action="route('admin.reports.create')" actionLabel="New report" />
    @endif
</div>
@endsection
