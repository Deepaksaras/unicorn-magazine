@extends('layouts.front')

@php
    use Illuminate\Support\Str;
    $seo = $post->seoMeta;
@endphp

@section('title', ($seo->meta_title ?? null) ?: $post->title . ' - ' . \App\Support\SiteSettings::get('site_name', 'The Unicorn Magazine'))
@section('description', ($seo->meta_description ?? null) ?: Str::limit(strip_tags($post->excerpt ?: $post->content), 160))
@section('og_image', \App\Support\Media::url($seo->og_image ?? null, $post->image_url))

@section('content')

@include('partials.breadcrumb', ['items' => array_filter([
    ($post->category->name ?? '') => $post->category ? route('category', $post->category->slug) : null,
    $post->title => null,
], fn ($v, $k) => $k !== '', ARRAY_FILTER_USE_BOTH)])

<!-- =========================================================
     ARTICLE HERO
========================================================= -->
<section class="featured-stories">
    <div class="container-fluid px-4 px-lg-5">
        <div class="featured-grid detail-page">
            <article class="featured-card featured-card-large">
                <div class="featured-card-image">
                    <img src="{{ $post->image_url }}" alt="{{ $post->title }}">
                    <span class="featured-image-overlay"></span>
                    <div class="featured-large-content">
                        @if($post->category)
                            <span class="story-category">{{ $post->badge ?: $post->category->name }}</span>
                        @endif
                        <h1 class="featured-large-title">{{ $post->title }}</h1>
                        @if($post->excerpt)
                            <p class="featured-description">{{ $post->excerpt }}</p>
                        @endif
                        <div class="featured-meta">
                            <span>By {{ $post->author_name }}</span>
                            <span class="meta-dot"></span>
                            <span>{{ $post->display_date->format('M d, Y') }}</span>
                            @if($post->reading_time)
                                <span class="meta-dot"></span>
                                <span>{{ $post->reading_time }} min read</span>
                            @endif
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>

<!-- =========================================================
     BODY + SIDEBAR
========================================================= -->
<div class="container-fluid px-4 px-lg-5">

    <div class="row g-5">
        <div class="col-lg-9">

            <article class="post-detail-content">

                @include('partials.share-bar', ['url' => $post->short_url, 'title' => $post->title])

                <div class="post-body">
                    <div class="rich-text">{!! $post->content !!}</div>

                    @if($post->tags->count())
                        <div class="d-flex flex-wrap gap-2 mt-4">
                            @foreach($post->tags as $tag)
                                <a href="{{ route('tag', $tag->slug) }}" class="badge rounded-pill text-bg-light border text-decoration-none fw-normal px-3 py-2">#{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>

            </article>

            @include('partials.ads.banner', ['placement' => 'in-content-ad'])

        </div>

        <div class="col-lg-3">
            @include('partials.sidebar-featured', ['posts' => $featured, 'title' => 'Featured'])
        </div>
    </div>

    @if($related->count())
        @include('partials.section-header', [
            'title' => 'Related Stories',
            'bigText' => 'RELATED',
            'link' => $post->category ? route('category', $post->category->slug) : route('latest'),
            'class' => 'mt-4',
        ])

        <div class="top-picks-grid related-posts-grid">
            @foreach($related as $item)
                @include('partials.cards.top-pick', ['post' => $item])
            @endforeach
        </div>
    @endif

    @include('partials.ads.banner', ['placement' => 'in-content-ad'])

</div>

@endsection
