@extends('layouts.front')

@section('title', $content->metaTitle())
@if($content->metaDescription())
    @section('description', $content->metaDescription())
@endif

@php
    use App\Support\SiteSettings as S;
    use Illuminate\Support\Str;
    $c = $content;
    $blocks = $c->enabled('content') ? $c->items('content.blocks') : [];
    $anchors = [];
    foreach ($blocks as $i => $block) {
        $base = Str::slug($block['title'] ?? ('section-' . ($i + 1))) ?: 'section-' . ($i + 1);
        $anchor = $base; $n = 2;
        while (in_array($anchor, $anchors)) { $anchor = $base . '-' . $n++; }
        $anchors[$i] = $anchor;
    }
    $showContact = $c->enabled('contact');
    $contactNumber = str_pad(count($blocks) + 1, 2, '0', STR_PAD_LEFT);
@endphp

@section('content')

@include('partials.breadcrumb', ['items' => [($c->page->title ?? $crumb) => null]])

<section class="legal-page-section">
    <div class="container-fluid px-4 px-lg-5">

        @if($c->enabled('header'))
            <div class="legal-page-header">
                <span class="legal-kicker">{{ $c->get('header.kicker') }}</span>
                <h1 class="legal-page-title">{{ $c->get('header.title') }}</h1>
                <p class="legal-page-intro">{{ $c->get('header.intro') }}</p>
                @if($c->page && $c->page->updated_at)
                    <p class="text-muted small mt-3 mb-0">Last updated: {{ $c->page->updated_at->format('F d, Y') }}</p>
                @endif
            </div>
        @endif

        <div class="legal-layout">

            <!-- SIDEBAR -->
            <aside class="legal-sidebar">
                <div class="legal-sidebar-inner">
                    <span class="legal-sidebar-label">{{ $c->get('header.sidebar_label', 'ON THIS PAGE') }}</span>
                    <nav class="legal-nav">
                        @foreach($blocks as $i => $block)
                            <a href="#{{ $anchors[$i] }}">{{ $block['title'] ?? '' }}</a>
                        @endforeach
                        @if($showContact)
                            <a href="#legal-contact">{{ $c->get('contact.title') }}</a>
                        @endif
                    </nav>
                </div>
            </aside>

            <!-- MAIN CONTENT -->
            <article class="legal-content">

                @foreach($blocks as $i => $block)
                    <section id="{{ $anchors[$i] }}" class="legal-block">
                        <span class="legal-number">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <h2>{{ $block['title'] ?? '' }}</h2>
                        {!! $block['body'] ?? '' !!}

                        @if(!empty($block['callout_title']) || !empty($block['callout_text']))
                            <div class="legal-callout">
                                <i class="ri-shield-check-line"></i>
                                <div>
                                    @if(!empty($block['callout_title']))<strong>{{ $block['callout_title'] }}</strong>@endif
                                    @if(!empty($block['callout_text']))<p>{{ $block['callout_text'] }}</p>@endif
                                </div>
                            </div>
                        @endif
                    </section>
                @endforeach

                @if($showContact)
                    <section id="legal-contact" class="legal-block legal-contact-block">
                        <span class="legal-number">{{ $contactNumber }}</span>
                        <h2>{{ $c->get('contact.title') }}</h2>
                        <p>{{ $c->get('contact.text') }}</p>

                        <div class="legal-contact-card">
                            @if(S::get('contact_email'))
                                <div class="legal-contact-item">
                                    <span>{{ $c->get('contact.general_label') }}</span>
                                    <a href="mailto:{{ S::get('contact_email') }}">{{ S::get('contact_email') }}</a>
                                </div>
                            @endif
                            @if(S::get('editorial_email'))
                                <div class="legal-contact-item">
                                    <span>{{ $c->get('contact.editorial_label') }}</span>
                                    <a href="mailto:{{ S::get('editorial_email') }}">{{ S::get('editorial_email') }}</a>
                                </div>
                            @endif
                            @if(S::get('contact_address'))
                                <div class="legal-contact-item">
                                    <span>{{ $c->get('contact.location_label') }}</span>
                                    <strong>{{ S::get('contact_address') }}</strong>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

            </article>

        </div>
    </div>
</section>

@endsection
