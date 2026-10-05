@extends('layouts.admin')
@php $editing = $member->exists; @endphp
@section('title', $editing ? 'Edit team member' : 'Add team member')
@section('breadcrumb')
    <a href="{{ route('admin.team-members.index') }}">Team Members</a> <i class="ri-arrow-right-s-line"></i> {{ $editing ? $member->name : 'New' }}
@endsection
@section('page_title', $editing ? 'Edit team member' : 'Add team member')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.team-members.update', $member) : route('admin.team-members.store') }}">
    @csrf @if($editing) @method('PUT') @endif
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="a-card"><div class="a-card-body">
                <div class="row g-3">
                    <div class="col-md-6"><x-admin.input name="name" label="Full name" :value="$member->name" required /></div>
                    <div class="col-md-6"><x-admin.input name="role_title" label="Role" :value="$member->role_title" placeholder="Editor in Chief" /></div>
                </div>
                <x-admin.input type="email" name="email" label="Email (not shown publicly)" :value="$member->email" />
                <x-admin.textarea name="biography" label="Short bio" :value="$member->biography" rows="4" />
            </div></div>
            <div class="a-card">
                <div class="a-card-head"><h3>Social links</h3></div>
                <div class="a-card-body">
                    <x-admin.input name="linkedin" label="LinkedIn" :value="$member->social('linkedin')" prefix='<i class="ri-linkedin-fill"></i>' placeholder="https://linkedin.com/in/…" />
                    <x-admin.input name="x" label="X / Twitter" :value="$member->social('x')" prefix='<i class="ri-twitter-x-fill"></i>' placeholder="https://x.com/…" />
                    <x-admin.input name="instagram" label="Instagram" :value="$member->social('instagram')" prefix='<i class="ri-instagram-line"></i>' placeholder="https://instagram.com/…" />
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="a-card"><div class="a-card-body">
                <x-admin.image name="photo" label="Photo" :value="$member->photo" help="Portrait works best (e.g. 900×1100)." />
                <x-admin.input type="number" name="sort_order" label="Order" :value="$member->sort_order" />
                <x-admin.status :value="$member->status" />
            </div></div>
        </div>
    </div>
    @include('admin.partials.savebar', ['cancel' => route('admin.team-members.index')])
</form>
@endsection
