@extends('layouts.admin')
@section('title', 'Pages')
@section('breadcrumb', 'Pages')
@section('page_title', 'Pages')
@section('page_subtitle', 'Every text, image and block on the designed pages of the website. Pick a page to edit its sections.')
@section('content')
<div class="row g-4">
    @foreach($blueprints as $key => $bp)
        @php $page = $pages[$bp['slug']] ?? null; @endphp
        <div class="col-md-6 col-xl-4">
            <div class="a-card h-100">
                <a href="{{ route('admin.pages.edit', $key) }}" class="a-page-card">
                    <span class="icon"><i class="{{ $bp['icon'] }}"></i></span>
                    <h3>{{ $page->title ?? $bp['title'] }}</h3>
                    <p>{{ $bp['description'] }}</p>
                    <div class="d-flex justify-content-between align-items-center small">
                        <span>
                            @if(!$page)<span class="a-pill muted">Using defaults</span>
                            @elseif($page->status == 1)<span class="a-pill success">Published</span>
                            @else<span class="a-pill warning">Draft</span>@endif
                        </span>
                        <span class="text-muted">{{ count($bp['sections']) }} sections <i class="ri-arrow-right-line ms-1"></i></span>
                    </div>
                </a>
                <div class="a-card-foot d-flex justify-content-between small">
                    <a href="{{ route($bp['route']) }}" target="_blank" class="a-link-muted"><i class="ri-external-link-line"></i> {{ parse_url(route($bp['route']), PHP_URL_PATH) ?: '/' }}</a>
                    @if($page)<span class="text-muted">Updated {{ $page->updated_at->diffForHumans() }}</span>@endif
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
