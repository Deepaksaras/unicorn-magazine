@extends('layouts.admin')
@section('title', 'Tags')
@section('breadcrumb', 'Tags')
@section('page_title', 'Tags')
@section('page_subtitle', 'Keywords shown under articles; each tag has a page at /tag/slug.')
@section('page_actions')
    <a href="{{ route('admin.tags.create') }}" class="btn btn-ink"><i class="ri-add-line me-1"></i> New tag</a>
@endsection
@section('content')
<div class="a-card">
    <x-admin.toolbar placeholder="Search tags…" />
    @if($tags->count())
        <div class="table-responsive">
            <table class="a-table">
                <thead><tr><th>Name</th><th>Slug</th><th class="text-end">Articles</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach($tags as $tag)
                    <tr>
                        <td class="a-title-cell"><strong>{{ $tag->name }}</strong>@if($tag->description)<small>{{ \Illuminate\Support\Str::limit($tag->description, 70) }}</small>@endif</td>
                        <td><span class="a-kbd">{{ $tag->slug }}</span></td>
                        <td class="text-end">{{ $tag->posts_count }}</td>
                        <td><x-admin.pill :status="$tag->status" /></td>
                        <td><div class="a-row-actions">
                            <x-admin.edit-link :href="route('admin.tags.edit', $tag)" />
                            <x-admin.delete :action="route('admin.tags.destroy', $tag)" message="Delete tag “{{ $tag->name }}”?" />
                        </div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['items' => $tags])
    @else
        <x-admin.empty icon="ri-price-tag-3-line" title="No tags yet" :action="route('admin.tags.create')" actionLabel="New tag" />
    @endif
</div>
@endsection
