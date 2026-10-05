@extends('layouts.front')

@section('title', $content->page->meta_title ?? \App\Support\SiteSettings::get('default_meta_title', \App\Support\SiteSettings::get('site_name', 'The Unicorn Magazine')))
@if($content->metaDescription())
    @section('description', $content->metaDescription())
@endif

@php
    use Illuminate\Support\Str;
    $c = $content;
@endphp

@section('content')

{{-- =========================================================
     HEADLINE LINE LIMITS
     Each section has "Headline lines – large screens / mobile" in
     Admin → Pages → Home. Longer headlines end with "…" on screen only;
     the title saved in the database is never shortened. 0 = no limit.
========================================================= --}}
@php
    $headlineRules = [
        'latest' => '.featured-stories .featured-large-title, .featured-stories .featured-small-title',
        'funding' => '.funding-spotlight .funding-card-title',
        'business' => '.top-picks .top-pick-title',
        'unicorn' => '.unicorn-section .unicorn-main-title, .unicorn-section .unicorn-related-title',
        'startup' => '.startup-hustle .startup-title',
        'entrepreneurs' => '.minds-section .founder-content h4',
        'billionaires' => '.billionaires-section .billionaire-card h3',
        'profiles' => '.stories-profiles .profile-name',
        'reports' => '.reports-section .report-title',
    ];
    $lineValue = fn ($n) => ($n = max(0, min(10, (int) $n))) ? $n : 'unset';
@endphp
<style>
@foreach($headlineRules as $sectionKey => $selector)
    {!! $selector !!} {
        display: -webkit-box !important; -webkit-box-orient: vertical; overflow: hidden; white-space: normal !important;
        -webkit-line-clamp: {{ $lineValue($c->get($sectionKey . '.title_lines_desktop', 3)) }} !important;
        line-clamp: {{ $lineValue($c->get($sectionKey . '.title_lines_desktop', 3)) }} !important;
    }
@endforeach
    .featured-stories .featured-large-title, .featured-stories .featured-small-title { padding-bottom: .12em; }
    .reports-section .report-title a { display: inline !important; overflow: visible !important; }
    @media (max-width: 991.98px) {
@foreach($headlineRules as $sectionKey => $selector)
        {!! $selector !!} {
            -webkit-line-clamp: {{ $lineValue($c->get($sectionKey . '.title_lines_mobile', 3)) }} !important;
            line-clamp: {{ $lineValue($c->get($sectionKey . '.title_lines_mobile', 3)) }} !important;
        }
@endforeach
    }
</style>

<!-- HORIZONTAL AD -->
@include('partials.ads.banner', ['placement' => 'header-ad'])

{{-- =========================================================
     LATEST NEWS
========================================================= --}}
@if($c->enabled('latest') && $hero->count())
<section class="featured-stories">
    <div class="container-fluid px-4 px-lg-5">

        @include('partials.section-header', [
            'title' => $c->get('latest.title'),
            'bigText' => $c->get('latest.big_text'),
            'link' => $c->get('latest.link'),
            'line' => true,
        ])

        <div class="featured-grid">
            @foreach($hero as $post)
                @if($loop->first)
                    <article class="featured-card featured-card-large">
                        <a href="{{ $post->url }}" class="featured-card-image">
                            <img src="{{ $post->image_url }}" alt="{{ $post->title }}">
                            <span class="featured-image-overlay"></span>
                            <div class="featured-large-content">
                                <span class="story-category">{{ $post->badge ?: ($post->category->name ?? 'News') }}</span>
                                <h3 class="featured-large-title" title="{{ $post->title }}">{{ $post->title }}</h3>
                                @if($post->excerpt)
                                    <p class="featured-description">{{ Str::limit(strip_tags($post->excerpt), 160) }}</p>
                                @endif
                                <div class="featured-meta">
                                    <span>By {{ $post->author_name }}</span>
                                    <span class="meta-dot"></span>
                                    <span>{{ $post->display_date->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </a>
                    </article>
                @else
                    <article class="featured-card featured-card-small">
                        <a href="{{ $post->url }}" class="featured-card-image">
                            <img src="{{ $post->image_url }}" alt="{{ $post->title }}">
                            <span class="featured-image-overlay"></span>
                            <div class="featured-small-content">
                                <div class="featured-small-meta">
                                    <span class="story-category">{{ $post->badge ?: ($post->category->name ?? 'News') }}</span>
                                    <span class="story-date">{{ $post->display_date->format('M d, Y') }}</span>
                                </div>
                                <h3 class="featured-small-title" title="{{ $post->title }}">{{ $post->title }}</h3>
                            </div>
                        </a>
                    </article>
                @endif
            @endforeach
        </div>

        @if($strip->count())
            <div class="hero-bottom-posts">
                <div class="hero-posts-track">
                    @foreach($strip as $post)
                        <a href="{{ $post->url }}" class="hero-small-post">
                            <div class="hero-small-post-image">
                                <img src="{{ $post->thumb_url }}" alt="{{ $post->title }}" loading="lazy">
                            </div>
                            <h3>{{ $post->title }}</h3>
                            <div class="author-date mt-2">
                                <span>By {{ $post->author_name }}</span>
                                <span class="meta-dot"></span>
                                <span>{{ $post->display_date->format('M d') }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</section>
@endif

{{-- =========================================================
     FUNDING SPOTLIGHT
========================================================= --}}
@if($c->enabled('funding') && $funding->count())
<section class="funding-spotlight">
    <div class="container-fluid px-4 px-lg-5">

        @include('partials.section-header', [
            'title' => $c->get('funding.title'),
            'bigText' => $c->get('funding.big_text'),
            'link' => route('category', $c->get('funding.category')),
        ])

        <div class="funding-grid">
            @foreach($funding as $post)
                <article class="funding-card">
                    <a href="{{ $post->url }}" class="funding-card-link">
                        <div class="funding-image">
                            <img src="{{ $post->image_url }}" alt="{{ $post->title }}" loading="lazy">
                            <span class="funding-badge">{{ $post->badge ?: ($post->category->name ?? 'Funding') }}</span>
                        </div>
                        <h3 class="funding-card-title">{{ $post->title }}</h3>
                        <div class="author-date mt-1">
                            <span>By {{ $post->author_name }}</span>
                            <span class="meta-dot"></span>
                            <span>{{ $post->display_date->format('M d, Y') }}</span>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>

    </div>
</section>
@endif

{{-- =========================================================
     BUSINESS BRIEF
========================================================= --}}
@if($c->enabled('business') && $business->count())
<section class="top-picks">
    <div class="container-fluid px-4 px-lg-5">

        @include('partials.section-header', [
            'title' => $c->get('business.title'),
            'bigText' => $c->get('business.big_text'),
            'link' => route('category', $c->get('business.category')),
        ])

        <div class="top-picks-grid">
            @foreach($business as $post)
                @include('partials.cards.top-pick', ['post' => $post, 'showCategory' => true])
            @endforeach
        </div>

    </div>
</section>
@endif

{{-- =========================================================
     INDIA'S UNICORN
========================================================= --}}
@if($c->enabled('unicorn') && $unicorn->count())
@php $main = $unicorn->first(); $side = $unicorn->slice(1); @endphp
<section class="unicorn-section">
    <div class="container-fluid px-4 px-lg-5">

        @include('partials.section-header', [
            'title' => $c->get('unicorn.title'),
            'bigText' => $c->get('unicorn.big_text'),
            'link' => route('category', $c->get('unicorn.category')),
        ])

        <div class="unicorn-feature">

            <div class="unicorn-feature-image">
                <img src="{{ $main->image_url }}" alt="{{ $main->title }}">
                <div class="unicorn-image-overlay"></div>
                <span class="unicorn-badge">{{ $main->badge ?: ($main->category->name ?? 'Unicorn') }}</span>
                <a href="{{ $main->url }}" class="unicorn-image-arrow" aria-label="Read story">
                    <i class="ri-arrow-right-up-line"></i>
                </a>
            </div>

            <div class="unicorn-feature-content">
                <h3 class="unicorn-main-title">
                    <a href="{{ $main->url }}" class="text-reset text-decoration-none">{{ $main->title }}</a>
                </h3>
                <div class="unicorn-main-meta">
                    <span>By {{ $main->author_name }}</span>
                    <span>{{ $main->display_date->format('M d, Y') }}</span>
                </div>
                @if($main->excerpt)
                    <p class="unicorn-main-description">{{ Str::limit(strip_tags($main->excerpt), 220) }}</p>
                @endif
            </div>

            <div class="unicorn-related">
                @foreach($side as $post)
                    <a href="{{ $post->url }}" class="unicorn-related-post">
                        <div class="unicorn-thumb">
                            <img src="{{ $post->thumb_url }}" alt="{{ $post->title }}" loading="lazy">
                        </div>
                        <div class="unicorn-related-content">
                            <div class="unicorn-related-top">
                                <span class="unicorn-related-badge">{{ $post->badge ?: ($post->category->name ?? 'Unicorn') }}</span>
                                <span class="unicorn-related-date">{{ $post->display_date->format('M d') }}</span>
                            </div>
                            <h4 class="unicorn-related-title">{{ $post->title }}</h4>
                            <div class="unicorn-related-meta">
                                <span>By {{ $post->author_name }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

        </div>
    </div>
</section>
@endif

{{-- =========================================================
     STARTUP HUSTLE
========================================================= --}}
@if($c->enabled('startup') && $startup->count())
@php $icons = $c->items('startup.icons') ?: ['ri-rocket-2-line']; @endphp
<section class="startup-hustle">
    <div class="container-fluid px-4 px-lg-5">

        @include('partials.section-header', [
            'title' => $c->get('startup.title'),
            'bigText' => $c->get('startup.big_text'),
            'link' => route('category', $c->get('startup.category')),
        ])

        <div class="startup-grid">
            @foreach($startup as $post)
                <article class="startup-card">
                    <a href="{{ $post->url }}" class="startup-card-link">
                        <div class="startup-image-wrap">
                            <span class="startup-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="startup-image">
                                <img src="{{ $post->thumb_url }}" alt="{{ $post->title }}" loading="lazy">
                            </div>
                            <span class="startup-icon">
                                <i class="{{ $icons[$loop->index % count($icons)] }}"></i>
                            </span>
                        </div>
                        <div class="startup-content">
                            <span class="startup-label">{{ $post->badge ?: ($post->category->name ?? 'Startups') }}</span>
                            <h3 class="startup-title">{{ $post->title }}</h3>
                            <div class="startup-bottom">
                                <span class="startup-time">{{ $post->display_date->format('M d') }}</span>
                                <span class="startup-arrow"><i class="ri-arrow-right-line"></i></span>
                            </div>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>

    </div>
</section>
@endif

{{-- =========================================================
     ENTREPRENEURS
========================================================= --}}
@if($c->enabled('entrepreneurs'))
<section class="minds-section">
    <div class="container-fluid px-3 px-md-4 px-xl-5">
        <div class="minds-layout">

            <div class="minds-feature">

                <div class="minds-feature-image">
                    <img src="{{ $c->image('entrepreneurs.image', \App\Support\Media::placeholder()) }}" alt="{{ $c->get('entrepreneurs.quote_author', 'Entrepreneur') }}">
                </div>

                <div class="minds-content">
                    <div class="minds-eyebrow">
                        <span></span>
                        {{ $c->get('entrepreneurs.eyebrow') }}
                    </div>

                    <h2 class="minds-title">{!! $c->lines('entrepreneurs.title') !!}</h2>

                    <p class="minds-description">{{ $c->get('entrepreneurs.description') }}</p>

                    @if($c->get('entrepreneurs.cta_text'))
                        <a href="{{ \App\Support\Menus::url($c->get('entrepreneurs.cta_url')) }}" class="minds-cta">
                            {{ $c->get('entrepreneurs.cta_text') }}
                            <i class="ri-arrow-right-line"></i>
                        </a>
                    @endif
                </div>

                @if($c->get('entrepreneurs.quote_text'))
                    <div class="minds-quote">
                        <div class="quote-icon"><i class="ri-double-quotes-l"></i></div>
                        <p>{{ $c->get('entrepreneurs.quote_text') }}</p>
                        <div class="quote-line"></div>
                        <strong>{{ $c->get('entrepreneurs.quote_author') }}</strong>
                        <small>{{ $c->get('entrepreneurs.quote_role') }}</small>
                    </div>
                @endif

                @if(count($c->items('entrepreneurs.stats')))
                    <div class="minds-stats">
                        @foreach($c->items('entrepreneurs.stats') as $stat)
                            <div class="minds-stat">
                                <i class="{{ $stat['icon'] ?? 'ri-star-line' }}"></i>
                                <div>
                                    <strong>{{ $stat['number'] ?? '' }}</strong>
                                    <span>{!! nl2br(e($stat['label'] ?? ''), false) !!}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>

            <div class="founder-spotlight">

                <div class="founder-header">
                    <div class="founder-heading">
                        <h3 class="section-title fs-3">{{ $c->get('entrepreneurs.spotlight_title') }}</h3>
                    </div>
                    <a href="{{ route('category', $c->get('entrepreneurs.category')) }}" class="view-all">
                        View All
                        <i class="ri-arrow-right-line"></i>
                    </a>
                </div>

                @forelse($founders as $post)
                    @php
                        $badge = $post->badge ?: ($post->category->name ?? 'Story');
                        $badgeClass = Str::contains(Str::lower($badge), 'interview') ? 'interview'
                            : (Str::contains(Str::lower($badge), 'lead') ? 'leadership' : 'success');
                    @endphp
                    <article class="founder-card">
                        <div class="founder-image">
                            <a href="{{ $post->url }}"><img src="{{ $post->thumb_url }}" alt="{{ $post->title }}" loading="lazy"></a>
                        </div>
                        <div class="founder-content">
                            <div class="founder-meta">
                                <span class="founder-badge {{ $badgeClass }}">{{ Str::upper($badge) }}</span>
                                <span class="founder-date">{{ $post->display_date->format('M d, Y') }}</span>
                            </div>
                            <h4><a href="{{ $post->url }}" class="text-reset text-decoration-none">{{ $post->title }}</a></h4>
                            <div class="founder-bottom">
                                <span>By {{ $post->author_name }}</span>
                                <a href="{{ $post->url }}" class="round-arrow" aria-label="Read story">
                                    <i class="ri-arrow-right-line"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="text-muted small">Add posts to the selected category to fill this spotlight.</p>
                @endforelse

            </div>

        </div>
    </div>
</section>
@endif

{{-- =========================================================
     BILLIONAIRES
========================================================= --}}
@if($c->enabled('billionaires') && $billionaires->count())
<section class="billionaires-section">
    <div class="container-fluid px-4 px-lg-5">

        @include('partials.section-header', [
            'title' => $c->get('billionaires.title'),
            'bigText' => $c->get('billionaires.big_text'),
            'link' => route('category', $c->get('billionaires.category')),
        ])

        <div class="billionaires-cards">
            @foreach($billionaires as $post)
                <a href="{{ $post->url }}" class="billionaire-card">
                    <div class="billionaire-card-image">
                        <img src="{{ $post->thumb_url }}" alt="{{ $post->title }}" loading="lazy">
                        <span class="billionaire-image-badge">{{ $post->badge ?: ($post->category->name ?? 'Billionaires') }}</span>
                    </div>
                    <h3>{{ $post->title }}</h3>
                    <div class="author-date mt-1">
                        <span>By {{ $post->author_name }}</span>
                        <span class="meta-dot"></span>
                        <span>{{ $post->display_date->format('M d') }}</span>
                    </div>
                </a>
            @endforeach
        </div>

    </div>
</section>
@endif

{{-- =========================================================
     STORIES & PROFILES
========================================================= --}}
@if($c->enabled('profiles') && $profiles->count())
<section class="stories-profiles">
    <div class="container-fluid px-4 px-lg-5">

        @include('partials.section-header', [
            'title' => $c->get('profiles.title'),
            'bigText' => $c->get('profiles.big_text'),
            'link' => $c->get('profiles.link'),
        ])

        <div class="profiles-grid">
            @foreach($profiles as $profile)
                @include('partials.cards.profile', ['profile' => $profile])
            @endforeach
        </div>

    </div>
</section>
@endif

<!-- HORIZONTAL AD -->
@include('partials.ads.banner', ['placement' => 'in-content-ad'])

{{-- =========================================================
     REPORTS
========================================================= --}}
@if($c->enabled('reports'))
<section class="reports-section">
    <div class="container-fluid px-4 px-lg-5">

        @include('partials.section-header', [
            'title' => $c->get('reports.title'),
            'bigText' => $c->get('reports.big_text'),
            'link' => route('reports.index'),
        ])

        <div class="row g-4 align-items-start">

            <div class="col-lg-8">
                <div class="reports-list">
                    @forelse($reports as $report)
                        @include('partials.cards.report', ['report' => $report])
                    @empty
                        <p class="cms-empty">Reports will appear here once they are published.</p>
                    @endforelse

                    <div class="reports-explore">
                        <a href="{{ route('reports.index') }}" class="reports-explore-btn">
                            {{ $c->get('reports.explore_text') }}
                            <i class="ri-arrow-right-line"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <aside class="reports-sidebar">

                    <div class="reports-sidebar-header">
                        <h3>{{ $c->get('reports.highlights_title') }}</h3>
                        <span></span>
                    </div>

                    @foreach($c->items('reports.highlights') as $item)
                        <a href="{{ \App\Support\Menus::url($item['url'] ?? '#') }}" class="report-highlight">
                            <div class="highlight-icon"><i class="{{ $item['icon'] ?? 'ri-bar-chart-line' }}"></i></div>
                            <div class="highlight-content">
                                <h4>{{ $item['title'] ?? '' }}</h4>
                                <p>{{ $item['text'] ?? '' }}</p>
                            </div>
                        </a>
                    @endforeach

                    <div class="exclusive-report">
                        <div class="exclusive-content">
                            <span class="exclusive-label">{{ $c->get('reports.exclusive_label') }}</span>
                            <h3>{!! $c->lines('reports.exclusive_title') !!}</h3>
                            <div class="exclusive-line"></div>
                            <p>{{ $c->get('reports.exclusive_text') }}</p>
                            <a href="{{ $exclusiveReport ? $exclusiveReport->link : route('reports.index') }}" class="download-report">
                                {{ $c->get('reports.exclusive_button') }}
                                <i class="ri-download-line"></i>
                            </a>
                        </div>
                        <div class="report-cover">
                            <div class="cover-inner">
                                <span>{{ $c->get('reports.cover_top') }}</span>
                                <strong>{!! $c->lines('reports.cover_main') !!}</strong>
                                <b>{{ $c->get('reports.cover_sub') }}</b>
                                <em>{{ $c->get('reports.cover_year') }}</em>
                            </div>
                        </div>
                    </div>

                    @include('partials.ads.square', ['placement' => 'sidebar-ad'])

                </aside>
            </div>

        </div>
    </div>
</section>
@endif

@endsection
