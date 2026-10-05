@extends('layouts.front')

@section('title', $content->metaTitle())
@if($content->metaDescription())
    @section('description', $content->metaDescription())
@endif

@php $c = $content; @endphp

@section('content')

@include('partials.breadcrumb', ['items' => [($c->page->title ?? 'About Us') => null]])

@if($c->enabled('hero'))
<!-- ABOUT HERO -->
<section class="about-hero">
    <div class="container-fluid px-4 px-lg-5">
        <div class="about-hero-image">
            <img src="{{ $c->image('hero.image', \App\Support\Media::placeholder()) }}" alt="{{ $c->get('hero.overlay', 'About us') }}">
            @if($c->get('hero.overlay'))
                <div class="about-hero-overlay">
                    <span>{{ $c->get('hero.overlay') }}</span>
                </div>
            @endif
        </div>
    </div>
</section>
@endif

@if($c->enabled('intro'))
<!-- ABOUT INTRO -->
<section class="about-intro">
    <div class="container-fluid px-4 px-lg-5">
        <div class="about-intro-grid">
            <div class="about-intro-label">
                <span class="about-line"></span>
                <span>{{ $c->get('intro.label') }}</span>
            </div>
            <div class="about-intro-content">
                <h1>{!! $c->lines('intro.heading') !!}</h1>
                @if($c->get('intro.lead'))
                    <p class="about-lead">{{ $c->get('intro.lead') }}</p>
                @endif
                {!! $c->get('intro.body') !!}
            </div>
        </div>
    </div>
</section>
@endif

@if($c->enabled('mission'))
<!-- MISSION & VISION -->
<section class="mission-vision">
    <div class="container-fluid px-4 px-lg-5">

        <div class="section-header about-section-header">
            <h2 class="section-title">{{ $c->get('mission.title') }}</h2>
        </div>

        <div class="mission-vision-grid">
            @foreach(['mission' => '', 'vision' => ' vision-card'] as $key => $extra)
                <div class="mission-card{{ $extra }}">
                    <div class="mission-card-image">
                        <img src="{{ $c->image("mission.{$key}_image", \App\Support\Media::placeholder()) }}" alt="{{ $c->get("mission.{$key}_label") }}">
                    </div>
                    <div class="mission-card-content">
                        <span class="mission-label">{{ $c->get("mission.{$key}_label") }}</span>
                        <h3>{!! $c->lines("mission.{$key}_heading") !!}</h3>
                        <p>{{ $c->get("mission.{$key}_text") }}</p>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</section>
@endif

@if($c->enabled('team') && $team->count())
<!-- OUR TEAM -->
<section class="about-team">
    <div class="container-fluid px-4 px-lg-5">

        <div class="section-header">
            <h2 class="section-title">{{ $c->get('team.title') }}</h2>
        </div>

        <div class="team-grid">
            @foreach($team as $member)
                <div class="team-card">
                    <div class="team-image">
                        <img src="{{ $member->photo_url }}" alt="{{ $member->name }}" loading="lazy">
                        @if($member->social('linkedin') || $member->social('x') || $member->social('instagram'))
                            <div class="team-social">
                                @if($member->social('linkedin'))
                                    <a href="{{ $member->social('linkedin') }}" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="ri-linkedin-fill"></i></a>
                                @endif
                                @if($member->social('x'))
                                    <a href="{{ $member->social('x') }}" target="_blank" rel="noopener" aria-label="X"><i class="ri-twitter-x-fill"></i></a>
                                @endif
                                @if($member->social('instagram'))
                                    <a href="{{ $member->social('instagram') }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="ri-instagram-line"></i></a>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="team-info">
                        <h3>{{ $member->name }}</h3>
                        <span>{{ $member->role_title }}</span>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</section>
@endif

@if($c->enabled('subscribe'))
<!-- SUBSCRIBE CTA -->
<section class="about-subscribe">
    <div class="container-fluid px-4 px-lg-5">
        <div class="subscribe-box">

            <div class="subscribe-content">
                <span class="subscribe-kicker">{{ $c->get('subscribe.kicker') }}</span>
                <h2>{!! $c->lines('subscribe.heading') !!}</h2>
                <p>{{ $c->get('subscribe.text') }}</p>
            </div>

            <form class="about-subscribe-form js-subscribe-form" action="{{ route('subscribe') }}" method="POST">
                @csrf
                <input type="hidden" name="source" value="about-page">
                <div class="subscribe-input-wrap">
                    <input type="email" name="email" placeholder="Your email address" required>
                    <button type="submit">
                        Subscribe
                        <i class="ri-arrow-right-line"></i>
                    </button>
                </div>
                <small>{{ $c->get('subscribe.note') }}</small>
                <div class="cms-form-note js-form-note"></div>
            </form>

        </div>
    </div>
</section>
@endif

@endsection
