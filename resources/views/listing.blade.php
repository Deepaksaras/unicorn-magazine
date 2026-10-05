@extends('layouts.front')

@section('title', ($metaTitle ?? $title) . ' - ' . \App\Support\SiteSettings::get('site_name', 'The Unicorn Magazine'))
@if(!empty($metaDescription))
    @section('description', \Illuminate\Support\Str::limit(strip_tags($metaDescription), 160))
@endif

@section('content')

@include('partials.breadcrumb', ['items' => $breadcrumb])

<!-- =========================================================
     HEADING + LEAD STORY
========================================================= -->
<section class="featured-stories">
    <div class="container-fluid px-4 px-lg-5">

        @include('partials.section-header', ['title' => $title, 'bigText' => $bigText])

        @if(!empty($intro))
            <p class="text-muted mb-4" style="max-width: 760px;">{{ $intro }}</p>
        @endif

        @if($hero)
            <div class="row g-5">
                <div class="col-lg-9">
                    <div class="featured-grid category">
                        <article class="featured-card featured-card-large">
                            <a href="{{ $hero->url }}" class="featured-card-image">
                                <img src="{{ $hero->image_url }}" alt="{{ $hero->title }}">
                                <span class="featured-image-overlay"></span>
                                <div class="featured-large-content category">
                                    <h3 class="featured-large-title">{{ $hero->title }}</h3>
                                    @if($hero->excerpt)
                                        <p class="featured-description">{{ \Illuminate\Support\Str::limit(strip_tags($hero->excerpt), 180) }}</p>
                                    @endif
                                    <div class="featured-meta">
                                        <span>By {{ $hero->author_name }}</span>
                                        <span class="meta-dot"></span>
                                        <span>{{ $hero->display_date->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    </div>
                </div>
                <div class="col-lg-3 mt-sm-5 mt-3">
                    @include('partials.ads.square', ['placement' => 'sidebar-ad'])
                </div>
            </div>
        @endif

    </div>
</section>

<!-- =========================================================
     POSTS + FEATURED
========================================================= -->
<div class="container-fluid px-4 px-lg-5">
    <div class="row g-5">

        <div class="col-lg-9">

            @include('partials.ads.banner', ['placement' => 'in-content-ad'])

            @if($posts->count())
                <div class="top-picks-grid category" id="postGrid">
                    @include('partials.post-grid-items', ['posts' => $posts])
                </div>

                @if($posts->hasMorePages())
                    <div class="reports-explore my-4">
                        <a href="{{ $posts->nextPageUrl() }}" class="reports-explore-btn" id="loadMore">
                            Load More
                            <i class="ri-arrow-down-line"></i>
                        </a>
                    </div>
                @endif
            @elseif(!$hero)
                <div class="cms-empty">{{ $emptyText }}</div>
            @endif

            @if($posts->count())
                @include('partials.ads.banner', ['placement' => 'in-content-ad'])
            @endif

        </div>

        <div class="col-lg-3">
            @include('partials.sidebar-featured', ['posts' => $featured, 'title' => 'Featured'])
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('loadMore');
    var grid = document.getElementById('postGrid');
    if (!btn || !grid) return;

    btn.addEventListener('click', function (e) {
        e.preventDefault();
        var url = btn.getAttribute('href');
        if (!url) return;
        btn.classList.add('disabled');

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                grid.insertAdjacentHTML('beforeend', data.html);
                if (data.next) {
                    btn.setAttribute('href', data.next);
                    btn.classList.remove('disabled');
                } else {
                    btn.parentElement.remove();
                }
            })
            .catch(function () { window.location.href = url; });
    });
});
</script>
@endpush
