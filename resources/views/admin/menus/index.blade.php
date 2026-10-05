@extends('layouts.admin')
@section('title', 'Menus')
@section('breadcrumb', 'Menus')
@section('page_title', 'Menus')
@section('page_subtitle', 'Navigation links across the site — header, footer columns, footer legal links and the side panel.')
@section('page_actions')
    <a href="{{ route('admin.menus.create') }}" class="btn btn-soft"><i class="ri-add-line me-1"></i> New menu</a>
@endsection
@section('content')
<div class="row g-4">
    @foreach($menus as $menu)
        <div class="col-md-6 col-xl-4">
            <a href="{{ route('admin.menus.edit', $menu) }}" class="a-card a-page-card">
                <span class="icon"><i class="ri-menu-search-line"></i></span>
                <h3>{{ $menu->name }} @if($menu->status != 1)<span class="a-pill muted ms-1">Hidden</span>@endif</h3>
                <p>{{ $system[$menu->slug] ?? ($menu->description ?: 'Custom menu') }}</p>
                <div class="d-flex justify-content-between align-items-center small">
                    <span class="a-kbd">{{ $menu->slug }}</span>
                    <span class="text-muted">{{ $menu->items_count }} {{ \Illuminate\Support\Str::plural('link', $menu->items_count) }} <i class="ri-arrow-right-line ms-1"></i></span>
                </div>
            </a>
        </div>
    @endforeach
</div>
@endsection
