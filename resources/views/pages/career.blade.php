@extends('layouts.front')

@section('title', $content->metaTitle())
@if($content->metaDescription())
    @section('description', $content->metaDescription())
@endif

@php $c = $content; @endphp

@section('content')

@include('partials.breadcrumb', ['items' => [($c->page->title ?? 'Career') => null]])

@if($c->enabled('hero'))
<!-- CAREERS HERO -->
<section class="career-hero">
    <div class="container-fluid px-4 px-lg-5">
        <div class="career-hero-grid">

            <div class="career-hero-content">
                <span class="career-kicker">{{ $c->get('hero.kicker') }}</span>
                <h1>{!! $c->lines('hero.heading') !!}</h1>
                <p>{{ $c->get('hero.text') }}</p>
                @if($c->get('hero.button'))
                    <a href="#openRoles" class="career-primary-btn">
                        {{ $c->get('hero.button') }}
                        <i class="ri-arrow-right-line"></i>
                    </a>
                @endif
            </div>

            <div class="career-hero-image">
                <img src="{{ $c->image('hero.image', \App\Support\Media::placeholder()) }}" alt="{{ $c->get('hero.caption', 'Our team') }}">
                @if($c->get('hero.caption'))
                    <div class="career-image-caption">
                        <span>{{ $c->get('hero.caption') }}</span>
                    </div>
                @endif
            </div>

        </div>
    </div>
</section>
@endif

@if($c->enabled('perks') && count($c->items('perks.items')))
<!-- PERKS & BENEFITS -->
<section class="career-perks">
    <div class="container-fluid px-4 px-lg-5">

        <div class="section-header career-section-header justify-content-center">
            <h2 class="section-title">{{ $c->get('perks.title') }}</h2>
        </div>

        <div class="career-perks-grid">
            @foreach($c->items('perks.items') as $perk)
                <div class="career-perk-card">
                    <div class="career-perk-icon">
                        <i class="{{ $perk['icon'] ?? 'ri-star-line' }}"></i>
                    </div>
                    <h3>{{ $perk['title'] ?? '' }}</h3>
                    <p>{{ $perk['text'] ?? '' }}</p>
                </div>
            @endforeach
        </div>

    </div>
</section>
@endif

@if($c->enabled('openings'))
<!-- CURRENT OPENINGS -->
<section class="career-openings" id="openRoles">
    <div class="container-fluid px-4 px-lg-5">

        <div class="section-header">
            <div>
                <h2 class="section-title">{{ $c->get('openings.title') }}</h2>
            </div>
        </div>

        @if($jobs->count())
            <div class="career-accordion" id="careerAccordion">
                @foreach($jobs as $job)
                    @php
                        $open = $loop->first;
                        $applyUrl = $job->apply_url
                            ?: 'mailto:' . $c->get('openings.apply_email', \App\Support\SiteSettings::get('contact_email', ''))
                               . '?subject=' . rawurlencode('Application: ' . $job->title);
                    @endphp
                    <div class="career-job-item">

                        <button class="career-job-header {{ $open ? '' : 'collapsed' }}" type="button"
                                data-bs-toggle="collapse" data-bs-target="#job{{ $job->id }}"
                                aria-expanded="{{ $open ? 'true' : 'false' }}" aria-controls="job{{ $job->id }}">
                            <div class="career-job-title">
                                <span class="career-job-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <div>
                                    <h3>{{ $job->title }}</h3>
                                    <span>{{ $job->meta_line }}</span>
                                </div>
                            </div>
                            <span class="career-job-toggle">
                                <i class="ri-add-line"></i>
                            </span>
                        </button>

                        <div id="job{{ $job->id }}" class="collapse {{ $open ? 'show' : '' }}" data-bs-parent="#careerAccordion">
                            <div class="career-job-content">

                                @if($job->summary)
                                    <p>{{ $job->summary }}</p>
                                @endif

                                <div class="career-job-details">
                                    @if(count($job->responsibilities ?? []))
                                        <div>
                                            <strong>{{ $c->get('openings.duties_label') }}</strong>
                                            <ul>
                                                @foreach($job->responsibilities as $line)
                                                    <li>{{ $line }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    @if(count($job->requirements ?? []))
                                        <div>
                                            <strong>{{ $c->get('openings.requirements_label') }}</strong>
                                            <ul>
                                                @foreach($job->requirements as $line)
                                                    <li>{{ $line }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>

                                <a href="{{ $applyUrl }}" class="career-apply-btn" @if($job->apply_url) target="_blank" rel="noopener" @endif>
                                    {{ $c->get('openings.apply_text') }}
                                    <i class="ri-arrow-right-line"></i>
                                </a>

                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <p class="cms-empty">{{ $c->get('openings.empty_text') }}</p>
        @endif

    </div>
</section>
@endif

@if($c->enabled('voices') && count($c->items('voices.items')))
<!-- TEAM VOICES -->
<section class="career-voices">
    <div class="container-fluid px-4 px-lg-5">

        <div class="section-header">
            <div>
                <h2 class="section-title">{{ $c->get('voices.title') }}</h2>
            </div>
        </div>

        <div class="career-voices-grid">
            @foreach($c->items('voices.items') as $voice)
                @php
                    $initials = collect(preg_split('/\s+/', trim($voice['name'] ?? '')))
                        ->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
                @endphp
                <article class="career-voice-card">
                    <div class="voice-quote"><i class="ri-double-quotes-l"></i></div>
                    <p>{{ $voice['quote'] ?? '' }}</p>
                    <div class="voice-person">
                        <div class="voice-avatar">{{ $initials }}</div>
                        <div>
                            <strong>{{ $voice['name'] ?? '' }}</strong>
                            <span>{{ $voice['role'] ?? '' }}</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

    </div>
</section>
@endif

@endsection
