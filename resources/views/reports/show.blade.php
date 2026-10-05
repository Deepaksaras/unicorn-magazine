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
