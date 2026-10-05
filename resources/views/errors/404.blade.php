@extends('layouts.front')

@php
    use App\Support\Menus;
    use App\Support\PageContent;
    use App\Support\PostFeed;

    // Text comes from Admin → Website → Pages → "404 – Page Not Found".
    // If that page isn't set up yet, the defaults below are used.
    $c = null;
    try {
        $c = PageContent::blueprint('not-found') ? PageContent::for('not-found') : null;
    } catch (\Throwable $e) {
        $c = null;
    }
    $t = fn (string $path, $default = '') => $c ? $c->get($path, $default) : $default;
    $on = fn (string $section) => $c ? $c->enabled($section) : true;

    $storyCount = max(0, (int) $t('stories.count', 4));
    $stories = collect();
    if ($on('stories') && $storyCount) {
        try {
            $stories = PostFeed::base()->take($storyCount)->get();
        } catch (\Throwable $e) {
            $stories = collect();
        }
    }
@endphp

@php
    // SEO: never let a missing page be indexed; title from Admin → Pages → Page Not Found → SEO box
    \App\Support\Seo::model($c ? $c->page : null, ['title' => 'Page Not Found']);
    \App\Support\Seo::set(['robots' => 'noindex, follow', 'canonical' => url()->current()]);
@endphp

@push('styles')
<style>
    .nf-section { padding: 70px 0 50px; background: #fff; }
    .nf-wrap { position: relative; max-width: 860px; margin: 0 auto; text-align: center; }
    .nf-big {
        font-family: "Merriweather", Georgia, serif; font-weight: 900;
        font-size: clamp(120px, 24vw, 260px); line-height: .9; letter-spacing: -6px;
        color: transparent; -webkit-text-stroke: 1.5px var(--border, #e7e7e7);
        user-select: none; margin-bottom: -40px;
    }
    .nf-kicker {
        display: inline-flex; align-items: center; gap: 8px;
        font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
        color: var(--black, #0a0a0a); background: #f4f4f4; border-radius: 50px; padding: 7px 14px;
    }
    .nf-kicker .dot { width: 7px; height: 7px; border-radius: 50%; background: #e63946; }
    .nf-title {
        position: relative; font-family: "Merriweather", Georgia, serif; font-weight: 900;
        font-size: clamp(30px, 4.4vw, 52px); line-height: 1.15; color: var(--black, #0a0a0a);
        margin: 18px 0 16px;
    }
    .nf-text { font-size: 17px; line-height: 1.7; color: var(--muted, #777); max-width: 620px; margin: 0 auto 30px; }
    .nf-actions { display: flex; justify-content: center; flex-wrap: wrap; gap: 12px; }
    .nf-btn {
        display: inline-flex; align-items: center; gap: 8px; padding: 13px 26px; border-radius: 50px;
        font-size: 15px; font-weight: 600; text-decoration: none; transition: all .25s ease;
        border: 1px solid var(--black, #0a0a0a);
    }
    .nf-btn-dark { background: var(--black, #0a0a0a); color: #fff; }
    .nf-btn-dark:hover { background: #333; border-color: #333; color: #fff; transform: translateY(-2px); }
    .nf-btn-light { background: #fff; color: var(--black, #0a0a0a); }
    .nf-btn-light:hover { background: var(--black, #0a0a0a); color: #fff; transform: translateY(-2px); }
    .nf-search { max-width: 560px; margin: 40px auto 0; position: relative; }
    .nf-search label { display: block; font-size: 13px; font-weight: 600; color: var(--text, #181818); margin-bottom: 10px; }
    .nf-search-box { display: flex; align-items: center; border: 1px solid var(--border, #e7e7e7); border-radius: 50px; padding: 6px 6px 6px 22px; background: #fff; transition: border-color .2s, box-shadow .2s; }
    .nf-search-box:focus-within { border-color: var(--black, #0a0a0a); box-shadow: 0 6px 24px rgba(0,0,0,.06); }
    .nf-search-box i { color: var(--muted, #777); font-size: 18px; }
    .nf-search-box input { flex: 1; border: 0; outline: 0; padding: 10px 12px; font-size: 15px; background: transparent; min-width: 0; }
    .nf-search-box button { border: 0; background: var(--black, #0a0a0a); color: #fff; border-radius: 50px; padding: 10px 22px; font-weight: 600; font-size: 14px; }
    .nf-search-box button:hover { background: #333; }
    .nf-stories { padding: 30px 0 60px; border-top: 1px solid var(--border, #e7e7e7); }
    .nf-stories .top-picks-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 30px; }
    @media (max-width: 991px) { .nf-stories .top-picks-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 575px) {
        .nf-section { padding: 45px 0 35px; }
        .nf-big { margin-bottom: -22px; letter-spacing: -3px; }
        .nf-stories .top-picks-grid { grid-template-columns: 1fr; }
        .nf-btn { width: 100%; justify-content: center; }
    }
</style>
@endpush

@section('content')

<section class="nf-section">
    <div class="container-fluid px-4 px-lg-5">
        <div class="nf-wrap">

            @if($on('hero'))
                <div class="nf-big" aria-hidden="true">{{ $t('hero.big_text', '404') }}</div>

                <span class="nf-kicker"><span class="dot"></span>{{ $t('hero.kicker', 'Error 404') }}</span>

                <h1 class="nf-title">{{ $t('hero.title', 'This story has gone off the record.') }}</h1>

                <p class="nf-text">{{ $t('hero.text', "The page you're looking for may have been moved, renamed or never existed. Let's get you back to the stories that matter.") }}</p>

                <div class="nf-actions">
                    @if($t('hero.primary_text', 'Back to home'))
                        <a href="{{ Menus::url($t('hero.primary_link', '/')) }}" class="nf-btn nf-btn-dark">
                            <i class="ri-home-5-line"></i> {{ $t('hero.primary_text', 'Back to home') }}
                        </a>
                    @endif
                    @if($t('hero.secondary_text', 'Read latest news'))
                        <a href="{{ Menus::url($t('hero.secondary_link', '/latest')) }}" class="nf-btn nf-btn-light">
                            {{ $t('hero.secondary_text', 'Read latest news') }} <i class="ri-arrow-right-line"></i>
                        </a>
                    @endif
                </div>
            @endif

            @if($on('search'))
                <form class="nf-search" action="{{ route('search') }}" method="GET" role="search">
                    <label for="nfSearch">{{ $t('search.label', 'Or search the magazine') }}</label>
                    <div class="nf-search-box">
                        <i class="ri-search-line"></i>
                        <input type="search" id="nfSearch" name="q" placeholder="{{ $t('search.placeholder', 'Search stories, founders, companies…') }}" required>
                        <button type="submit">{{ $t('search.button', 'Search') }}</button>
                    </div>
                </form>
            @endif

        </div>
    </div>
</section>

@if($stories->count())
<section class="nf-stories">
    <div class="container-fluid px-4 px-lg-5">
        @include('partials.section-header', [
            'title' => $t('stories.title', 'Worth reading instead'),
            'bigText' => $t('stories.big_text', 'READ'),
            'link' => '/latest',
        ])

        <div class="top-picks-grid">
            @foreach($stories as $post)
                @include('partials.cards.top-pick', ['post' => $post, 'showCategory' => true])
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection
