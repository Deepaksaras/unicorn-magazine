@extends('layouts.admin')
@section('title', 'Team Members')
@section('breadcrumb', 'Team Members')
@section('page_title', 'Team Members')
@section('page_subtitle', 'People shown in “Meet our team” on the About page. Drag to reorder.')
@section('page_actions')
    <a href="{{ route('admin.pages.edit', 'about') }}" class="btn btn-soft"><i class="ri-layout-4-line me-1"></i> About page texts</a>
    <a href="{{ route('admin.team-members.create') }}" class="btn btn-ink"><i class="ri-add-line me-1"></i> Add member</a>
@endsection
@section('content')
<div class="a-card">
    <x-admin.toolbar placeholder="Search people…" />
    @if($members->count())
        <div class="table-responsive">
            <table class="a-table">
                <thead><tr><th style="width:40px"></th><th style="width:60px"></th><th>Name</th><th>Social</th><th>Status</th><th></th></tr></thead>
                <tbody data-sortable-url="{{ route('admin.team-members.reorder') }}">
                @foreach($members as $member)
                    <tr data-id="{{ $member->id }}">
                        <td><i class="ri-draggable handle text-muted" style="cursor:grab;font-size:18px"></i></td>
                        <td><img src="{{ $member->photo_url }}" class="a-thumb-round" alt=""></td>
                        <td class="a-title-cell"><strong>{{ $member->name }}</strong><small>{{ $member->role_title }}</small></td>
                        <td class="text-muted">
                            @foreach(['linkedin' => 'ri-linkedin-fill', 'x' => 'ri-twitter-x-fill', 'instagram' => 'ri-instagram-line'] as $k => $icon)
                                @if($member->social($k))<i class="{{ $icon }} me-1"></i>@endif
                            @endforeach
                        </td>
                        <td><x-admin.pill :status="$member->status" /></td>
                        <td><div class="a-row-actions">
                            <x-admin.edit-link :href="route('admin.team-members.edit', $member)" />
                            <x-admin.delete :action="route('admin.team-members.destroy', $member)" message="Remove {{ $member->name }} from the team?" />
                        </div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['items' => $members])
    @else
        <x-admin.empty icon="ri-team-line" title="No team members yet" :action="route('admin.team-members.create')" actionLabel="Add member" />
    @endif
</div>
@endsection
