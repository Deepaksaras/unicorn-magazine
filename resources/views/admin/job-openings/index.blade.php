@extends('layouts.admin')
@section('title', 'Job Openings')
@section('breadcrumb', 'Job Openings')
@section('page_title', 'Job Openings')
@section('page_subtitle', 'Roles listed under “Current openings” on the Career page. Drag to reorder.')
@section('page_actions')
    <a href="{{ route('admin.pages.edit', 'career') }}" class="btn btn-soft"><i class="ri-layout-4-line me-1"></i> Career page texts</a>
    <a href="{{ route('admin.job-openings.create') }}" class="btn btn-ink"><i class="ri-add-line me-1"></i> New opening</a>
@endsection
@section('content')
<div class="a-card">
    <x-admin.toolbar placeholder="Search roles…" />
    @if($jobs->count())
        <div class="table-responsive">
            <table class="a-table">
                <thead><tr><th style="width:40px"></th><th>Role</th><th>Details</th><th>Status</th><th></th></tr></thead>
                <tbody data-sortable-url="{{ route('admin.job-openings.reorder') }}">
                @foreach($jobs as $job)
                    <tr data-id="{{ $job->id }}">
                        <td><i class="ri-draggable handle text-muted" style="cursor:grab;font-size:18px"></i></td>
                        <td class="a-title-cell"><strong>{{ $job->title }}</strong><small>{{ \Illuminate\Support\Str::limit($job->summary, 80) }}</small></td>
                        <td class="small text-muted">{{ $job->meta_line ?: '—' }}</td>
                        <td><x-admin.pill :status="$job->status" on="Open" off="Closed" /></td>
                        <td><div class="a-row-actions">
                            <x-admin.edit-link :href="route('admin.job-openings.edit', $job)" />
                            <x-admin.delete :action="route('admin.job-openings.destroy', $job)" message="Delete “{{ $job->title }}”?" />
                        </div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['items' => $jobs])
    @else
        <x-admin.empty icon="ri-briefcase-4-line" title="No openings" text="The Career page shows a friendly “no open roles” message." :action="route('admin.job-openings.create')" actionLabel="New opening" />
    @endif
</div>
@endsection
