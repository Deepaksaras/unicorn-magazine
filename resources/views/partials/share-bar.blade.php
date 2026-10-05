{{--
    Share buttons on article / profile / report pages – they share the SHORT link.
    @include('partials.share-bar', ['url' => $post->short_url, 'title' => $post->title])
--}}
@php
    $u = urlencode($url);
    $t = urlencode($title);
@endphp
<div class="post-share">
    <span class="share-label">SHARE</span>
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $u }}" target="_blank" rel="noopener" aria-label="Share on Facebook"><i class="ri-facebook-fill"></i></a>
    <a href="https://twitter.com/intent/tweet?url={{ $u }}&text={{ $t }}" target="_blank" rel="noopener" aria-label="Share on X"><i class="ri-twitter-x-fill"></i></a>
    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $u }}" target="_blank" rel="noopener" aria-label="Share on LinkedIn"><i class="ri-linkedin-fill"></i></a>
    <a href="https://wa.me/?text={{ $t }}%20{{ $u }}" target="_blank" rel="noopener" aria-label="Share on WhatsApp"><i class="ri-whatsapp-fill"></i></a>
    <a href="{{ $url }}" data-copy-link="{{ $url }}" aria-label="Copy link" title="Copy link"><i class="ri-link"></i></a>
    <a href="{{ $url }}" data-native-share data-title="{{ $title }}" data-url="{{ $url }}" class="d-none" aria-label="More ways to share" title="Share"><i class="ri-share-forward-line"></i></a>
    <span class="share-copied" role="status" aria-live="polite">Link copied</span>
</div>

@once
@push('styles')
<style>
    /* "Link copied" message: fixed at the bottom of the screen, so it never changes the share bar's position */
    .share-copied { position: fixed; left: 50%; bottom: 26px; transform: translate(-50%, 10px); white-space: nowrap; padding: 9px 16px; border-radius: 50px; background: #111; color: #fff; font-size: 13px; font-weight: 600; box-shadow: 0 10px 30px rgba(0, 0, 0, .25); opacity: 0; pointer-events: none; transition: opacity .2s ease, transform .2s ease; z-index: 1500; }
    .share-copied.show { opacity: 1; transform: translate(-50%, 0); }
    @media (max-width: 991.98px) { .post-share { flex-wrap: wrap; gap: 8px; } }
</style>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Copy the short link
    document.querySelectorAll('[data-copy-link]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            var link = btn.getAttribute('data-copy-link');
            var note = btn.parentElement.querySelector('.share-copied');
            var done = function () {
                if (!note) return;
                note.classList.add('show');
                setTimeout(function () { note.classList.remove('show'); }, 1800);
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(link).then(done, function () { window.prompt('Copy this link:', link); });
            } else {
                var box = document.createElement('textarea');
                box.value = link; document.body.appendChild(box); box.select();
                try { document.execCommand('copy'); done(); } catch (err) { window.prompt('Copy this link:', link); }
                box.remove();
            }
        });
    });

    // Phone "share" menu (WhatsApp, Instagram, Telegram, SMS …) where the browser supports it
    if (navigator.share) {
        document.querySelectorAll('[data-native-share]').forEach(function (btn) {
            btn.classList.remove('d-none');
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                navigator.share({ title: btn.getAttribute('data-title'), url: btn.getAttribute('data-url') }).catch(function () {});
            });
        });
    }
});
</script>
@endpush
@endonce
