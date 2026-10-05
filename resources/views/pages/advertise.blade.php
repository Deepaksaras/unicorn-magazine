@extends('layouts.front')

@section('title', $content->metaTitle())
@if($content->metaDescription())
    @section('description', $content->metaDescription())
@endif

@php
    $c = $content;

    // Seniority pie: build the conic-gradient from the saved percentages
    $pieColors = ['#c6a65b', '#68758b', '#a9a9a9'];
    $legendClasses = ['legend-founder', 'legend-manager', 'legend-entry'];
    $slices = array_slice($c->items('audience.seniority'), 0, 3);
    $total = max(1, array_sum(array_map(fn ($s) => (float) ($s['percent'] ?? 0), $slices)));
    $deg = 0; $stops = [];
    foreach ($slices as $i => $s) {
        $end = $deg + ((float) ($s['percent'] ?? 0) / $total) * 360;
        $stops[] = $pieColors[$i] . ' ' . round($deg, 1) . 'deg ' . round($end, 1) . 'deg';
        $deg = $end;
    }
    $bar = fn ($v) => max(0, min(100, (float) $v));
@endphp

@section('content')

@include('partials.breadcrumb', ['items' => [($c->page->title ?? 'Advertise with Us') => null]])

@if($c->enabled('hero'))
<!-- ADVERTISE HERO -->
<section class="advertise-hero">
    <div class="container-fluid px-4 px-lg-5">
        <div class="advertise-hero-image">
            <img src="{{ $c->image('hero.image', \App\Support\Media::placeholder()) }}" alt="Advertising and business partnership">
            <div class="advertise-hero-content">
                <h1>{!! $c->lines('hero.heading') !!}</h1>
            </div>
        </div>
    </div>
</section>
@endif

@if($c->enabled('intro'))
<!-- ADVERTISE INTRO -->
<section class="advertise-intro">
    <div class="container-fluid px-4 px-lg-5">
        <div class="advertise-intro-grid">
            <div class="advertise-intro-label">
                <span class="advertise-line"></span>
                {{ $c->get('intro.label') }}
            </div>
            <div class="advertise-intro-content">
                <h2>{!! $c->lines('intro.heading') !!}</h2>
                <p class="advertise-lead">{{ $c->get('intro.lead') }}</p>
                <p>{{ $c->get('intro.body') }}</p>
            </div>
        </div>
    </div>
</section>
@endif

@if($c->enabled('stats') && count($c->items('stats.items')))
<!-- ADVERTISING IMPACT -->
<section class="advertise-stats">
    <div class="container-fluid px-4 px-lg-5">
        <div class="advertise-stats-grid">
            @foreach($c->items('stats.items') as $stat)
                <div class="advertise-stat">
                    <span class="advertise-stat-number">{{ $stat['number'] ?? '' }}</span>
                    <span class="advertise-stat-label">{{ $stat['label'] ?? '' }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($c->enabled('audience'))
<!-- AUDIENCE INSIGHTS -->
<section class="audience-insights-section">
    <div class="container-fluid px-4 px-lg-5">

        <div class="audience-insights-header">
            <h2 class="audience-insights-title">{{ $c->get('audience.title') }}</h2>
        </div>

        <div class="audience-insights-grid">

            <!-- SENIORITY -->
            <div class="audience-insight-card">
                <div class="audience-card-head"><div><h3>{{ $c->get('audience.seniority_title') }}</h3></div></div>
                <div class="seniority-chart-wrap">
                    <div class="seniority-chart">
                        <div class="seniority-pie" @if($stops) style="background: conic-gradient({{ implode(', ', $stops) }});" @endif></div>
                        <div class="seniority-pie-center">
                            <strong>100%</strong>
                            <span>Audience</span>
                        </div>
                    </div>
                    <div class="seniority-legend">
                        @foreach($slices as $i => $slice)
                            <div class="seniority-legend-item">
                                <span class="legend-color {{ $legendClasses[$i] }}"></span>
                                <div class="legend-content">
                                    <span>{{ $slice['label'] ?? '' }}</span>
                                    <strong>{{ $slice['percent'] ?? 0 }}%</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @foreach(['company', 'age'] as $chart)
                <div class="audience-insight-card">
                    <div class="audience-card-head"><div><h3>{{ $c->get("audience.{$chart}_title") }}</h3></div></div>
                    <div class="audience-stat-list">
                        @foreach($c->items("audience.$chart") as $row)
                            <div class="audience-stat-row">
                                <div class="audience-stat-top">
                                    <span>{{ $row['label'] ?? '' }}</span>
                                    <strong>{{ $row['percent'] ?? 0 }}%</strong>
                                </div>
                                <div class="audience-progress">
                                    <span style="width:{{ $bar($row['percent'] ?? 0) }}%;"></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</section>
@endif

@if($c->enabled('options') && count($c->items('options.items')))
<!-- ADVERTISING OPTIONS -->
<section class="advertise-options">
    <div class="container-fluid px-4 px-lg-5">

        <div class="section-header">
            <h2 class="section-title">{{ $c->get('options.title') }}</h2>
            <a href="#advertiseForm" class="view-all">
                {{ $c->get('options.button') }}
                <i class="ri-arrow-right-line"></i>
            </a>
        </div>

        <div class="advertise-options-grid">
            @foreach($c->items('options.items') as $option)
                <a href="#advertiseForm" class="advertise-option">
                    <div class="option-icon"><i class="{{ $option['icon'] ?? 'ri-megaphone-line' }}"></i></div>
                    <h3>{{ $option['title'] ?? '' }}</h3>
                    <p>{{ $option['text'] ?? '' }}</p>
                    <span class="option-arrow"><i class="ri-arrow-right-up-line"></i></span>
                </a>
            @endforeach
        </div>

    </div>
</section>
@endif

@if($c->enabled('brands') && count($c->items('brands.logos')))
<!-- BRANDS -->
<section class="brands-section">
    <div class="container-fluid px-4 px-lg-5">

        <div class="brands-header">
            <h2 class="brands-title">{{ $c->get('brands.title') }}</h2>
            <p class="brands-description">{{ $c->get('brands.description') }}</p>
        </div>

        <div class="brands-grid">
            @foreach($c->items('brands.logos') as $logo)
                @if(!empty($logo['image']))
                    <div class="brand-logo-card">
                        <img src="{{ \App\Support\Media::url($logo['image']) }}" alt="{{ $logo['name'] ?? 'Brand' }}" loading="lazy">
                    </div>
                @endif
            @endforeach
        </div>

    </div>
</section>
@endif

@if($c->enabled('form'))
<!-- ADVERTISE FORM -->
<section class="advertise-form-section" id="advertiseForm">
    <div class="container-fluid px-4 px-lg-5">
        <div class="advertise-form-wrapper">

            <div class="advertise-form-intro">
                <span class="advertise-kicker">{{ $c->get('form.kicker') }}</span>
                <h2>{!! $c->lines('form.heading') !!}</h2>
                <p>{{ $c->get('form.text') }}</p>
                <div class="form-contact-list">
                    @if($c->get('form.email'))
                        <div>
                            <i class="ri-mail-line"></i>
                            <span><a href="mailto:{{ $c->get('form.email') }}" class="text-reset text-decoration-none">{{ $c->get('form.email') }}</a></span>
                        </div>
                    @endif
                    @if($c->get('form.response_note'))
                        <div>
                            <i class="ri-time-line"></i>
                            <span>{{ $c->get('form.response_note') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="advertise-form-box">

                @if(session('success'))
                    <div class="cms-alert cms-alert-success"><i class="ri-checkbox-circle-line"></i><span>{{ session('success') }}</span></div>
                @endif
                @if($errors->any())
                    <div class="cms-alert cms-alert-error"><i class="ri-error-warning-line"></i><span>{{ $errors->first() }}</span></div>
                @endif

                <form id="advertiseEnquiryForm" method="POST" action="{{ route('advertise.send') }}" novalidate>
                    @csrf

                    <div class="form-row">
                        <div class="form-group">
                            <label for="advertiseName">Full Name *</label>
                            <input type="text" id="advertiseName" name="name" value="{{ old('name') }}" placeholder="Your full name" required>
                        </div>
                        <div class="form-group">
                            <label for="advertiseCompany">Company *</label>
                            <input type="text" id="advertiseCompany" name="company" value="{{ old('company') }}" placeholder="Company name" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="advertiseEmail">Work Email *</label>
                            <input type="email" id="advertiseEmail" name="email" value="{{ old('email') }}" placeholder="name@company.com" required>
                        </div>
                        <div class="form-group">
                            <label for="advertisePhone">Phone</label>
                            <input type="tel" id="advertisePhone" name="phone" value="{{ old('phone') }}" placeholder="+91 00000 00000">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="advertiseIndustry">Industry *</label>
                            <select id="advertiseIndustry" name="industry" required>
                                <option value="" disabled {{ old('industry') ? '' : 'selected' }}>Select industry</option>
                                @foreach($c->items('form.industries') as $option)
                                    <option {{ old('industry') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="advertiseBudget">Estimated Budget</label>
                            <select id="advertiseBudget" name="budget">
                                <option value="" {{ old('budget') ? '' : 'selected' }} disabled>Select budget</option>
                                @foreach($c->items('form.budgets') as $option)
                                    <option {{ old('budget') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Advertising Interest *</label>
                        <div class="advertise-checkboxes">
                            @foreach($c->items('form.interests') as $option)
                                <label>
                                    <input type="checkbox" name="interest[]" value="{{ $option }}" {{ in_array($option, (array) old('interest', [])) ? 'checked' : '' }}>
                                    <span>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="advertiseMessage">Tell us about your campaign *</label>
                        <textarea id="advertiseMessage" name="message" rows="5" placeholder="Tell us about your goals, audience, campaign or requirements..." required>{{ old('message') }}</textarea>
                    </div>

                    <div class="advertise-form-bottom">
                        <label class="form-consent">
                            <input type="checkbox" name="consent" value="1" {{ old('consent') ? 'checked' : '' }} required>
                            <span>{{ $c->get('form.consent') }}</span>
                        </label>
                        <button type="submit">
                            {{ $c->get('form.button') }}
                            <i class="ri-arrow-right-line"></i>
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</section>
@endif

@endsection
