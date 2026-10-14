@extends('layouts.front')

{{-- /report/{slug} – one report (Admin → Content → Reports) --}}
@php
    use Illuminate\Support\Str;
    $site = \App\Support\SiteSettings::get('site_name', 'The Unicorn Magazine');
    $facts = array_filter([
        'Type' => $typeLabel,
        'Published' => $report->display_date->format('d M Y'),
        'By' => $report->author_name ?: 'Research Desk',
        'Format' => $report->file_info,
        'Downloads' => $report->download_count ? number_format($report->download_count) : null,
    ]);
@endphp

@section('title', $report->title . ' - ' . $site)
@section('description', Str::limit(strip_tags($report->description ?: $report->content), 160))
@section('og_image', $report->cover_url)

{{-- =========================================================
     CSS FOR THIS PAGE ONLY
     Loads after custom.css, so anything here wins without !important.
     Change the report detail page design from this block.
========================================================= --}}
@push('styles')
<style>
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

    /* ---- Report hero (cover on the left, details on the right) ---- */
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
    }
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

    /* ---- Headings inside the article body ---- */
    .detail-heading {
        margin: 8px 0 16px;
        font-family: "Merriweather", Georgia, serif;
        font-size: 22px;
        font-weight: 700;
        color: #111;
    }
    .post-body .detail-heading:not(:first-child) { margin-top: 34px; }

    /* ---- "Get the full report" box ---- */
    .report-download-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-top: 30px;
        padding: 20px 22px;
        border: 1px dashed #d8c39a;
        border-radius: 14px;
        background: #fbf8f2;
    }
    .report-download-box strong {
        display: block;
        font-family: "Merriweather", Georgia, serif;
        font-size: 17px;
    }
    .report-download-box span { color: #777; font-size: 13px; }

    /* ---- "Report details" box in the right column ---- */
    .facts-card {
        margin-bottom: 28px;
        padding: 20px;
        border: 1px solid #e5e5e5;
        border-radius: 14px;
        background: #fff;
    }
    .facts-card h3 {
        margin: 0 0 12px;
        font-family: "Merriweather", Georgia, serif;
        font-size: 17px;
        font-weight: 700;
    }
    .facts-card dl { margin: 0; }
    .facts-card dl > div {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 13px;
    }
    .facts-card dl > div:last-child { border-bottom: 0; }
    .facts-card dt { color: #777; font-weight: 500; }
    .facts-card dd { margin: 0; color: #111; font-weight: 600; text-align: right; }

    /* ---- Tablets ---- */
    @media (max-width: 1199.98px) {
        .report-hero { grid-template-columns: 240px 1fr; gap: 28px; }
    }

    /* ---- Phones ---- */
    @media (max-width: 767.98px) {
        .report-hero { grid-template-columns: 1fr; gap: 20px; padding: 18px; }
        .report-hero-cover { max-width: 220px; }
        .report-download-box { flex-direction: column; align-items: flex-start; }

        /* One report per row in the "More Reports" list */
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
    }

    @media (max-width: 359.98px) {
        .report-item { grid-template-columns: 1fr; }
        .report-image { height: 170px; }
    }
</style>
@endpush

@section('content')

@include('partials.breadcrumb', ['items' => ['Reports' => route('reports.index'), $report->title => null]])

<!-- =========================================================
     REPORT HERO
========================================================= -->
<section class="featured-stories">
    <div class="container-fluid px-4 px-lg-5">

        @if(session('success'))
            <div class="cms-alert cms-alert-success"><i class="ri-information-line"></i><span>{{ session('success') }}</span></div>
        @endif

        <div class="report-hero detail">
            <div class="report-hero-cover">
                <img src="{{ $report->cover_url }}" alt="{{ $report->title }}">
            </div>
            <div class="report-hero-body">
                <div class="report-meta mb-2">
                    <span class="report-category">{{ $report->type_label }}</span>
                    @if($report->is_exclusive)<span class="report-badge"><i class="ri-vip-crown-line"></i> Exclusive</span>@endif
                </div>
                <h1 class="report-hero-title">{{ $report->title }}</h1>
                @if($report->description)
                    <p class="report-hero-text">{{ $report->description }}</p>
                @endif
                <div class="featured-meta dark">
                    <span>By {{ $report->author_name ?: 'Research Desk' }}</span>
                    <span class="meta-dot"></span>
                    <span>{{ $report->display_date->format('M d, Y') }}</span>
                    @if($report->file_info)
                        <span class="meta-dot"></span>
                        <span>{{ $report->file_info }}</span>
                    @endif
                </div>
                <div class="d-flex flex-wrap gap-2 mt-auto pt-3">
                    @if($report->download_url)
                        <a href="{{ $report->download_url }}" class="um-btn" @if(!$report->file_path) target="_blank" rel="noopener" @endif>
                            <i class="{{ $report->file_path ? 'ri-download-line' : 'ri-external-link-line' }}"></i>
                            {{ $report->file_path ? 'Download report' : 'Open report' }}
                        </a>
                    @endif
                    <a href="{{ route('reports.index') }}" class="um-btn um-btn-outline">All reports</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================
     CONTENT + SIDEBAR
========================================================= -->
<div class="container-fluid px-4 px-lg-5">
    <div class="row g-5">
        <div class="col-lg-9">

            <article class="post-detail-content">
                @include('partials.share-bar', ['url' => $report->short_url, 'title' => $report->title])

                <div class="post-body">
                    @if(trim(strip_tags($report->content)))
                        <div class="rich-text">{!! $report->content !!}</div>
                    @elseif($report->description)
                        <h2 class="detail-heading">About this report</h2>
                        <p>{!! nl2br(e($report->description)) !!}</p>
                    @endif

                    @if($report->download_url)
                        <div class="report-download-box">
                            <div>
                                <strong>Get the full report</strong>
                                <span>{{ $report->file_info ?: 'Read the complete findings' }}</span>
                            </div>
                            <a href="{{ $report->download_url }}" class="um-btn" @if(!$report->file_path) target="_blank" rel="noopener" @endif>
                                <i class="{{ $report->file_path ? 'ri-download-line' : 'ri-external-link-line' }}"></i>
                                {{ $report->file_path ? 'Download' : 'Open' }}
                            </a>
                        </div>
                    @endif
                </div>
            </article>

            @include('partials.ads.banner', ['placement' => 'in-content-ad'])

        </div>

        <div class="col-lg-3">
            <div class="facts-card">
                <h3>Report details</h3>
                <dl>
                    @foreach($facts as $label => $value)
                        <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                    @endforeach
                </dl>
                @if($report->download_url)
                    <a href="{{ $report->download_url }}" class="um-btn w-100 justify-content-center mt-3" @if(!$report->file_path) target="_blank" rel="noopener" @endif>
                        <i class="ri-download-line"></i> {{ $report->file_path ? 'Download' : 'Open report' }}
                    </a>
                @endif
            </div>

            @include('partials.sidebar-featured', ['posts' => $featured, 'title' => 'Featured'])
        </div>
    </div>

    @if($more->count())
        @include('partials.section-header', [
            'title' => 'More Reports',
            'bigText' => 'REPORTS',
            'link' => route('reports.index'),
            'class' => 'mt-4',
        ])
        <div class="reports-list mb-4">
            @include('partials.cards.report-items', ['reports' => $more])
        </div>
    @endif
</div>

@endsection
