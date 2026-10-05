{{--
    SEO box for an edit form (articles, categories, tags, profiles, reports, pages).
    <x-admin.seo :model="$post" :title="$post->title" :description="$post->excerpt" :url="$post->url" />
    Saved by App\Http\Controllers\Admin\Concerns\SavesSeo. Empty = automatic (falls back to the item's own text).
--}}
@props(['model' => null, 'title' => null, 'description' => null, 'url' => null, 'imageHelp' => 'Shown when the link is shared on WhatsApp, LinkedIn, Facebook, X. Empty = the main image.'])
@php
    $seo = $model && $model->exists ? $model->seoMeta()->first() : null;
    $val = fn (string $column, $legacy = null) => old('seo_' . $column, $seo->{$column} ?? $legacy);
    $siteName = \App\Support\SiteSettings::get('site_name', 'The Unicorn Magazine');
    $autoTitle = trim((string) $title) !== '' ? $title . ' - ' . $siteName : $siteName;
    $autoDescription = \Illuminate\Support\Str::limit(trim(strip_tags((string) $description)), 160);
    $legacyTitle = $model ? $model->getAttribute('meta_title') : null;
    $legacyDescription = $model ? $model->getAttribute('meta_description') : null;
    $titleValue = old('seo_title', $seo->meta_title ?? $legacyTitle);
    $descValue = old('seo_description', $seo->meta_description ?? $legacyDescription);
@endphp
<div class="a-card" id="sec-seo">
    <div class="a-card-head">
        <div><h3><i class="ri-search-eye-line me-1"></i>SEO</h3><div class="a-card-sub">How this page appears on Google and when shared. Leave a box empty to use the automatic value.</div></div>
    </div>
    <div class="a-card-body">
        {{-- Google preview --}}
        <div class="a-seo-preview mb-3">
            <div class="a-seo-url">{{ $url ?: url('/') }}</div>
            <div class="a-seo-title" data-seo-preview="title" data-auto="{{ $autoTitle }}">{{ $titleValue ?: $autoTitle }}</div>
            <div class="a-seo-desc" data-seo-preview="description" data-auto="{{ $autoDescription }}">{{ $descValue ?: ($autoDescription ?: 'The site description is used.') }}</div>
        </div>

        <div class="row g-3">
            <div class="col-lg-7">
                <x-admin.input name="seo_title" label="SEO title" :value="$titleValue" maxlength="191" data-count="60" data-seo-input="title" :placeholder="$autoTitle" help="The browser tab and the blue Google headline. About 60 characters." />
                <x-admin.textarea name="seo_description" label="SEO description" :value="$descValue" rows="3" maxlength="191" data-count="160" data-seo-input="description" :placeholder="$autoDescription" help="The grey text under the headline on Google. About 160 characters." />
                <x-admin.input name="seo_keywords" label="SEO keywords" :value="$val('meta_keywords')" maxlength="191" placeholder="startup funding, unicorn, fintech" help="Comma separated." />
            </div>
            <div class="col-lg-5">
                <x-admin.image name="seo_og_image" label="Share image (OG image)" :value="$val('og_image')" :help="$imageHelp" />
            </div>
        </div>

        <details class="a-seo-more mt-2" @if(($seo->og_title ?? null) || ($seo->og_description ?? null) || ($seo->canonical_url ?? null) || ($seo->robots ?? null)) open @endif>
            <summary>Advanced: share title, share text, canonical URL, hide from Google</summary>
            <div class="row g-3 mt-1">
                <div class="col-md-6"><x-admin.input name="seo_og_title" label="Share title (OG title)" :value="$val('og_title')" maxlength="191" help="Empty = the page title." /></div>
                <div class="col-md-6"><x-admin.input name="seo_og_description" label="Share text (OG description)" :value="$val('og_description')" maxlength="191" help="Empty = the SEO description." /></div>
                <div class="col-md-8"><x-admin.input type="url" name="seo_canonical" label="Canonical URL" :value="$val('canonical_url')" placeholder="https://" help="Only if this content's main address is a different page. Empty = this page's own address." /></div>
                <div class="col-md-4 d-flex align-items-center"><x-admin.toggle name="seo_noindex" label="Hide from Google" :checked="\Illuminate\Support\Str::contains((string) ($seo->robots ?? ''), 'noindex')" help="Adds “noindex”." /></div>
            </div>
        </details>
    </div>
</div>
