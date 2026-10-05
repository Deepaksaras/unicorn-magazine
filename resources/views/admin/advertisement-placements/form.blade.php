@extends('layouts.admin')
@php $editing = $placement->exists; $isSystem = $editing && isset($system[$placement->slug]); @endphp
@section('title', $editing ? 'Edit placement' : 'New placement')
@section('breadcrumb')
    <a href="{{ route('admin.advertisement-placements.index') }}">Placements</a> <i class="ri-arrow-right-s-line"></i> {{ $editing ? $placement->name : 'New' }}
@endsection
@section('page_title', $editing ? 'Edit placement' : 'New placement')
@section('content')
<form method="POST" action="{{ $editing ? route('admin.advertisement-placements.update', $placement) : route('admin.advertisement-placements.store') }}">
    @csrf @if($editing) @method('PUT') @endif
    <div class="a-card" style="max-width:760px"><div class="a-card-body">
        @if($isSystem)
            <div class="a-help mb-3"><i class="ri-information-line"></i><div>System slot: <strong>{{ $system[$placement->slug] }}</strong>. The slug is fixed.</div></div>
        @endif
        <x-admin.input name="name" label="Name" :value="$placement->name" required id="plName" />
        <x-admin.input name="slug" label="Slug" :value="$placement->slug" data-slug-from="#plName" :readonly="$isSystem" help="Used in templates: @@include('partials.ads.banner', ['placement' => 'slug'])" />
        <x-admin.input name="description" label="Description" :value="$placement->description" />
        <div class="row g-3">
            <div class="col-md-4"><x-admin.input name="dimensions" label="Size" :value="$placement->dimensions" placeholder="728x90" /></div>
            <div class="col-md-4"><x-admin.input name="location" label="Location" :value="$placement->location" placeholder="header / sidebar" /></div>
            <div class="col-md-4"><x-admin.input type="number" name="max_ads" label="Max ads" :value="$placement->max_ads" /></div>
        </div>
        <x-admin.toggle name="is_active" label="Active" :checked="$placement->is_active" />
        <x-admin.status :value="$placement->status" />
    </div></div>
    @include('admin.partials.savebar', ['cancel' => route('admin.advertisement-placements.index')])
</form>
@endsection
