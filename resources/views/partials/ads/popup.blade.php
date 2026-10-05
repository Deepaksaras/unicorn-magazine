{{--
    Pop-up advertisement – settings: Admin → Advertising → Pop-up Ad
    Loaded hidden with the page; the script below decides when (and if) it opens.
--}}
@php
    $popupAd = \App\Support\Popup::forCurrentPage();
    $ps = \App\Support\Popup::settings();
    $preview = request()->boolean('popup_preview') && auth()->check();
@endphp
@if($popupAd)
<div class="um-popup {{ $ps['popup_size'] === 'large' ? 'large' : '' }}" id="umPopup" hidden
     role="dialog" aria-modal="true" aria-label="Advertisement"
     data-ad="{{ $popupAd->id }}"
     data-view-url="{{ route('ad.view', $popupAd->id) }}"
     data-delay="{{ $preview ? 0 : (int) $ps['popup_delay'] }}"
     data-scroll="{{ $preview ? 0 : (int) $ps['popup_scroll'] }}"
     data-close-after="{{ $preview ? 0 : (int) $ps['popup_close_after'] }}"
     data-auto-close="{{ (int) $ps['popup_auto_close'] }}"
     data-frequency="{{ $preview ? 'always' : $ps['popup_frequency'] }}"
     data-devices="{{ $preview ? 'all' : $ps['popup_devices'] }}"
     data-backdrop="{{ $ps['popup_backdrop_close'] === '1' ? 1 : 0 }}">
    <div class="um-popup-backdrop" data-popup-backdrop></div>
    <div class="um-popup-box">
        <div class="um-popup-top">
            <span class="um-popup-label">Advertisement</span>
            <button type="button" class="um-popup-close" data-popup-close aria-label="Close" disabled>
                <span data-popup-count></span><i class="ri-close-line"></i>
            </button>
        </div>

        <div class="um-popup-body">
            @if($popupAd->code)
                <div class="cms-ad-code">{!! $popupAd->code !!}</div>
            @elseif($popupAd->image)
                <a href="{{ \App\Support\Ads::link($popupAd) }}" target="{{ $popupAd->target ?: '_blank' }}" rel="noopener sponsored" class="um-popup-image">
                    <img src="{{ $popupAd->image_url }}" alt="{{ $popupAd->alt_text ?: $popupAd->title }}">
                </a>
            @else
                <div class="um-popup-text">
                    <div class="square-ad-icon">AD</div>
                    <h3>{{ $popupAd->title }}</h3>
                    @if($popupAd->description)<p>{{ $popupAd->description }}</p>@endif
                    @if($popupAd->url)
                        <a href="{{ \App\Support\Ads::link($popupAd) }}" target="{{ $popupAd->target ?: '_blank' }}" rel="noopener sponsored" class="um-btn">
                            {{ $popupAd->button_text ?: 'Learn More' }} <i class="ri-arrow-right-line"></i>
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <div class="um-popup-progress" data-popup-progress hidden><span></span></div>
    </div>
</div>

<style>
    .um-popup { position: fixed; inset: 0; z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 16px; }
    .um-popup[hidden] { display: none; }
    .um-popup-backdrop { position: absolute; inset: 0; background: rgba(10, 10, 10, .62); backdrop-filter: blur(2px); opacity: 0; transition: opacity .3s ease; }
    .um-popup-box { position: relative; width: 100%; max-width: 600px; max-height: calc(100vh - 32px); overflow: auto; background: #fff; border-radius: 16px; box-shadow: 0 30px 80px rgba(0, 0, 0, .35); opacity: 0; transform: translateY(18px) scale(.97); transition: opacity .3s ease, transform .3s ease; }
    .um-popup.large .um-popup-box { max-width: 800px; }
    .um-popup.open .um-popup-backdrop { opacity: 1; }
    .um-popup.open .um-popup-box { opacity: 1; transform: none; }
    .um-popup-top { position: absolute; top: 0; left: 0; right: 0; display: flex; justify-content: space-between; align-items: center; padding: 10px 12px; z-index: 2; pointer-events: none; }
    .um-popup-label { pointer-events: auto; padding: 3px 9px; border-radius: 50px; background: rgba(255, 255, 255, .92); color: #777; font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
    .um-popup-close { pointer-events: auto; display: inline-flex; align-items: center; gap: 4px; min-width: 36px; height: 36px; padding: 0 10px; border: 0; border-radius: 50px; background: #111; color: #fff; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .2s ease, opacity .2s ease; }
    .um-popup-close i { font-size: 18px; }
    .um-popup-close:disabled { background: rgba(17, 17, 17, .55); cursor: default; }
    .um-popup-close:disabled i { display: none; }
    .um-popup-close:not(:disabled):hover { background: #333; }
    .um-popup-image { display: block; }
    .um-popup-image img { display: block; width: 100%; height: auto; }
    .um-popup-body .cms-ad-code { padding: 48px 16px 16px; }
    .um-popup-text { padding: 56px 32px 34px; text-align: center; display: flex; flex-direction: column; align-items: center; }
    .um-popup-text .square-ad-icon { margin: 0 auto 16px; }
    .um-popup-text h3 { font-family: "Merriweather", Georgia, serif; font-size: 26px; font-weight: 700; margin: 0 0 10px; color: #111; }
    .um-popup-text p { color: #666; font-size: 15px; line-height: 1.6; margin: 0 0 22px; max-width: 440px; }
    .um-popup-text .um-btn { align-self: center; }
    .um-popup-progress { height: 4px; background: #eee; }
    .um-popup-progress span { display: block; height: 100%; width: 100%; background: #b08a4a; transform-origin: left; }
    @media (max-width: 575.98px) { .um-popup { padding: 12px; } .um-popup-text { padding: 52px 20px 26px; } .um-popup-text h3 { font-size: 21px; } }
</style>

<script>
(function () {
    var box = document.getElementById('umPopup');
    if (!box) return;
    var d = box.dataset, key = 'um_popup_' + d.ad;
    var isMobile = window.matchMedia('(max-width: 767.98px)').matches;
    if ((d.devices === 'desktop' && isMobile) || (d.devices === 'mobile' && !isMobile)) return;

    // How often: remember when this visitor last saw it
    var limits = { day: 864e5, week: 6048e5 };
    try {
        if (d.frequency === 'session' && sessionStorage.getItem(key)) return;
        if (limits[d.frequency] && Date.now() - Number(localStorage.getItem(key) || 0) < limits[d.frequency]) return;
    } catch (e) {}

    // Shared pop-up manager (layouts/front.blade.php): the ad pop-up goes first, the subscribe pop-up waits for it
    var PM = window.UMPopups || { expect: function () {}, request: function (n, open) { open(); }, done: function () {} };
    PM.expect('ad');

    var closeBtn = box.querySelector('[data-popup-close]');
    var count = box.querySelector('[data-popup-count]');
    var progress = box.querySelector('[data-popup-progress]');
    var timeUp = false, scrolledEnough = Number(d.scroll) <= 0, opened = false, asked = false, timers = [];

    function remember() {
        try {
            if (d.frequency === 'session') sessionStorage.setItem(key, '1');
            if (limits[d.frequency]) localStorage.setItem(key, String(Date.now()));
        } catch (e) {}
    }

    function close() {
        box.classList.remove('open');
        timers.forEach(clearTimeout);
        document.removeEventListener('keydown', onKey);
        setTimeout(function () { box.hidden = true; }, 300);
        PM.done('ad');                                    // lets the subscribe pop-up (if waiting) open next
    }

    function onKey(e) { if (e.key === 'Escape' && !closeBtn.disabled && d.backdrop === '1') close(); }

    function enableClose() { closeBtn.disabled = false; count.textContent = ''; }

    function open() {
        if (opened) return;
        opened = true;
        box.hidden = false;
        requestAnimationFrame(function () { box.classList.add('open'); });
        remember();

        // count the view (only real openings)
        try { navigator.sendBeacon ? navigator.sendBeacon(d.viewUrl) : fetch(d.viewUrl, { method: 'POST', keepalive: true }); } catch (e) {}

        // close button countdown
        var wait = Number(d.closeAfter) || 0;
        if (wait > 0) {
            count.textContent = 'Close in ' + wait;
            var left = wait;
            var tick = setInterval(function () {
                left -= 1;
                if (left <= 0) { clearInterval(tick); enableClose(); } else { count.textContent = 'Close in ' + left; }
            }, 1000);
            timers.push(tick);
        } else {
            enableClose();
        }

        // auto close with a progress bar
        var auto = Number(d.autoClose) || 0;
        if (auto > 0) {
            progress.hidden = false;
            var bar = progress.querySelector('span');
            bar.style.transition = 'transform ' + auto + 's linear';
            requestAnimationFrame(function () { requestAnimationFrame(function () { bar.style.transform = 'scaleX(0)'; }); });
            timers.push(setTimeout(close, auto * 1000));
        }

        document.addEventListener('keydown', onKey);
    }

    // ask the manager: opens now, or right after the subscribe pop-up if that one is open
    function maybeOpen() { if (timeUp && scrolledEnough && !opened && !asked) { asked = true; PM.request('ad', open); } }

    closeBtn.addEventListener('click', function () { if (!closeBtn.disabled) close(); });
    box.querySelector('[data-popup-backdrop]').addEventListener('click', function () {
        if (!closeBtn.disabled && d.backdrop === '1') close();
    });
    box.querySelectorAll('a[href]').forEach(function (a) { a.addEventListener('click', function () { setTimeout(close, 150); }); });

    // a short page that can't scroll counts as "scrolled"
    if (!scrolledEnough && document.documentElement.scrollHeight <= window.innerHeight + 10) scrolledEnough = true;

    if (!scrolledEnough) {
        window.addEventListener('scroll', function onScroll() {
            var h = document.documentElement.scrollHeight - window.innerHeight;
            if (h <= 0 || (window.scrollY / h) * 100 >= Number(d.scroll)) {
                scrolledEnough = true;
                window.removeEventListener('scroll', onScroll);
                maybeOpen();
            }
        }, { passive: true });
    }

    setTimeout(function () { timeUp = true; maybeOpen(); }, (Number(d.delay) || 0) * 1000);
})();
</script>
@endif
