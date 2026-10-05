@extends('layouts.admin')
@section('title', 'Users')
@section('breadcrumb', 'Users')
@section('page_title', 'Users')
@section('page_subtitle', 'People who can sign in to this CMS. Their name is shown as the author on articles.')
@section('page_actions')
    <a href="{{ route('admin.users.create') }}" class="btn btn-ink"><i class="ri-user-add-line me-1"></i> New user</a>
@endsection
@section('content')
<div class="a-card">
    <x-admin.toolbar placeholder="Search name or email…">
        <select name="role" class="form-select" data-autosubmit>
            <option value="">All roles</option>
            @foreach($roles as $id => $name)<option value="{{ $id }}" @selected(request('role') == $id)>{{ $name }}</option>@endforeach
        </select>
    </x-admin.toolbar>
    @if($users->count())
        <div class="table-responsive">
            <table class="a-table">
                <thead><tr><th style="width:56px"></th><th>Name</th><th>Role</th><th class="text-end">Articles</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach($users as $u)
                    <tr>
                        <td><img src="{{ \App\Support\Media::url($u->avatar, \App\Support\Media::avatar($u->name)) }}" class="a-thumb-round" alt=""></td>
                        <td class="a-title-cell"><strong>{{ $u->name }} @if($u->id == auth()->id())<span class="a-pill gold ms-1">You</span>@endif</strong><small>{{ $u->email }}</small></td>
                        <td>@forelse($u->roles as $r)<span class="a-pill info me-1">{{ $r->name }}</span>@empty<span class="a-pill muted">No CMS access</span>@endforelse</td>
                        <td class="text-end">{{ $u->posts_count }}</td>
                        <td><x-admin.pill :status="$u->status" off="Disabled" /></td>
                        <td><div class="a-row-actions">
                            <x-admin.edit-link :href="route('admin.users.edit', $u)" />
                            @if($u->id != auth()->id())
                                <x-admin.delete :action="route('admin.users.destroy', $u)" message="Delete {{ $u->name }}? Their articles stay published." />
                            @endif
                        </div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['items' => $users])
    @else
        <x-admin.empty icon="ri-shield-user-line" title="No users found" />
    @endif
</div>
@endsection
