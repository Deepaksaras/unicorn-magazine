@extends('layouts.admin')
@php $editing = $tag->exists; @endphp
@section('title', $editing ? 'Edit tag' : 'New tag')
@section('breadcrumb')
    <a href="{{ route('admin.tags.index') }}">Tags</a> <i class="ri-arrow-right-s-line"></i> {{ $editing ? $tag->name : 'New' }}
@endsection
@section('page_title', $editing ? 'Edit tag' : 'New tag')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.tags.update', $tag) : route('admin.tags.store') }}">
    @csrf @if($editing) @method('PUT') @endif
    <div class="a-card" style="max-width:760px"><div class="a-card-body">
        <x-admin.input name="name" label="Name" :value="$tag->name" required id="tagName" />
        <x-admin.input name="slug" label="Slug" :value="$tag->slug" data-slug-from="#tagName" prefix="/tag/" />
        <x-admin.textarea name="description" label="Description" :value="$tag->description" rows="3" />
        <x-admin.status :value="$tag->status" />
    </div></div>
    <div style="max-width:760px">
        <x-admin.seo :model="$tag" :title="$tag->name ? '#' . $tag->name : null" :description="$tag->description" :url="$tag->exists ? route('tag', $tag->slug) : null" />
    </div>
    @include('admin.partials.savebar', ['cancel' => route('admin.tags.index')])
</form>
@endsection
