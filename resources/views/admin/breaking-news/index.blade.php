@extends('layouts.admin')
@section('title', 'Breaking News')
@section('breadcrumb', 'Breaking News')
@section('page_title', 'Breaking News')
@section('page_subtitle', 'The “IMPORTANT” bar at the very top of every page shows the first active headline. Drag to reorder.')
@section('page_actions')
    <a href="{{ route('admin.breaking-news.create') }}" class="btn btn-ink"><i class="ri-add-line me-1"></i> New headline</a>
@endsection
@section('content')
<div class="a-card">
    @if($items->count())
        <div class="table-responsive">
            <table class="a-table">
                <thead><tr><th style="width:40px"></th><th>Headline</th><th>Link</th><th>Status</th><th></th></tr></thead>
                <tbody data-sortable-url="{{ route('admin.breaking-news.reorder') }}">
                @foreach($items as $item)
                    <tr data-id="{{ $item->id }}">
                        <td><i class="ri-draggable handle text-muted" style="cursor:grab;font-size:18px"></i></td>
                        <td class="a-title-cell"><strong>{{ $item->title }}</strong>@if($loop->first && $item->status == 1)<small class="text-success">Showing on the site now</small>@endif</td>
                        <td class="small text-muted">{{ $item->url ?: '—' }}</td>
                        <td><x-admin.pill :status="$item->status" /></td>
                        <td><div class="a-row-actions">
                            <x-admin.edit-link :href="route('admin.breaking-news.edit', $item)" />
                            <x-admin.delete :action="route('admin.breaking-news.destroy', $item)" message="Delete this headline?" />
                        </div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <x-admin.empty icon="ri-flashlight-line" title="No headlines" text="Without an active headline the top bar is hidden." :action="route('admin.breaking-news.create')" actionLabel="New headline" />
    @endif
</div>
@endsection
