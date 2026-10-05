@extends('layouts.front')

{{-- /profiles – all profiles (Admin → Content → Profiles) --}}
@section('title', 'Stories & Profiles - ' . \App\Support\SiteSettings::get('site_name', 'The Unicorn Magazine'))
@section('description', 'Profiles of the founders, entrepreneurs, executives and billionaires shaping India\'s business story.')

@section('content')

@include('partials.breadcrumb', ['items' => ['Stories & Profiles' => null]])

<!-- =========================================================
     HEADING + LEAD PROFILE
========================================================= -->
<section class="featured-stories">
    <div class="container-fluid px-4 px-lg-5">

        @include('partials.section-header', ['title' => 'Stories & Profiles', 'bigText' => 'PROFILES'])

        <p class="text-muted mb-4" style="max-width: 760px;">The founders, leaders and entrepreneurs behind India's most talked-about companies.</p>

        @include('partials.type-chips', ['types' => $types, 'active' => $type, 'route' => 'profiles.index', 'all' => 'All profiles'])

        @if($hero)
            <div class="row g-5">
                <div class="col-lg-9">
                    <a href="{{ $hero->link }}" class="profile-hero">
                        <div class="profile-hero-image">
                            <img src="{{ $hero->image_url }}" alt="{{ $hero->display_name }}">
                        </div>
                        <div class="profile-hero-body">
                            <span class="story-category">{{ $hero->type_label }}</span>
                            <h2 class="profile-hero-name">{{ $hero->display_name }}</h2>
                            @if($hero->role_line)
                                <div class="profile-hero-role">{{ $hero->role_line }}</div>
                            @endif
                            <p class="profile-hero-text">{{ $hero->summary ?: \Illuminate\Support\Str::limit(strip_tags($hero->biography), 220) }}</p>
                            <div class="profile-hero-facts">
                                @if($hero->industry)<span><i class="ri-building-2-line"></i>{{ $hero->industry }}</span>@endif
                                @if($hero->net_worth_text)<span><i class="ri-money-rupee-circle-line"></i>{{ $hero->net_worth_text }}</span>@endif
                            </div>
                            <span class="um-btn mt-auto">Read profile <i class="ri-arrow-right-line"></i></span>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3">
                    @include('partials.ads.square', ['placement' => 'sidebar-ad'])
                </div>
            </div>
        @endif

    </div>
</section>

<!-- =========================================================
     PROFILE GRID + FEATURED
========================================================= -->
<div class="container-fluid px-4 px-lg-5">
    <div class="row g-5">

        <div class="col-lg-9">

            @include('partials.ads.banner', ['placement' => 'in-content-ad'])

            @if($profiles->count())
                <div class="profiles-grid listing" id="profileGrid">
                    @include('partials.cards.profile-grid-items', ['profiles' => $profiles])
                </div>

                @if($profiles->hasMorePages())
                    <div class="reports-explore my-4">
                        <a href="{{ $profiles->nextPageUrl() }}" class="reports-explore-btn" id="loadMore">
                            Load More <i class="ri-arrow-down-line"></i>
                        </a>
                    </div>
                @endif
            @elseif(!$hero)
                <div class="cms-empty">No profiles have been published yet.</div>
            @endif

        </div>

        <div class="col-lg-3">
            @include('partials.sidebar-featured', ['posts' => $featured, 'title' => 'Featured'])
        </div>

    </div>

    {{-- Latest articles from the "Stories & Profiles" category --}}
    @if($stories->count())
        @include('partials.section-header', [
            'title' => 'Latest Stories',
            'bigText' => 'STORIES',
            'link' => route('category', 'stories-profiles'),
            'class' => 'mt-4',
        ])
        <div class="top-picks-grid related-posts-grid mb-4">
            @foreach($stories as $post)
                @include('partials.cards.top-pick', ['post' => $post])
            @endforeach
        </div>
    @endif
</div>

@endsection

@include('partials.load-more-script', ['grid' => 'profileGrid'])
