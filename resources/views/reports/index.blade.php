@extends('layouts.front')

{{-- /reports – all reports (Admin → Content → Reports) --}}
@section('title', 'Reports - ' . \App\Support\SiteSettings::get('site_name', 'The Unicorn Magazine'))
@section('description', 'Research, market studies and industry reports from The Unicorn Magazine.')

@section('content')

@include('partials.breadcrumb', ['items' => ['Reports' => null]])

<!-- =========================================================
     HEADING + LEAD REPORT
========================================================= -->
<section class="featured-stories">
    <div class="container-fluid px-4 px-lg-5">

        @include('partials.section-header', ['title' => 'Reports', 'bigText' => 'REPORTS'])

        <p class="text-muted mb-4" style="max-width: 760px;">Research, market studies and industry outlooks on India's startups, business and economy.</p>

        @include('partials.type-chips', ['types' => $types, 'active' => $type, 'route' => 'reports.index', 'all' => 'All reports'])

        @if(session('success'))
            <div class="cms-alert cms-alert-success"><i class="ri-information-line"></i><span>{{ session('success') }}</span></div>
        @endif

        @if($hero)
            <div class="row g-5">
                <div class="col-lg-9">
                    <a href="{{ $hero->link }}" class="report-hero">
                        <div class="report-hero-cover">
                            <img src="{{ $hero->cover_url }}" alt="{{ $hero->title }}">
                        </div>
                        <div class="report-hero-body">
                            <div class="report-meta mb-2">
                                <span class="report-category">{{ $hero->type_label }}</span>
                                @if($hero->is_exclusive)<span class="report-badge"><i class="ri-vip-crown-line"></i> Exclusive</span>@endif
                            </div>
                            <h2 class="report-hero-title">{{ $hero->title }}</h2>
                            @if($hero->description)
                                <p class="report-hero-text">{{ \Illuminate\Support\Str::limit(strip_tags($hero->description), 240) }}</p>
                            @endif
                            <div class="featured-meta dark">
                                <span>By {{ $hero->author_name ?: 'Research Desk' }}</span>
                                <span class="meta-dot"></span>
                                <span>{{ $hero->display_date->format('M d, Y') }}</span>
                            </div>
                            <span class="um-btn mt-auto">View report <i class="ri-arrow-right-line"></i></span>
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
     REPORT LIST + FEATURED
========================================================= -->
<section class="reports-section pt-0">
    <div class="container-fluid px-4 px-lg-5">
        <div class="row g-5 align-items-start">
            <div class="col-lg-9">

                @include('partials.ads.banner', ['placement' => 'in-content-ad'])

                @if($reports->count())
                    <div class="reports-list" id="reportList">
                        @include('partials.cards.report-items', ['reports' => $reports])
                    </div>

                    @if($reports->hasMorePages())
                        <div class="reports-explore my-4">
                            <a href="{{ $reports->nextPageUrl() }}" class="reports-explore-btn" id="loadMore">
                                Load More <i class="ri-arrow-down-line"></i>
                            </a>
                        </div>
                    @endif
                @elseif(!$hero)
                    <div class="cms-empty">No reports have been published yet.</div>
                @endif

            </div>

            <div class="col-lg-3">
                @include('partials.sidebar-featured', ['posts' => $featured, 'title' => 'Featured'])
            </div>
        </div>
    </div>
</section>

@endsection

@include('partials.load-more-script', ['grid' => 'reportList'])
