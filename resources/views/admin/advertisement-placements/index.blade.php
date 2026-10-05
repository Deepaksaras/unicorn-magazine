@extends('layouts.admin')
@section('title', 'Ad Placements')
@section('breadcrumb')
    <a href="{{ route('admin.advertisements.index') }}">Advertisements</a> <i class="ri-arrow-right-s-line"></i> Placements
@endsection
@section('page_title', 'Ad Placements')
@section('page_subtitle', 'Slots on the website. The three system slots are built into the page templates.')
@section('page_actions')
    <a href="{{ route('admin.advertisement-placements.create') }}" class="btn btn-ink"><i class="ri-add-line me-1"></i> New placement</a>
@endsection
@section('content')
<div class="a-card">
    <div class="table-responsive">
        <table class="a-table">
            <thead><tr><th>Placement</th><th>Slug</th><th>Size</th><th class="text-end">Live ads</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($placements as $p)
                <tr>
                    <td class="a-title-cell"><strong>{{ $p->name }} @if(isset($system[$p->slug]))<span class="a-pill gold ms-1">System</span>@endif</strong><small>{{ $system[$p->slug] ?? $p->description }}</small></td>
                    <td><span class="a-kbd">{{ $p->slug }}</span></td>
                    <td>{{ $p->dimensions ?: '—' }}</td>
                    <td class="text-end">{{ $p->advertisements_count }}</td>
                    <td>@if($p->status == 1 && $p->is_active)<span class="a-pill success">Active</span>@else<span class="a-pill muted">Inactive</span>@endif</td>
                    <td><div class="a-row-actions">
                        <x-admin.edit-link :href="route('admin.advertisement-placements.edit', $p)" />
                        @unless(isset($system[$p->slug]))
                            <x-admin.delete :action="route('admin.advertisement-placements.destroy', $p)" message="Delete this placement and hide its ads?" />
                        @endunless
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="6"><x-admin.empty title="No placements" /></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
