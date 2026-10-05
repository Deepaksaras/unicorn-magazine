@extends('layouts.admin')
@php $editing = $profile->exists; @endphp
@section('title', $editing ? 'Edit profile' : 'New profile')
@section('breadcrumb')
    <a href="{{ route('admin.profiles.index') }}">Profiles</a> <i class="ri-arrow-right-s-line"></i> {{ $editing ? $profile->display_name : 'New' }}
@endsection
@section('page_title', $editing ? 'Edit profile' : 'New profile')
@if($editing && $profile->status == 1)
    @section('page_actions')
        <a href="{{ route('profile', $profile->slug) }}" target="_blank" class="btn btn-soft"><i class="ri-external-link-line me-1"></i> View on site</a>
    @endsection
@endif
@include('admin.partials.editor')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.profiles.update', $profile) : route('admin.profiles.store') }}">
    @csrf @if($editing) @method('PUT') @endif
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="a-card">
                <div class="a-card-head"><h3>Card</h3></div>
                <div class="a-card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><x-admin.input name="name" label="Name" :value="$profile->name" required /></div>
                        <div class="col-md-6"><x-admin.input name="label" label="Card label" :value="$profile->label" placeholder="Leadership, Founders…" /></div>
                    </div>
                    <x-admin.textarea name="summary" label="Card text" :value="$profile->summary" rows="2" maxlength="500" data-count="160" />
                    <x-admin.select name="post_id" label="Related article" :value="$profile->post_id" :options="$posts" placeholder="— none —" help="Shown on the profile page as “Read the full story”." />
                </div>
            </div>
            <div class="a-card">
                <div class="a-card-head"><h3>Details</h3></div>
                <div class="a-card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><x-admin.input name="company_name" label="Company" :value="$profile->company_name" /></div>
                        <div class="col-md-6"><x-admin.input name="designation" label="Designation" :value="$profile->designation" /></div>
                        <div class="col-md-6"><x-admin.input name="industry" label="Industry" :value="$profile->industry" /></div>
                        <div class="col-md-4"><x-admin.input name="net_worth" label="Net worth" :value="$profile->net_worth" maxlength="100" placeholder="e.g. ₹3,800 Cr" help="Shown exactly as typed, e.g. ₹3,800 Cr or $1.2 Billion. A plain number (38000000000) is formatted automatically using the currency." /></div>
                        <div class="col-md-2"><x-admin.input name="currency" label="Currency" :value="$profile->currency" maxlength="3" /></div>
                    </div>
                </div>
            </div>
            <div class="a-card">
                <div class="a-card-head"><div><h3>Profile page</h3><div class="a-card-sub">Shown on {{ url('/profile') }}/{{ $profile->slug ?: 'name' }}</div></div></div>
                <div class="a-card-body">
                    <x-admin.textarea name="biography" label="Biography" :value="$profile->biography_html ?? $profile->biography" rich data-height="420" />
                    <x-admin.textarea name="achievements" label="Key achievements" :value="$profile->achievement_list" rows="5" placeholder="One achievement per line" help="Each line becomes one item in the “Key achievements” list." />
                    <x-admin.input name="slug" label="Page address (slug)" :value="$profile->slug" placeholder="made from the name if empty" help="Only letters, numbers and dashes. Changing it changes the page link." />
                    <div class="row g-3">
                        <div class="col-md-4"><x-admin.input name="website" label="Website" :value="$profile->website" /></div>
                        <div class="col-md-4"><x-admin.input name="linkedin_url" label="LinkedIn" :value="$profile->linkedin_url" /></div>
                        <div class="col-md-4"><x-admin.input name="twitter_url" label="X / Twitter" :value="$profile->twitter_url" /></div>
                    </div>
                </div>
            </div>
            <x-admin.seo :model="$profile" :title="$profile->name" :description="$profile->summary ?: $profile->biography" :url="$profile->exists && $profile->slug ? route('profile', $profile->slug) : null" image-help="Shown when the profile is shared. Empty = the profile photo." />
        </div>
        <div class="col-lg-4">
            <x-admin.short-link :item="$profile" />
            <div class="a-card"><div class="a-card-body">
                <x-admin.image name="profile_image" label="Photo" :value="$profile->profile_image" />
                <x-admin.select name="profile_type" label="Type" :value="$profile->profile_type" :options="$types" />
                <x-admin.input type="number" name="position" label="Order" :value="$profile->position" />
                <x-admin.status :value="$profile->status" />
            </div></div>
        </div>
    </div>
    @include('admin.partials.savebar', ['cancel' => route('admin.profiles.index')])
</form>
@endsection
