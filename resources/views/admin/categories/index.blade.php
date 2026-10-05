@extends('layouts.admin')
@section('title', 'Categories')
@section('breadcrumb', 'Categories')
@section('page_title', 'Categories')
@section('page_subtitle', 'Sections of the magazine. Each has its own page at /category/slug and can feed a Home page block.')
@section('page_actions')
    <a href="{{ route('admin.categories.create') }}" class="btn btn-ink"><i class="ri-add-line me-1"></i> New category</a>
@endsection

@section('content')
<div class="a-card">
    <x-admin.toolbar placeholder="Search categories…" />
    @if($categories->count())
        <div class="table-responsive">
            <table class="a-table">
                <thead><tr><th>Name</th><th>Slug</th><th class="text-end">Posts</th><th>Order</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td class="a-title-cell">
                            <strong>@if($category->parent_id)<span class="text-muted me-1">↳</span>@endif{{ $category->name }}</strong>
                            @if($category->parent)<small>in {{ $category->parent->name }}</small>@endif
                        </td>
                        <td><span class="a-kbd">{{ $category->slug }}</span></td>
                        <td class="text-end">{{ $category->posts_count }}</td>
                        <td>{{ $category->position }}</td>
                        <td><x-admin.pill :status="$category->status" /></td>
                        <td>
                            <div class="a-row-actions">
                                <a href="{{ route('category', $category->slug) }}" target="_blank" class="btn btn-soft btn-icon btn-sm" title="View"><i class="ri-external-link-line"></i></a>
                                <x-admin.edit-link :href="route('admin.categories.edit', $category)" />
                                <x-admin.delete :action="route('admin.categories.destroy', $category)" message="Delete “{{ $category->name }}”?" />
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['items' => $categories])
    @else
        <x-admin.empty icon="ri-folder-3-line" title="No categories yet" :action="route('admin.categories.create')" actionLabel="New category" />
    @endif
</div>
@endsection
