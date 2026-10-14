@extends('layouts.front')

{{-- /reports – all reports (Admin → Content → Reports) --}}
@section('title', 'Reports - ' . \App\Support\SiteSettings::get('site_name', 'The Unicorn Magazine'))
@section('description', 'Research, market studies and industry reports from The Unicorn Magazine.')

{{-- =========================================================
     CSS FOR THIS PAGE ONLY
     Loads after custom.css, so anything here wins without !important.
     Change the reports listing design from this block.
========================================================= --}}
@push('styles')
<style>
    /* ---- Type filter chips (All reports / Research / Market study …) ---- */
    .type-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0 0 26px;
    }
    .type-chips a {
        display: inline-flex;
        align-items: center;
        height: 36px;
        padding: 0 16px;
        border: 1px solid #e5e5e5;
        border-radius: 50px;
        color: #333;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all .25s ease;
    }
    .type-chips a:hover { border-color: #111; color: #111; }
    .type-chips a.active { background: #111; border-color: #111; color: #fff; }

    /* ---- Buttons ---- */
    .um-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 44px;
        padding: 0 20px;
        border: 1px solid #111;
        border-radius: 7px;
        background: #111;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        align-self: flex-start;
        transition: background .3s ease, color .3s ease, transform .3s ease;
    }
    .um-btn:hover { background: #333; border-color: #333; color: #fff; }
    .um-btn i { font-size: 16px; }
    .um-btn-outline { background: #fff; color: #111; }
    .um-btn-outline:hover { background: #111; color: #fff; }

    /* ---- Lead report card ---- */
    .report-hero {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 40px;
        align-items: stretch;
        padding: 28px;
        border: 1px solid #e5e5e5;
        border-radius: 16px;
        background: linear-gradient(135deg, #fbf8f2, #fff 60%);
        color: #111;
        text-decoration: none;
        transition: box-shadow .3s ease, border-color .3s ease;
    }
    a.report-hero:hover {
        color: #111;
        border-color: #d2d2d2;
        box-shadow: 0 16px 40px rgba(0, 0, 0, .07);
    }
    .report-hero-cover {
        position: relative;
        aspect-ratio: 3 / 4;
        border-radius: 10px;
        overflow: hidden;
        background: #e9e9e9;
        box-shadow: 0 18px 40px rgba(0, 0, 0, .18);
    }
    .report-hero-cover img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .6s ease;
    }
    a.report-hero:hover .report-hero-cover img { transform: scale(1.03); }
    .report-hero-body {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        min-width: 0;
        padding: 8px 0;
    }
    .report-hero-title {
        margin: 6px 0 14px;
        font-family: "Merriweather", Georgia, serif;
        font-size: clamp(26px, 2.6vw, 38px);
        font-weight: 700;
        line-height: 1.18;
        color: #111;
    }
    .report-hero-text {
        margin: 0 0 18px;
        max-width: 640px;
        color: #555;
        font-size: 15px;
        line-height: 1.7;
    }
    .report-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 50px;
        background: #111;
        color: #d7b36d;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .4px;
    }
    .featured-meta.dark { margin-bottom: 18px; color: #666; }
    .featured-meta.dark .meta-dot { background: #bbb; }

    /* ---- Tablets ---- */
    @media (max-width: 1199.98px) {
        .report-hero { grid-template-columns: 240px 1fr; gap: 28px; }
    }

    /* ---- Phones ---- */
    @media (max-width: 767.98px) {
        .report-hero { grid-template-columns: 1fr; gap: 20px; padding: 18px; }
        .report-hero-cover { max-width: 220px; }

        /* Chips scroll sideways instead of wrapping into three rows */
        .type-chips {
            flex-wrap: nowrap;
            overflow-x: auto;
            margin-right: -24px;
            padding: 0 24px 4px 0;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }
        .type-chips::-webkit-scrollbar { display: none; }
        .type-chips a { flex: 0 0 auto; }

        /* One report per row */
        .reports-list { display: block; padding: 14px; }
        .report-item {
            grid-template-columns: 38% 1fr;
            gap: 14px;
            align-items: stretch;
            padding: 0 0 16px;
            margin: 0 0 16px;
        }
        .report-item:last-of-type { margin-bottom: 0; }
        .report-image { height: 100%; min-height: 128px; border-radius: 10px; }
        .report-content { padding: 0; }
        .report-meta { margin-bottom: 8px; gap: 8px; flex-wrap: wrap; }
        .report-category { padding: 6px 10px; font-size: 10px; }
        .report-date { font-size: 11px; }
        .report-title { margin-bottom: 6px; font-size: 16px; line-height: 1.35; }
        .report-title a { display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 3; line-clamp: 3; overflow: hidden; }
        .report-description {
            font-size: 13px; line-height: 1.5;
            display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; line-clamp: 2; overflow: hidden;
        }
        .report-bottom { padding-top: 10px; }
        .report-reading { font-size: 12px; }
        .report-reading i { display: none; }
        .report-arrow { width: 32px; height: 32px; flex-basis: 32px; }
        .report-arrow i { font-size: 17px; }
        .reports-explore { padding-top: 4px; }
        .reports-explore-btn { gap: 14px; min-height: 44px; padding: 0 18px; font-size: 13px; }
    }

    @media (max-width: 359.98px) {
        .report-item { grid-template-columns: 1fr; }
        .report-image { height: 170px; }
    }
</style>
@endpush

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
