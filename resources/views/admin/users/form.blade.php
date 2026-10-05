@extends('layouts.admin')
@php $editing = $user->exists; $self = $editing && $user->id == auth()->id(); @endphp
@section('title', $editing ? 'Edit user' : 'New user')
@section('breadcrumb')
    <a href="{{ route('admin.users.index') }}">Users</a> <i class="ri-arrow-right-s-line"></i> {{ $editing ? $user->name : 'New' }}
@endsection
@section('page_title', $self ? 'My account' : ($editing ? 'Edit user' : 'New user'))
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}">
    @csrf @if($editing) @method('PUT') @endif
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="a-card"><div class="a-card-body">
                <div class="row g-3">
                    <div class="col-md-6"><x-admin.input name="name" label="Name" :value="$user->name" required /></div>
                    <div class="col-md-6"><x-admin.input type="email" name="email" label="Email" :value="$user->email" required /></div>
                    <div class="col-md-6"><x-admin.input name="designation" label="Designation" :value="$user->designation" /></div>
                    <div class="col-md-6"><x-admin.input name="phone" label="Phone" :value="$user->phone" /></div>
                </div>
                <x-admin.textarea name="bio" label="Bio" :value="$user->bio" rows="3" />
            </div></div>
            <div class="a-card">
                <div class="a-card-head"><div><h3>Password</h3>@if($editing)<div class="a-card-sub">Leave empty to keep the current password.</div>@endif</div></div>
                <div class="a-card-body"><div class="row g-3">
                    <div class="col-md-6"><x-admin.input type="password" name="password" label="New password" :required="!$editing" autocomplete="new-password" help="At least 8 characters." /></div>
                    <div class="col-md-6"><x-admin.input type="password" name="password_confirmation" label="Confirm password" :required="!$editing" autocomplete="new-password" /></div>
                </div></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="a-card"><div class="a-card-body">
                <x-admin.image name="avatar" label="Avatar" :value="$user->avatar" :allowUrl="false" />
                <x-admin.select name="role" label="Role" :value="$currentRole" :options="$roles" placeholder="— No CMS access —"
                    help="Super Admin: everything · Editor: content · Author: own writing." />
                @unless($self)
                    <x-admin.status :value="$user->status" :labels="['1' => 'Active', '0' => 'Disabled']" />
                @else
                    <input type="hidden" name="status" value="1">
                @endunless
            </div></div>
        </div>
    </div>
    @include('admin.partials.savebar', ['cancel' => route('admin.users.index')])
</form>
@endsection
