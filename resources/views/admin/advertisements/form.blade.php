@extends('layouts.admin')
@php $editing = $ad->exists; @endphp
@section('title', $editing ? 'Edit ad' : 'New ad')
@section('breadcrumb')
    <a href="{{ route('admin.advertisements.index') }}">Advertisements</a> <i class="ri-arrow-right-s-line"></i> {{ $editing ? 'Edit' : 'New' }}
@endsection
@section('page_title', $editing ? 'Edit advertisement' : 'New advertisement')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.advertisements.update', $ad) : route('admin.advertisements.store') }}">
    @csrf @if($editing) @method('PUT') @endif
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="a-card"><div class="a-card-body">
                <x-admin.select name="placement_id" label="Placement" :value="$ad->placement_id" :options="$placements" placeholder="Choose where it appears…" required />
                <x-admin.input name="title" label="Title" :value="$ad->title" required help="Headline for text ads; internal name for image/code ads." />
                <x-admin.textarea name="description" label="Text" :value="$ad->description" rows="2" />
                <div class="row g-3">
                    <div class="col-md-5"><x-admin.input name="button_text" label="Button text" :value="$ad->button_text" placeholder="Learn More" /></div>
                    <div class="col-md-7"><x-admin.input name="url" label="Click-through URL" :value="$ad->url" placeholder="https://advertiser.com" /></div>
                </div>
            </div></div>
            <div class="a-card">
                <div class="a-card-head"><div><h3>Ad code</h3><div class="a-card-sub">Paste Google AdSense or any HTML. When filled, it is shown instead of the image/text.</div></div></div>
                <div class="a-card-body">
                    <x-admin.textarea name="code" :value="$ad->code" rows="6" class="font-monospace small" placeholder="<ins class=&quot;adsbygoogle&quot; …></ins>" />
                    <div class="form-text">Load the AdSense script once in Settings → Code snippets.</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="a-card"><div class="a-card-body">
                <x-admin.toggle name="is_active" label="Active" :checked="$ad->is_active" />
                <x-admin.status :value="$ad->status" />
                <x-admin.image name="image" label="Banner image" :value="$ad->image" contain help="Horizontal slots ~ 1200×200, square ~ 600×600." />
                <x-admin.input name="alt_text" label="Image alt text" :value="$ad->alt_text" />
                <x-admin.select name="target" label="Open link in" :value="$ad->target ?: '_blank'" :options="['_blank' => 'New tab', '_self' => 'Same tab']" />
                <div class="row g-2">
                    <div class="col-6"><x-admin.input type="date" name="start_date" label="Start" :value="optional($ad->start_date)->format('Y-m-d')" /></div>
                    <div class="col-6"><x-admin.input type="date" name="end_date" label="End" :value="optional($ad->end_date)->format('Y-m-d')" /></div>
                </div>
            </div></div>
        </div>
    </div>
    @include('admin.partials.savebar', ['cancel' => route('admin.advertisements.index')])
</form>
@endsection
