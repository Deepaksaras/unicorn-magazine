@extends('layouts.admin')
@php $editing = $item->exists; @endphp
@section('title', $editing ? 'Edit headline' : 'New headline')
@section('breadcrumb')
    <a href="{{ route('admin.breaking-news.index') }}">Breaking News</a> <i class="ri-arrow-right-s-line"></i> {{ $editing ? 'Edit' : 'New' }}
@endsection
@section('page_title', $editing ? 'Edit headline' : 'New headline')
@section('content')
<form method="POST" action="{{ $editing ? route('admin.breaking-news.update', $item) : route('admin.breaking-news.store') }}">
    @csrf @if($editing) @method('PUT') @endif
    <div class="a-card" style="max-width:760px"><div class="a-card-body">
        <x-admin.textarea name="title" label="Headline" :value="$item->title" rows="2" required maxlength="191" data-count="191" />
        <x-admin.input name="url" label="Link" :value="$item->url" placeholder="/article/my-story or https://…" help="Where the headline goes when clicked." />
        <div class="row g-3">
            <div class="col-md-6"><x-admin.input type="number" name="position" label="Order" :value="$item->position" /></div>
            <div class="col-md-6"><x-admin.status :value="$item->status" /></div>
        </div>
    </div></div>
    @include('admin.partials.savebar', ['cancel' => route('admin.breaking-news.index')])
</form>
@endsection
