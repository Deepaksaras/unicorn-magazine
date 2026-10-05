@php
    use App\Support\Menus;
    use App\Support\Media;
    use App\Support\SiteSettings as S;

    $siteName = S::get('site_name', 'The Unicorn Magazine');
    $logo = Media::url(S::get('site_logo'), asset('img/logo.svg'));
    $favicon = Media::url(S::get('site_favicon'), asset('img/favicon.svg'));
    $headerMenu = Menus::items('header-menu');

    // Header menu limit (Admin → Settings → Header & Footer): the first N links show
    // in the bar, the rest move behind the "More" button (side panel).
    $menuLimit = max(0, (int) S::get('header_menu_limit', 6));
    $menuVisible = $menuLimit > 0 ? $headerMenu->take($menuLimit) : $headerMenu;
    $menuMore = $menuLimit > 0 ? $headerMenu->slice($menuLimit)->values() : collect();
    $moreActive = $menuMore->contains(fn ($item) => Menus::isActive($item->url));
    // Newsletter: is this visitor subscribed? + subscribe pop-up settings (Admin → Website → Subscribe Pop-up)
    $subState = \App\Support\Subscription::state();          // yes | no | unknown
    $subPopup = \App\Support\Subscription::settings();
    $googleSub = \App\Support\Subscription::providerReady('google') ? route('subscribe.oauth', 'google') : S::get('google_login_url');
    $linkedinSub = \App\Support\Subscription::providerReady('linkedin') ? route('subscribe.oauth', 'linkedin') : S::get('linkedin_login_url');

    $socials = [
        ['linkedin_url', 'LinkedIn', 'ri-linkedin-fill'],
        ['facebook_url', 'Facebook', 'ri-facebook-fill'],
        ['instagram_url', 'Instagram', 'ri-instagram-line'],
        ['twitter_url', 'X', 'ri-twitter-x-line'],
        ['youtube_url', 'YouTube', 'ri-youtube-line'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO: title, description, keywords, canonical, Open Graph, Twitter card.
         Values come from the admin (SEO box on each item / System → SEO) with automatic fallbacks. --}}
    {!! \App\Support\Seo::render([
        'title' => $__env->yieldContent('title'),
        'description' => $__env->yieldContent('description'),
        'image' => $__env->yieldContent('og_image'),
    ]) !!}

    <!-- favicon icon -->
    <link rel="shortcut icon" href="{{ $favicon }}">

    <!-- BOOTSTRAP -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- REMIX ICON -->
    <link href="{{ asset('css/remixicon.css') }}" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wdth,wght@75..100,400;75..100,500;75..100,600;75..100,700&family=Merriweather:opsz,wght@18..144,400;18..144,700;18..144,900&display=swap" rel="stylesheet">

    {{-- ?v= changes whenever the file changes, so browsers never show an old cached copy --}}
    <link href="{{ asset('css/custom.css') }}?v={{ @filemtime(public_path('css/custom.css')) ?: 1 }}" rel="stylesheet">

    <style>
        .cms-alert { border-radius: 14px; padding: 14px 18px; margin-bottom: 20px; font-size: 14px; display: flex; gap: 10px; align-items: flex-start; }
        .cms-alert i { font-size: 18px; line-height: 1.2; }
        .cms-alert-success { background: #eef8f1; color: #1d6b3a; border: 1px solid #cbe9d5; }
        .cms-alert-error { background: #fdf0f0; color: #9b2c2c; border: 1px solid #f3cccc; }
        .cms-form-note { font-size: 13px; margin-top: 10px; min-height: 18px; }
        .cms-form-note.ok { color: #1d6b3a; }
        .cms-form-note.err { color: #c0392b; }
        .um-email-msg { color: #c0392b; font-size: 12.5px; line-height: 1.4; margin-top: 6px; min-height: 0; }
        .um-email-msg:empty { display: none; }
        input.um-email-bad { border-color: #c0392b !important; }
        .cms-empty { padding: 48px 0; text-align: center; color: var(--muted, #777); }
        .cms-pagination { display: flex; justify-content: center; }
        .cms-ad-code { width: 100%; text-align: center; overflow: hidden; }
        .cms-ad-image img { width: 100%; height: auto; display: block; border-radius: inherit; }
        .subscribe-btn[aria-disabled="true"] { cursor: default; opacity: .85; }
        .subscribe-btn i { font-size: 15px; vertical-align: -2px; }
        .um-flash { position: fixed; left: 50%; bottom: 28px; transform: translate(-50%, 16px); display: flex; align-items: center; gap: 10px; max-width: calc(100vw - 32px); padding: 13px 20px; border-radius: 50px; background: #111; color: #fff; font-size: 14px; font-weight: 500; box-shadow: 0 14px 40px rgba(0, 0, 0, .3); opacity: 0; pointer-events: none; transition: opacity .3s ease, transform .3s ease; z-index: 2100; }
        .um-flash.show { opacity: 1; transform: translate(-50%, 0); }
        .um-flash i { font-size: 20px; color: #7ddc9a; }
        .um-flash.err i { color: #ff9c9c; }
        .main-menu > li.menu-more > a { gap: 6px; cursor: pointer; }
        .main-menu > li.menu-more > a i { font-size: 16px; line-height: 1; }
        .canvas-more { margin-bottom: 0; }
        .canvas-more .canvas-links { margin-top: 0; }
        .canvas-links a.active { font-weight: 700; color: #000; }
    </style>

    @stack('styles')

    @if(S::get('google_analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ S::get('google_analytics_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ S::get('google_analytics_id') }}');
        </script>
    @endif

    {!! S::get('header_code') !!}
</head>


<body>

<!-- =========================================================
     IMPORTANT NEWS BAR
========================================================= -->
@php $breaking = \App\Models\BreakingNews::where('status', 1)->orderBy('position')->first(); @endphp
@if($breaking)
<div class="top-news-bar">
    <div class="container-fluid px-4 px-lg-5">
        <div class="top-news-inner">
            <div class="top-news-label">
                <span class="top-news-dot"></span>
                {{ S::get('breaking_label', 'IMPORTANT') }}
            </div>
            <a href="{{ Menus::url($breaking->url) }}" class="top-news-link">
                <span>{{ $breaking->title }}</span>
                <i class="ri-arrow-right-up-line"></i>
            </a>
        </div>
    </div>
</div>
@endif

<!-- =========================================================
     NAVBAR
========================================================= -->
<header class="main-navbar">

    <div class="navbar-top">
        <div class="container-fluid px-4 px-lg-5">
            <div class="navbar-top-inner">

                <!-- LEFT : OFFCANVAS BUTTON -->
                <div class="navbar-left">
                    <button type="button" class="menu-toggle" data-bs-toggle="offcanvas" data-bs-target="#mainOffcanvas" aria-controls="mainOffcanvas" aria-label="Open menu">
                        <i class="ri-menu-3-line"></i>
                    </button>
                </div>

                <!-- CENTER : LOGO -->
                <a href="{{ route('home') }}" class="site-logo gsap-logo" aria-label="{{ $siteName }}">
                    <img src="{{ $logo }}" alt="{{ $siteName }}">
                </a>

                <!-- RIGHT : SEARCH + SUBSCRIBE -->
                <div class="navbar-actions">
                    <button type="button" class="search-btn" id="openSearch" aria-label="Open search">
                        <i class="ri-search-line"></i>
                    </button>
                    <a href="#" class="subscribe-btn" data-bs-toggle="modal" data-bs-target="#subscribeModal">
                        {{ S::get('subscribe_button_text', 'Subscribe') }}
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- SECOND ROW : MAIN MENU -->
    <div class="navbar-menu">
        <div class="container-fluid px-4 px-lg-5">
            <nav class="main-navigation">
                <ul class="main-menu">
                    @foreach($menuVisible as $item)
                        <li class="{{ Menus::isActive($item->url) ? 'active' : '' }} {{ $item->css_class }}">
                            <a href="{{ Menus::url($item->url) }}" @if($item->target === '_blank') target="_blank" rel="noopener" @endif>
                                {{ $item->title }}
                            </a>
                        </li>
                    @endforeach

                    {{-- MORE: opens its own side panel with only the remaining header links (separate from the toggle menu) --}}
                    @if($menuMore->count())
                        <li class="menu-more {{ $moreActive ? 'active' : '' }}">
                            <a href="#moreOffcanvas" data-bs-toggle="offcanvas" data-bs-target="#moreOffcanvas" aria-controls="moreOffcanvas" aria-label="More menu links">
                                {{ S::get('header_more_text', 'More') }}
                                <i class="ri-menu-3-line"></i>
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    </div>

</header>

@yield('content')

<!-- =========================================================
     FOOTER
========================================================= -->
<footer class="site-footer">
    <div class="container-fluid px-4 px-lg-5">

        <div class="footer-top">
            <div class="row g-5">

                <!-- BRAND -->
                <div class="col-lg-4 col-md-6">
                    <a href="{{ route('home') }}" class="footer-logo" aria-label="{{ $siteName }}">
                        <img src="{{ $logo }}" alt="{{ $siteName }}">
                    </a>

                    <p class="footer-description">
                        {{ S::get('footer_description', "The Unicorn Magazine brings you thoughtful stories, business insights, startup news, technology, culture and the people shaping India's future.") }}
                    </p>

                    <div class="footer-social">
                        @foreach($socials as [$key, $label, $icon])
                            @if(S::get($key))
                                <a href="{{ S::get($key) }}" target="_blank" rel="noopener" aria-label="{{ $label }}">
                                    <i class="{{ $icon }}"></i>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- STORIES -->
                <div class="col-lg-2 col-6">
                    <h6 class="footer-heading">{{ Menus::name('footer-stories', 'Stories') }}</h6>
                    <ul class="footer-links">
                        @foreach(Menus::items('footer-stories') as $item)
                            <li><a href="{{ Menus::url($item->url) }}" @if($item->target === '_blank') target="_blank" rel="noopener" @endif>{{ $item->title }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- COMPANY -->
                <div class="col-lg-2 col-6">
                    <h6 class="footer-heading">{{ Menus::name('footer-company', 'Company') }}</h6>
                    <ul class="footer-links">
                        @foreach(Menus::items('footer-company') as $item)
                            <li><a href="{{ Menus::url($item->url) }}" @if($item->target === '_blank') target="_blank" rel="noopener" @endif>{{ $item->title }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- NEWSLETTER -->
                <div class="col-lg-4">
                    <h6 class="footer-heading">{{ S::get('newsletter_title', 'Stay Updated') }}</h6>

                    <p class="footer-newsletter-text">
                        {{ S::get('newsletter_text', 'Get the latest stories and insights delivered to your inbox.') }}
                    </p>

                    <form class="footer-newsletter js-subscribe-form" action="{{ route('subscribe') }}" method="POST">
                        @csrf
                        <input type="hidden" name="source" value="footer">
                        <div class="footer-input-wrap">
                            <input type="email" name="email" placeholder="Your email address" aria-label="Email address" required>
                            <button type="submit" aria-label="Subscribe">
                                <i class="ri-arrow-right-line"></i>
                            </button>
                        </div>
                        <div class="cms-form-note js-form-note"></div>
                    </form>
                </div>

            </div>
        </div>

        <!-- FOOTER FEATURE -->
        <div class="footer-feature">
            <div class="footer-feature-content">
                <span>{{ S::get('footer_feature_kicker', 'THE UNICORN MAGAZINE') }}</span>
                <h3>{{ S::get('footer_feature_title', 'Ideas that shape tomorrow.') }}</h3>
            </div>
            <a href="{{ Menus::url(S::get('footer_feature_url', '/about')) }}" class="footer-feature-arrow" aria-label="Explore {{ $siteName }}">
                <i class="ri-arrow-right-up-line"></i>
            </a>
        </div>

        <!-- FOOTER BOTTOM -->
        <div class="footer-bottom">
            <div class="footer-copyright">
                {{ str_replace('{year}', date('Y'), S::get('copyright_text', '© {year} The Unicorn Magazine. All rights reserved.')) }}
            </div>

            <div class="footer-legal">
                @foreach(Menus::items('footer-legal') as $item)
                    <a href="{{ Menus::url($item->url) }}">{{ $item->title }}</a>
                @endforeach
            </div>

            <button type="button" class="back-to-top" id="backToTop" aria-label="Back to top">
                <i class="ri-arrow-up-line"></i>
            </button>
        </div>

    </div>
</footer>


<!-- =========================================================
     SEARCH OVERLAY
========================================================= -->
<div class="search-overlay" id="searchOverlay">

    <button type="button" class="search-close" id="closeSearch" aria-label="Close search">
        <i class="ri-close-line"></i>
    </button>

    <div class="search-box">
        <span class="search-label">{{ S::get('search_label', 'Search the publication') }}</span>

        <form class="search-input-wrapper" id="searchForm" action="{{ route('search') }}" method="GET">
            <input type="search" class="search-input" id="searchInput" name="q" placeholder="What are you looking for?" autocomplete="off" value="{{ request('q') }}">
            <button type="submit" class="search-submit" aria-label="Search">
                <i class="ri-arrow-right-line"></i>
            </button>
        </form>
    </div>

</div>

<!-- =========================================================
     SUBSCRIBE MODAL
========================================================= -->
<div class="modal fade subscribe-modal" id="subscribeModal" tabindex="-1" aria-labelledby="subscribeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <button type="button" class="subscribe-close" data-bs-dismiss="modal" aria-label="Close">
                <i class="ri-close-line"></i>
            </button>

            <div class="subscribe-modal-body">

                <div class="subscribe-header">
                    <span class="subscribe-kicker">{{ S::get('subscribe_modal_kicker', 'THE UNICORN MAGAZINE') }}</span>
                    <h2 id="subscribeModalLabel">{{ S::get('subscribe_modal_title', 'Stay ahead of the curve.') }}</h2>
                    <p>{{ S::get('subscribe_modal_text', 'Get the latest stories, startup insights, business news and ideas delivered to your inbox.') }}</p>
                </div>

                <form id="subscribeForm" class="subscribe-form js-subscribe-form" action="{{ route('subscribe') }}" method="POST">
                    @csrf
                    <input type="hidden" name="source" value="modal">

                    <div class="subscribe-field">
                        <label for="subscriberEmail">Email address</label>
                        <input type="email" id="subscriberEmail" name="email" placeholder="you@example.com" autocomplete="email" required>
                    </div>

                    <div class="subscribe-field">
                        <label for="subscriberMobile">
                            Mobile number
                            <span>Optional</span>
                        </label>
                        <input type="tel" id="subscriberMobile" name="mobile" placeholder="+91 98765 43210" autocomplete="tel">
                    </div>

                    <button type="submit" class="subscribe-submit">
                        Subscribe to {{ $siteName }}
                        <i class="ri-arrow-right-line"></i>
                    </button>

                    <div class="cms-form-note js-form-note"></div>
                </form>

                @if($googleSub || $linkedinSub)
                    <div class="subscribe-divider">
                        <span>or continue with</span>
                    </div>

                    <div class="subscribe-social">
                        @if($googleSub)
                            <a href="{{ $googleSub }}" class="social-login-btn google-login" id="googleSignIn" rel="nofollow">
                                <span class="social-icon"><i class="ri-google-fill"></i></span>
                                <span>Continue with Google</span>
                            </a>
                        @endif
                        @if($linkedinSub)
                            <a href="{{ $linkedinSub }}" class="social-login-btn linkedin-login" id="linkedinSignIn" rel="nofollow">
                                <span class="social-icon"><i class="ri-linkedin-fill"></i></span>
                                <span>Continue with LinkedIn</span>
                            </a>
                        @endif
                    </div>
                @endif

                <div class="subscribe-terms">
                    By subscribing, you agree to our
                    <a href="{{ route('terms') }}">Terms</a>
                    and
                    <a href="{{ route('privacy') }}">Privacy Policy</a>.
                </div>

            </div>
        </div>
    </div>
</div>

<!-- =========================================================
     RIGHT OFFCANVAS
========================================================= -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="mainOffcanvas" aria-labelledby="mainOffcanvasLabel">

    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mainOffcanvasLabel">{{ S::get('offcanvas_heading', 'Explore') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        {{-- Toggle menu: only its own links (Admin → Menus → Offcanvas menu) + the small About text --}}
        <div class="canvas-content">
            <h6>{{ S::get('offcanvas_title', $siteName) }}</h6>
            <p>{{ S::get('offcanvas_text', 'The Unicorn Magazine is an independent editorial platform focused on the people, companies and ideas shaping the next generation of business and innovation.') }}</p>

            <ul class="canvas-links">
                @foreach(Menus::items('offcanvas-menu') as $item)
                    <li>
                        <a href="{{ Menus::url($item->url) }}" @if($item->target === '_blank') target="_blank" rel="noopener" @endif>
                            {{ $item->title }}
                            <i class="ri-arrow-right-up-line"></i>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

</div>


{{-- =========================================================
     "MORE" PANEL – only the header menu links that come after the limit.
     Independent from the toggle menu above.
========================================================= --}}
@if($menuMore->count())
<div class="offcanvas offcanvas-end" tabindex="-1" id="moreOffcanvas" aria-labelledby="moreOffcanvasLabel">

    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="moreOffcanvasLabel">{{ S::get('header_more_text', 'More') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        <div class="canvas-content canvas-more">
            <ul class="canvas-links">
                @foreach($menuMore as $item)
                    <li>
                        <a href="{{ Menus::url($item->url) }}" class="{{ Menus::isActive($item->url) ? 'active' : '' }}" @if($item->target === '_blank') target="_blank" rel="noopener" @endif>
                            {{ $item->title }}
                            <i class="ri-arrow-right-line"></i>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

</div>
@endif


<!-- =========================================================
     SCRIPTS
========================================================= -->
<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('js/gsap.min.js') }}"></script>
<script src="{{ asset('js/main.js') }}?v={{ @filemtime(public_path('js/main.js')) ?: 1 }}"></script>

<script>
/* E-mail check while the visitor types (all public forms). The server checks again with App\Rules\RealEmail. */
(function () {
    var FORMAT = /^[a-z0-9](?:[a-z0-9._%+\-]{0,62}[a-z0-9_])?@(?:[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?\.)+[a-z]{2,24}$/;
    var TYPOS = {
        'gmial.com': 'gmail.com', 'gmai.com': 'gmail.com', 'gamil.com': 'gmail.com', 'gnail.com': 'gmail.com', 'gmail.co': 'gmail.com',
        'gmail.con': 'gmail.com', 'gmail.cm': 'gmail.com', 'gmaill.com': 'gmail.com', 'gmail.comm': 'gmail.com', 'gmal.com': 'gmail.com',
        'gmail.om': 'gmail.com', 'gmail.in': 'gmail.com', 'yaho.com': 'yahoo.com', 'yahooo.com': 'yahoo.com', 'yahoo.con': 'yahoo.com',
        'yhoo.com': 'yahoo.com', 'hotmial.com': 'hotmail.com', 'hotmal.com': 'hotmail.com', 'hotmail.con': 'hotmail.com', 'hotmil.com': 'hotmail.com',
        'outlok.com': 'outlook.com', 'outloook.com': 'outlook.com', 'outlook.con': 'outlook.com', 'rediffmail.con': 'rediffmail.com',
        'redifmail.com': 'rediffmail.com', 'icloud.con': 'icloud.com'
    };

    function problem(value) {
        var email = String(value || '').trim().toLowerCase();
        if (!email) return 'Please enter your e-mail address.';
        if (!FORMAT.test(email) || email.indexOf('..') !== -1) return 'Please enter a valid e-mail address, for example name@example.com.';
        var at = email.lastIndexOf('@'), domain = email.slice(at + 1);
        if (TYPOS[domain]) return 'Did you mean ' + email.slice(0, at + 1) + TYPOS[domain] + '?';
        return '';
    }

    function show(input, message) {
        var form = input.form;
        var note = form && form.querySelector('.js-form-note');
        if (!note) {
            note = input.parentNode.querySelector('.um-email-msg');
            if (!note) {
                note = document.createElement('div');
                note.className = 'um-email-msg';
                note.setAttribute('role', 'alert');
                input.parentNode.appendChild(note);
            }
            note.textContent = message;
        } else if (message || note.classList.contains('err')) {
            note.textContent = message;
            note.className = 'cms-form-note js-form-note' + (message ? ' err' : '');
        }
        input.classList.toggle('um-email-bad', !!message);
        input.setAttribute('aria-invalid', message ? 'true' : 'false');
    }

    function check(input, whenEmpty) {
        if (!input.value.trim() && !whenEmpty && !input.required) { show(input, ''); return true; }
        if (!input.value.trim() && !whenEmpty) { show(input, ''); return true; }
        var message = problem(input.value);
        show(input, message);
        return !message;
    }

    document.addEventListener('blur', function (e) {
        if (e.target.matches && e.target.matches('input[type="email"]')) check(e.target, false);
    }, true);

    document.addEventListener('input', function (e) {
        // once an error is showing, clear it as soon as the address becomes correct
        if (e.target.matches && e.target.matches('input[type="email"].um-email-bad')) check(e.target, false);
    }, true);

    // runs before the form's own submit code
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.querySelectorAll || form.closest('.offcanvas-search, [role="search"]')) return;
        var bad = null;
        form.querySelectorAll('input[type="email"]').forEach(function (input) {
            if ((input.required || input.value.trim()) && !check(input, true) && !bad) bad = input;
        });
        if (bad) {
            e.preventDefault();
            e.stopPropagation();
            bad.focus();
        }
    }, true);
})();
</script>

<script>
/* Newsletter forms (footer, modal, about page): submit without leaving the page */
document.addEventListener('DOMContentLoaded', function () {
    /* ---------- Newsletter forms (pop-up, footer, About page) ---------- */
    document.querySelectorAll('.js-subscribe-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var note = form.querySelector('.js-form-note');
            var button = form.querySelector('[type="submit"]');
            if (button) button.disabled = true;

            fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                var msg = res.data.message || (res.data.errors ? Object.values(res.data.errors)[0][0] : 'Something went wrong.');
                if (note) { note.textContent = msg; note.className = 'cms-form-note js-form-note ' + (res.ok ? 'ok' : 'err'); }
                if (res.ok) {
                    form.reset();
                    if (window.UMSubscribe) window.UMSubscribe.markSubscribed(form.closest('#subscribeModal') ? 1800 : 0);
                }
            })
            .catch(function () {
                if (note) { note.textContent = 'Could not subscribe right now. Please try again.'; note.className = 'cms-form-note js-form-note err'; }
            })
            .finally(function () { if (button) button.disabled = false; });
        });
    });
});
</script>

{{-- =========================================================
     POP-UP MANAGER – one pop-up at a time
     The ad pop-up and the subscribe pop-up both ask this manager before opening,
     so they can never sit on top of each other. The ad pop-up goes first.
========================================================= --}}
<script>
window.UMPopups = (function () {
    var active = null, queue = [], expected = {}, waiters = [];
    function flush() {
        // anything waiting for a pop-up that is no longer expected/active?
        waiters = waiters.filter(function (w) {
            if (expected[w.name] || active === w.name) return true;
            clearTimeout(w.timer); w.cb(); return false;
        });
        if (!active && queue.length) { var next = queue.shift(); active = next.name; delete expected[next.name]; next.open(); }
    }
    return {
        /** "I will probably open on this page" (the ad pop-up says this while its timer runs). */
        expect: function (name) { expected[name] = true; },
        /** Open now if nothing else is open, otherwise wait in line. */
        request: function (name, open) {
            if (!active) { active = name; delete expected[name]; open(); }
            else { queue.push({ name: name, open: open }); }
        },
        /** Call when a pop-up has closed. The next one waits a moment before opening. */
        done: function (name) {
            if (active === name) active = null;
            delete expected[name];
            setTimeout(flush, 1500);
        },
        /** Run cb when pop-up `name` is finished (or was never coming). maxWait = don't wait forever for one that never opens. */
        after: function (name, cb, maxWait) {
            if (!expected[name] && active !== name) { cb(); return; }
            var w = { name: name, cb: cb, timer: null };
            if (maxWait) {
                w.timer = setTimeout(function () {
                    if (active === name) return;             // it is open right now → keep waiting for done()
                    waiters = waiters.filter(function (x) { return x !== w; });
                    cb();
                }, maxWait);
            }
            waiters.push(w);
        },
        isOpen: function (name) { return name ? active === name : !!active; }
    };
})();
</script>

{{-- =========================================================
     SUBSCRIBE POP-UP – who is subscribed + when to show it
     Settings: Admin → Website → Subscribe Pop-up
========================================================= --}}
<script>
(function () {
    var cfg = {
        state: @json($subState),                                   // yes | no | unknown (from the server)
        enabled: @json($subPopup['subpop_enabled'] === '1'),
        delay: {{ (int) $subPopup['subpop_delay'] }} * 1000,
        interval: {{ (int) $subPopup['subpop_interval'] }} * 60 * 1000  // show again this long after it was closed
    };
    var KEY_SUB = 'um_sub', KEY_CLOSED = 'um_sub_closed';
    var store = {
        get: function (k) { try { return localStorage.getItem(k); } catch (e) { return null; } },
        set: function (k, v) { try { localStorage.setItem(k, v); } catch (e) {} },
        del: function (k) { try { localStorage.removeItem(k); } catch (e) {} }
    };

    // Server record wins; the browser flag covers anonymous visitors without the cookie
    if (cfg.state === 'yes') store.set(KEY_SUB, '1');
    if (cfg.state === 'no') store.del(KEY_SUB);
    var subscribed = cfg.state === 'yes' || (cfg.state === 'unknown' && store.get(KEY_SUB) === '1');

    var modalEl = document.getElementById('subscribeModal');
    var modal = modalEl && window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    var openedByManager = false;

    function paintSubscribed() {
        document.documentElement.classList.toggle('is-subscribed', subscribed);
        document.querySelectorAll('.subscribe-btn').forEach(function (btn) {
            if (!subscribed) return;
            btn.removeAttribute('data-bs-toggle'); btn.removeAttribute('data-bs-target');
            btn.setAttribute('aria-disabled', 'true');
            btn.innerHTML = '<i class="ri-check-line"></i> Subscribed';
            btn.addEventListener('click', function (e) { e.preventDefault(); });
        });
    }

    window.UMSubscribe = {
        isSubscribed: function () { return subscribed; },
        /** Called after a successful subscribe (form, Google or LinkedIn). */
        markSubscribed: function (closeAfter) {
            subscribed = true;
            store.set(KEY_SUB, '1');
            store.del(KEY_CLOSED);
            paintSubscribed();
            if (modal && closeAfter) setTimeout(function () { modal.hide(); }, closeAfter);
        }
    };

    paintSubscribed();

    if (modalEl) {
        // opened by hand (Subscribe button) → tell the manager so the ad pop-up waits
        modalEl.addEventListener('show.bs.modal', function () {
            if (!openedByManager) window.UMPopups.request('subscribe', function () {});
        });
        modalEl.addEventListener('hidden.bs.modal', function () {
            openedByManager = false;
            if (!subscribed) store.set(KEY_CLOSED, String(Date.now()));   // start the "show again after…" clock
            window.UMPopups.done('subscribe');
        });
    }

    // Automatic pop-up
    document.addEventListener('DOMContentLoaded', function () {
        if (!cfg.enabled || subscribed || !modal) return;
        if (/[?&]popup_preview=1/.test(location.search)) return;
        var closedAt = Number(store.get(KEY_CLOSED) || 0);
        if (closedAt && Date.now() - closedAt < cfg.interval) return;         // closed recently → wait

        setTimeout(function () {
            // Ad pop-up active or about to show? → it goes first, we wait until it is closed.
            window.UMPopups.after('ad', function () {
                if (subscribed) return;
                window.UMPopups.request('subscribe', function () {
                    if (subscribed || modalEl.classList.contains('show')) { window.UMPopups.done('subscribe'); return; }
                    openedByManager = true;
                    modal.show();
                });
            }, 20000);
        }, cfg.delay);
    });
})();
</script>

{{-- Message after "Continue with Google / LinkedIn" --}}
@if(session('subscribed') || session('subscribe_error'))
    <div class="um-flash {{ session('subscribe_error') ? 'err' : '' }}" id="umFlash" role="status">
        <i class="{{ session('subscribe_error') ? 'ri-error-warning-line' : 'ri-checkbox-circle-line' }}"></i>
        <span>{{ session('subscribed') ?: session('subscribe_error') }}</span>
    </div>
    <script>
        (function () {
            @if(session('subscribed')) if (window.UMSubscribe) window.UMSubscribe.markSubscribed(0); @endif
            var el = document.getElementById('umFlash');
            setTimeout(function () { el.classList.add('show'); }, 100);
            setTimeout(function () { el.classList.remove('show'); }, 6000);
        })();
    </script>
@endif

@stack('scripts')

{{-- Pop-up advertisement (Admin → Advertising → Pop-up Ad) --}}
@include('partials.ads.popup')

{{-- Visitor counter → Admin → Dashboard → Website visitors (anonymous, no personal data) --}}
<script>
(function () {
    if (navigator.webdriver) return;
    var hitUrl = @json(route('track.hit')), pingUrl = @json(route('track.ping'));
    function send(url, withReferrer) {
        var data = new FormData();
        data.append('p', location.pathname);
        if (withReferrer) data.append('r', document.referrer || '');
        if (navigator.sendBeacon && navigator.sendBeacon(url, data)) return;
        fetch(url, { method: 'POST', body: data, credentials: 'same-origin', keepalive: true }).catch(function () {});
    }
    send(hitUrl, true);                                   // page opened
    setInterval(function () {                             // still reading → "Online now"
        if (document.visibilityState === 'visible') send(pingUrl, false);
    }, 30000);
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') send(pingUrl, false);
    });
})();
</script>

{!! S::get('footer_code') !!}

</body>

</html>
