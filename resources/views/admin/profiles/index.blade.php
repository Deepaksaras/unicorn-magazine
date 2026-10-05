@extends('layouts.admin')
@section('title', 'Profiles')
@section('breadcrumb', 'Profiles')
@section('page_title', 'Profiles')
@section('page_subtitle', 'People featured in the “Stories & Profiles” block on the Home page.')
@section('page_actions')
    <a href="{{ route('admin.profiles.create') }}" class="btn btn-ink"><i class="ri-add-line me-1"></i> New profile</a>
@endsection
@section('content')
<div class="a-card">
    <x-admin.toolbar placeholder="Search names or companies…">
        <select name="type" class="form-select" data-autosubmit>
            <option value="">All types</option>
            @foreach($types as $k => $v)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $v }}</option>@endforeach
        </select>
    </x-admin.toolbar>
    @if($profiles->count())
        <div class="table-responsive">
            <table class="a-table">
                <thead><tr><th style="width:60px"></th><th>Name</th><th>Label</th><th>Type</th><th>Order</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach($profiles as $p)
                    <tr>
                        <td><img src="{{ $p->image_url }}" class="a-thumb-round" alt=""></td>
                        <td class="a-title-cell"><strong>{{ $p->display_name }}</strong><small>{{ collect([$p->designation, $p->company_name])->filter()->implode(', ') }}</small></td>
                        <td>{{ $p->label ?: '—' }}</td>
                        <td><span class="a-pill gold">{{ $types[$p->profile_type] ?? \Illuminate\Support\Str::headline($p->profile_type) }}</span></td>
                        <td>{{ $p->position }}</td>
                        <td><x-admin.pill :status="$p->status" /></td>
                        <td><div class="a-row-actions">
                                    @if($p->status == 1)<button type="button" class="btn btn-soft btn-icon btn-sm" data-copy="{{ $p->short_url }}" title="Copy short link"><i class="ri-link"></i></button>@endif
                            @if($p->status == 1 && $p->slug)<a href="{{ route('profile', $p->slug) }}" target="_blank" class="btn btn-soft btn-icon btn-sm" title="View on site"><i class="ri-external-link-line"></i></a>@endif
                            <x-admin.edit-link :href="route('admin.profiles.edit', $p)" />
                            <x-admin.delete :action="route('admin.profiles.destroy', $p)" message="Delete profile of {{ $p->display_name }}?" />
                        </div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['items' => $profiles])
    @else
        <x-admin.empty icon="ri-user-star-line" title="No profiles yet" :action="route('admin.profiles.create')" actionLabel="New profile" />
    @endif
</div>
@endsection
