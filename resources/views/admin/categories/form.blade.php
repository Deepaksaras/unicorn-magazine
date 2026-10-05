@extends('layouts.admin')
@php $editing = $category->exists; @endphp
@section('title', $editing ? 'Edit category' : 'New category')
@section('breadcrumb')
    <a href="{{ route('admin.categories.index') }}">Categories</a> <i class="ri-arrow-right-s-line"></i> {{ $editing ? $category->name : 'New' }}
@endsection
@section('page_title', $editing ? 'Edit category' : 'New category')

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
    @csrf @if($editing) @method('PUT') @endif
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="a-card"><div class="a-card-body">
                <x-admin.input name="name" label="Name" :value="$category->name" required id="catName" />
                <x-admin.input name="slug" label="Slug" :value="$category->slug" data-slug-from="#catName" prefix="/category/" />
                <x-admin.textarea name="description" label="Description" :value="$category->description" rows="3" help="Shown under the title on the category page." />
            </div></div>

            <x-admin.seo :model="$category" :title="$category->name" :description="$category->description" :url="$category->exists ? route('category', $category->slug) : null" image-help="Shown when the category page is shared. Empty = the site's default share image." />
        </div>
        <div class="col-lg-4">
            <div class="a-card"><div class="a-card-body">
                <x-admin.status :value="$category->status" />
                <x-admin.select name="parent_id" label="Parent category" :value="$category->parent_id" :options="$parents" placeholder="— None (top level) —" />
                <x-admin.input type="number" name="position" label="Sort order" :value="$category->position" />
                <x-admin.image name="image" label="Image" :value="$category->image" />
            </div></div>
        </div>
    </div>
    @include('admin.partials.savebar', ['cancel' => route('admin.categories.index')])
</form>
@endsection
