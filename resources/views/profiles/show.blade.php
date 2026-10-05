@extends('layouts.front')

{{-- /profile/{slug} – one profile (Admin → Content → Profiles) --}}
@php
    use Illuminate\Support\Str;
    $site = \App\Support\SiteSettings::get('site_name', 'The Unicorn Magazine');
    $story = $profile->post && (int) $profile->post->status === 1 ? $profile->post : null;
    $facts = array_filter([
        'Type' => $typeLabel,
        'Company' => $profile->company_name,
        'Designation' => $profile->designation,
        'Industry' => $profile->industry,
        'Net worth' => $profile->net_worth_text,
    ]);
    $links = array_filter([
        'website' => [$profile->website, 'ri-global-line', 'Website'],
        'linkedin' => [$profile->linkedin_url, 'ri-linkedin-fill', 'LinkedIn'],
        'x' => [$profile->twitter_url, 'ri-twitter-x-line', 'X'],
    ], fn ($l) => !empty($l[0]));
@endphp

@section('title', $profile->display_name . ($profile->role_line ? ' – ' . $profile->role_line : '') . ' - ' . $site)
@section('description', Str::limit(strip_tags($profile->summary ?: $profile->biography ?: $profile->display_name . ', ' . $profile->role_line), 160))
@section('og_image', $profile->image_url)

@section('content')

@include('partials.breadcrumb', ['items' => ['Stories & Profiles' => route('profiles.index'), $profile->display_name => null]])

<!-- =========================================================
     PROFILE HERO
========================================================= -->
<section class="featured-stories">
    <div class="container-fluid px-4 px-lg-5">
        <div class="profile-hero detail">
            <div class="profile-hero-image">
                <img src="{{ $profile->image_url }}" alt="{{ $profile->display_name }}">
            </div>
            <div class="profile-hero-body">
                <span class="story-category">{{ $profile->type_label }}</span>
                <h1 class="profile-hero-name">{{ $profile->display_name }}</h1>
                @if($profile->role_line)
                    <div class="profile-hero-role">{{ $profile->role_line }}</div>
                @endif
                @if($profile->summary)
                    <p class="profile-hero-text">{{ $profile->summary }}</p>
                @endif
                <div class="profile-hero-facts">
                    @if($profile->industry)<span><i class="ri-building-2-line"></i>{{ $profile->industry }}</span>@endif
                    @if($profile->net_worth_text)<span><i class="ri-money-rupee-circle-line"></i>Net worth {{ $profile->net_worth_text }}</span>@endif
                </div>
                @if($links)
                    <div class="profile-links">
                        @foreach($links as [$url, $icon, $label])
                            <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $label }}" title="{{ $label }}"><i class="{{ $icon }}"></i></a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- =========================================================
     BIOGRAPHY + SIDEBAR
========================================================= -->
<div class="container-fluid px-4 px-lg-5">
    <div class="row g-5">
        <div class="col-lg-9">

            <article class="post-detail-content">

                @include('partials.share-bar', ['url' => $profile->short_url, 'title' => $profile->display_name])

                <div class="post-body">
                    @if(trim(strip_tags($profile->biography)))
                        <h2 class="detail-heading">Biography</h2>
                        <div class="rich-text">{!! $profile->biography_html !!}</div>
                    @elseif(!$profile->summary)
                        <p class="text-muted">The full profile of {{ $profile->display_name }} is coming soon.</p>
                    @endif

                    @if(count($profile->achievement_list))
                        <h2 class="detail-heading">Key achievements</h2>
                        <ul class="achievement-list">
                            @foreach($profile->achievement_list as $item)
                                <li><i class="ri-award-line"></i><span>{{ $item }}</span></li>
                            @endforeach
                        </ul>
                    @endif

                    @if($story)
                        <a href="{{ route('article', $story->slug) }}" class="profile-story-card">
                            <span class="profile-story-label">Read the full story</span>
                            <strong>{{ $story->title }}</strong>
                            <i class="ri-arrow-right-up-line"></i>
                        </a>
                    @endif
                </div>

            </article>

            @if($mentions->count())
                @include('partials.section-header', ['title' => 'In the news', 'bigText' => 'NEWS', 'class' => 'mt-4'])
                <div class="top-picks-grid related-posts-grid mb-2">
                    @foreach($mentions as $post)
                        @include('partials.cards.top-pick', ['post' => $post])
                    @endforeach
                </div>
            @endif

            @include('partials.ads.banner', ['placement' => 'in-content-ad'])

        </div>

        <div class="col-lg-3">
            @if($facts)
                <div class="facts-card">
                    <h3>Quick facts</h3>
                    <dl>
                        @foreach($facts as $label => $value)
                            <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            @endif

            @include('partials.sidebar-featured', ['posts' => $featured, 'title' => 'Featured'])
        </div>
    </div>

    @if($more->count())
        @include('partials.section-header', [
            'title' => 'More Profiles',
            'bigText' => 'PROFILES',
            'link' => route('profiles.index'),
            'class' => 'mt-4',
        ])
        <div class="profiles-grid mb-4">
            @foreach($more as $item)
                @include('partials.cards.profile', ['profile' => $item])
            @endforeach
        </div>
    @endif
</div>

@endsection
