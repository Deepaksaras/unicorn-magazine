/* =====================================================================
   The Unicorn Magazine — Admin CMS behaviour
   (no build step; plain JS + Bootstrap 5, TinyMCE, SweetAlert2, Sortable)
===================================================================== */
(function () {
    "use strict";

    var csrf = document.querySelector('meta[name="csrf-token"]');
    var CSRF = csrf ? csrf.getAttribute('content') : '';

    /* ---------------- Sidebar (mobile) ---------------- */
    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-toggle-nav]')) {
            document.body.classList.toggle('nav-open');
        }
        if (e.target.classList.contains('a-backdrop')) {
            document.body.classList.remove('nav-open');
        }
    });

    /* ---------------- Toasts ---------------- */
    window.adminToast = function (message, type) {
        var wrap = document.querySelector('.a-toasts');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.className = 'a-toasts';
            document.body.appendChild(wrap);
        }
        var el = document.createElement('div');
        el.className = 'a-toast ' + (type || 'success');
        el.innerHTML = '<i class="' + (type === 'error' ? 'ri-error-warning-line' : 'ri-checkbox-circle-line') + '"></i>' +
            '<div></div><button type="button" class="x" aria-label="Close">&times;</button>';
        el.querySelector('div').textContent = message;
        wrap.appendChild(el);
        bindToast(el);
    };

    function bindToast(el) {
        var close = function () {
            el.classList.add('hide');
            setTimeout(function () { el.remove(); }, 350);
        };
        el.querySelector('.x') && el.querySelector('.x').addEventListener('click', close);
        setTimeout(close, 5000);
    }

    document.querySelectorAll('.a-toast').forEach(bindToast);

    /* ---------------- Confirm (delete etc.) ---------------- */
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.matches('[data-confirm]') || form.dataset.confirmed === '1') return;
        e.preventDefault();

        var text = form.getAttribute('data-confirm') || 'Are you sure?';
        var run = function () { form.dataset.confirmed = '1'; form.submit(); };

        if (window.Swal) {
            Swal.fire({
                title: form.getAttribute('data-confirm-title') || 'Please confirm',
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: form.getAttribute('data-confirm-button') || 'Yes, delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true
            }).then(function (r) { if (r.isConfirmed) run(); });
        } else if (window.confirm(text)) {
            run();
        }
    });

    /* ---------------- Slug from title ---------------- */
    function slugify(v) {
        return v.toString().toLowerCase().normalize('NFKD').replace(/[̀-ͯ]/g, '')
            .replace(/&/g, ' and ').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').substring(0, 180);
    }

    document.querySelectorAll('[data-slug-from]').forEach(function (slug) {
        var source = document.querySelector(slug.getAttribute('data-slug-from'));
        if (!source) return;
        var touched = slug.value.trim() !== '';
        slug.addEventListener('input', function () { touched = slug.value.trim() !== ''; });
        source.addEventListener('input', function () { if (!touched) slug.value = slugify(source.value); });
    });

    /* ---------------- Character counters ---------------- */
    document.querySelectorAll('[data-count]').forEach(function (input) {
        var max = parseInt(input.getAttribute('maxlength') || input.getAttribute('data-count'), 10);
        var badge = document.createElement('div');
        badge.className = 'form-text text-end';
        input.insertAdjacentElement('afterend', badge);
        var update = function () { badge.textContent = input.value.length + ' / ' + max; };
        input.addEventListener('input', update);
        update();
    });

    /* ---------------- Image pickers ---------------- */
    function bindImage(box) {
        if (box.dataset.bound) return;
        box.dataset.bound = '1';

        var file = box.querySelector('input[type="file"]');
        var url = box.querySelector('[data-image-url]');
        var remove = box.querySelector('[data-image-remove]');
        var preview = box.querySelector('.a-image-preview');

        var show = function (src) {
            if (!preview) return;
            preview.innerHTML = src ? '<img src="' + src + '" alt="">' : '<i class="ri-image-line"></i>';
        };

        file && file.addEventListener('change', function () {
            if (file.files && file.files[0]) {
                var reader = new FileReader();
                reader.onload = function (ev) { show(ev.target.result); };
                reader.readAsDataURL(file.files[0]);
                if (remove) remove.checked = false;
            }
        });

        url && url.addEventListener('change', function () {
            var v = url.value.trim();
            if (/^(https?:)?\/\//.test(v)) show(v);
        });

        remove && remove.addEventListener('change', function () {
            if (remove.checked) { show(''); if (file) file.value = ''; }
        });
    }

    window.bindImages = function (root) {
        (root || document).querySelectorAll('.a-image').forEach(bindImage);
    };
    bindImages();

    /* ---------------- Icon preview ---------------- */
    document.addEventListener('input', function (e) {
        if (!e.target.matches('[data-icon-input]')) return;
        var prev = e.target.closest('.input-group').querySelector('.a-icon-preview i');
        if (prev) prev.className = e.target.value.trim() || 'ri-question-line';
    });

    /* ---------------- Rich text (TinyMCE) ---------------- */
    var uploadUrl = document.body.getAttribute('data-upload-url');

    window.initRichText = function (root) {
        if (!window.tinymce) return;
        var nodes = (root || document).querySelectorAll('textarea.js-richtext');
        nodes.forEach(function (ta) {
            if (!ta.id) ta.id = 'rt_' + Math.random().toString(36).slice(2);
            if (tinymce.get(ta.id)) return;
            tinymce.init({
                target: ta,
                height: parseInt(ta.getAttribute('data-height') || '320', 10),
                menubar: false,
                branding: false,
                promotion: false,
                plugins: 'autolink link lists image media table code searchreplace wordcount autoresize',
                toolbar: 'blocks | bold italic underline | bullist numlist blockquote | link image media table | alignleft aligncenter | removeformat code',
                block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
                autoresize_bottom_margin: 20,
                min_height: 220,
                max_height: 900,
                convert_urls: false,
                relative_urls: false,
                image_caption: true,
                // Editor text looks exactly like the article page on the website
                content_css: 'https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Merriweather:wght@400;700;900&display=swap',
                content_style: [
                    'body{font-family:"Instrument Sans",sans-serif;font-size:15px;line-height:1.85;color:#555;max-width:850px;margin:16px auto;padding:0 12px}',
                    'p{margin:0 0 22px}',
                    'h2,h3,h4{font-family:"Merriweather",Georgia,serif;color:#111;font-weight:600}',
                    'h2{font-size:28px;line-height:1.35;margin:40px 0 18px}',
                    'h3{font-size:21px;line-height:1.4;margin:32px 0 14px}',
                    'h4{font-size:18px;line-height:1.4;margin:26px 0 12px}',
                    'a{color:#9a7627}',
                    'blockquote{margin:28px 0;padding:6px 0 6px 20px;border-left:3px solid #b08a4a;color:#222;font-style:italic}',
                    'img{max-width:100%;height:auto;border-radius:14px}',
                    'hr{border:0;border-top:1px solid #e7e7e7;margin:34px 0}',
                    'table{border-collapse:collapse;width:100%}td,th{border:1px solid #e7e7e7;padding:8px 10px}',
                    // old / pasted text that still carries its own fonts, sizes or colours looks like the website here too
                    'p,li,td,th,span,div,em,i,u,strong,b,a,font,small{font-family:"Instrument Sans",sans-serif !important;letter-spacing:normal !important;background:transparent !important}',
                    'p,li,td,th{font-size:15px !important;line-height:1.85 !important;color:#555 !important}',
                    'span,em,i,u,strong,b,font,small{font-size:inherit !important;line-height:inherit !important;color:inherit !important}',
                    'strong,b{color:#222 !important}a,a *{color:#9a7627 !important}',
                    'h1,h2,h3,h4,h5,h6,h1 *,h2 *,h3 *,h4 *,h5 *,h6 *{font-family:"Merriweather",Georgia,serif !important;color:#111 !important;font-size:inherit;line-height:inherit}',
                    'h2{font-size:28px !important}h3{font-size:21px !important}h4{font-size:18px !important}',
                    // pictures and videos: same spacing as on the article page
                    'img{display:block;margin:28px auto}figure.image{display:block;margin:28px 0;width:100%}figure.image img{margin:0 auto;width:100%}',
                    'figure.image figcaption{margin-top:10px;text-align:center;color:#777;font-size:13px;font-style:italic}',
                    'iframe,video,.mce-preview-object{display:block;width:100% !important;max-width:100%;aspect-ratio:16/9;height:auto !important;margin:28px 0;border:0;border-radius:14px}',
                    '.mce-preview-object iframe{margin:0}',
                    'ul,ol{margin:0 0 22px;padding-left:22px}li{margin-bottom:8px}'
                ].join(''),
                // Pasted text (Word, Google Docs, ChatGPT, websites) keeps bold/lists/links
                // but loses its own fonts, sizes and colours, so it matches the website.
                invalid_styles: { '*': 'font-family font-size color background background-color line-height letter-spacing word-spacing text-indent white-space margin margin-top margin-bottom padding' },
                invalid_elements: 'font,o:p,style,script',
                paste_webkit_styles: 'none',
                paste_remove_styles_if_webkit: true,
                paste_preprocess: function (editor, args) {
                    args.content = args.content
                        .replace(/<(\/?)h1(\s|>)/gi, '<$1h2$2')          // only one H1 per page: the article title
                        .replace(/<(\/?)h[56](\s|>)/gi, '<$1h4$2')
                        .replace(/\sclass="[^"]*"/gi, '');                 // drop foreign CSS classes
                },
                images_upload_handler: function (blobInfo) {
                    var data = new FormData();
                    data.append('file', blobInfo.blob(), blobInfo.filename());
                    return fetch(uploadUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                        body: data
                    }).then(function (r) {
                        if (!r.ok) throw new Error('Upload failed (' + r.status + ')');
                        return r.json();
                    }).then(function (json) { return json.location; });
                },
                setup: function (ed) { ed.on('change', function () { ed.save(); }); }
            });
        });
    };

    function removeRichText(root) {
        if (!window.tinymce) return;
        root.querySelectorAll('textarea.js-richtext').forEach(function (ta) {
            var ed = ta.id && tinymce.get(ta.id);
            if (ed) { ed.save(); ed.remove(); }
        });
    }

    /* ---------------- Repeaters ---------------- */
    function renumber(list) {
        list.querySelectorAll(':scope > .a-repeater-item').forEach(function (item, i) {
            var num = item.querySelector('.num');
            if (num) num.textContent = i + 1;
        });
        var wrap = list.closest('[data-repeater]');
        var empty = wrap && wrap.querySelector('[data-repeater-empty]');
        if (empty) empty.classList.toggle('d-none', list.children.length > 0);
    }

    function itemTitle(item) {
        var first = item.querySelector('input[type="text"], textarea');
        var t = item.querySelector('.title');
        if (t && first) t.textContent = (first.value || '').substring(0, 70) || t.getAttribute('data-default');
    }

    document.addEventListener('click', function (e) {
        var add = e.target.closest('[data-repeater-add]');
        if (add) {
            var wrap = add.closest('[data-repeater]');
            var list = wrap.querySelector('[data-repeater-list]');
            var tpl = wrap.querySelector('template');
            var html = tpl.innerHTML.replace(/__INDEX__/g, 'n' + Date.now() + Math.floor(Math.random() * 1000));
            list.insertAdjacentHTML('beforeend', html);
            var item = list.lastElementChild;
            renumber(list);
            bindImages(item);
            initRichText(item);
            var f = item.querySelector('input, textarea');
            f && f.focus();
            return;
        }

        var del = e.target.closest('[data-repeater-remove]');
        if (del) {
            var it = del.closest('.a-repeater-item');
            var l = it.parentElement;
            removeRichText(it);
            it.remove();
            renumber(l);
            return;
        }

        var tog = e.target.closest('[data-repeater-toggle]');
        if (tog) {
            var ri = tog.closest('.a-repeater-item');
            ri.classList.toggle('collapsed');
            tog.querySelector('i').className = ri.classList.contains('collapsed') ? 'ri-arrow-down-s-line' : 'ri-arrow-up-s-line';
            return;
        }

        var up = e.target.closest('[data-repeater-move]');
        if (up) {
            var item2 = up.closest('.a-repeater-item');
            var dir = up.getAttribute('data-repeater-move');
            var sib = dir === 'up' ? item2.previousElementSibling : item2.nextElementSibling;
            if (!sib) return;
            removeRichText(item2); removeRichText(sib);
            if (dir === 'up') sib.before(item2); else sib.after(item2);
            renumber(item2.parentElement);
            initRichText(item2); initRichText(sib);
        }
    });

    document.addEventListener('input', function (e) {
        var item = e.target.closest('.a-repeater-item');
        if (item) itemTitle(item);
    });

    document.querySelectorAll('[data-repeater-list]').forEach(function (list) {
        renumber(list);
        list.querySelectorAll(':scope > .a-repeater-item').forEach(itemTitle);
        if (window.Sortable) {
            Sortable.create(list, {
                handle: '.handle',
                animation: 180,
                onStart: function () { removeRichText(list); },
                onEnd: function () { renumber(list); initRichText(list); }
            });
        }
    });

    /* ---------------- Sortable tables (menu items etc.) ---------------- */
    document.querySelectorAll('[data-sortable-url]').forEach(function (tbody) {
        if (!window.Sortable) return;
        Sortable.create(tbody, {
            handle: '.handle',
            animation: 180,
            onEnd: function () {
                var ids = Array.prototype.map.call(tbody.querySelectorAll('[data-id]'), function (r) { return r.getAttribute('data-id'); });
                fetch(tbody.getAttribute('data-sortable-url'), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ids: ids })
                }).then(function (r) { return r.json(); })
                  .then(function (d) { adminToast(d.message || 'Order saved'); })
                  .catch(function () { adminToast('Could not save the order', 'error'); });
            }
        });
    });

    /* ---------------- SEO box: live Google preview ---------------- */
    document.querySelectorAll('[data-seo-input]').forEach(function (input) {
        var target = document.querySelector('[data-seo-preview="' + input.getAttribute('data-seo-input') + '"]');
        if (!target) return;
        input.addEventListener('input', function () {
            target.textContent = input.value.trim() || target.getAttribute('data-auto') || '';
        });
    });

    /* ---------------- Auto-submit filters (see below) ---------------- */
    /* ---------------- Copy to clipboard (media URLs, short links) ---------------- */
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-copy]');
        if (!btn) return;
        e.preventDefault();
        var text = btn.getAttribute('data-copy');
        var ok = function () { window.adminToast && window.adminToast('Copied: ' + text); };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(ok, function () { window.prompt('Copy this link:', text); });
        } else {
            var box = document.createElement('textarea');
            box.value = text; document.body.appendChild(box); box.select();
            try { document.execCommand('copy'); ok(); } catch (err) { window.prompt('Copy this link:', text); }
            box.remove();
        }
    });

    /* ---------------- Date filter: "Date range" button shows the date boxes ---------------- */
    document.querySelectorAll('[data-daterange-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var box = btn.closest('.a-datebar').querySelector('[data-daterange]');
            if (box) { box.classList.toggle('show'); var f = box.querySelector('input[type=date]'); if (f && box.classList.contains('show')) f.focus(); }
        });
    });

    document.querySelectorAll('[data-autosubmit]').forEach(function (el) {
        el.addEventListener('change', function () { el.form && el.form.submit(); });
    });

    /* ---------------- Warn on unsaved changes ---------------- */
    document.querySelectorAll('form[data-dirty-check]').forEach(function (form) {
        var dirty = false;
        form.addEventListener('input', function () { dirty = true; });
        form.addEventListener('change', function () { dirty = true; });
        form.addEventListener('submit', function () { dirty = false; });
        window.addEventListener('beforeunload', function (e) {
            if (dirty) { e.preventDefault(); e.returnValue = ''; }
        });
    });

    document.addEventListener('DOMContentLoaded', function () { initRichText(); });
    if (document.readyState !== 'loading') initRichText();
})();
