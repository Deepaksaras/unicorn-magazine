@extends('layouts.admin')
@section('title', 'SEO')
@section('breadcrumb', 'SEO')
@section('page_title', 'SEO')
@section('page_subtitle', 'The title, description and share image that Google, Facebook, LinkedIn, WhatsApp and X show for the website.')

@section('content')
@php
    $v = fn (string $key) => old($key, $values[$key] ?? '');
    $site = $values['site_name'] ?? 'The Unicorn Magazine';
@endphp

<div class="alert alert-light border shadow-sm" style="border-radius:12px">
    <strong><i class="ri-information-line me-1"></i> How it works</strong>
    <ul class="mb-0 mt-2 small">
        <li><strong>Articles, categories, tags, profiles, reports</strong> and <strong>pages</strong> (Home, About, Contact, Career, Advertise…) each have their own <strong>SEO</strong> box on their edit screen.</li>
        <li>This screen holds the <strong>site-wide defaults</strong> and the <strong>listing pages</strong> that have no edit screen.</li>
        <li>Anything left empty is filled automatically from the page’s own title and text, so no tag is ever empty.</li>
    </ul>
</div>

<form method="POST" action="{{ route('admin.seo.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="a-card">
        <div class="a-card-head"><div><h3>Site defaults</h3><div class="a-card-sub">Used on the home page and wherever a page has no SEO values of its own</div></div></div>
        <div class="a-card-body">
            <div class="row g-3">
                <div class="col-lg-7">
                    <x-admin.input name="default_meta_title" label="Home page / default title" :value="$v('default_meta_title')" maxlength="255" help="About 50–60 characters. Empty = the site name. (The Home page’s own SEO box wins over this.)" />
                    <x-admin.textarea name="default_meta_description" label="Default description" :value="$v('default_meta_description')" rows="3" help="About 150–160 characters." />
                    <x-admin.input name="default_meta_keywords" label="Default keywords" :value="$v('default_meta_keywords')" maxlength="255" help="Separate with commas, e.g. startups, funding, unicorns" />
                    <x-admin.input name="seo_twitter_site" label="X / Twitter username" :value="$v('seo_twitter_site')" maxlength="50" help="e.g. @unicornmagazine (optional)" />
                </div>
                <div class="col-lg-5">
                    <x-admin.image name="default_og_image" label="Default share image" :value="$values['default_og_image'] ?? null" :allow-url="false" help="Shown when a page without its own image is shared. Best size 1200 × 630. Empty = the site logo." />
                </div>
            </div>
        </div>
    </div>

    @foreach($pages as $key => [$label, $path, $defaultTitle, $defaultDescription])
        <div class="a-card">
            <div class="a-card-head">
                <div><h3>{{ $label }}</h3><div class="a-card-sub">{{ url($path) }}</div></div>
                <a href="{{ url($path) }}" target="_blank" class="btn btn-soft btn-sm"><i class="ri-external-link-line me-1"></i> View</a>
            </div>
            <div class="a-card-body">
                <div class="row g-3">
                    <div class="col-lg-7">
                        <x-admin.input name="seo_{{ $key }}_title" label="SEO title" :value="$v('seo_' . $key . '_title')" maxlength="255" placeholder="{{ $defaultTitle }} - {{ $site }}" help="Empty = “{{ $defaultTitle }} - {{ $site }}”" />
                        <x-admin.textarea name="seo_{{ $key }}_description" label="SEO description" :value="$v('seo_' . $key . '_description')" rows="2" placeholder="{{ $defaultDescription }}" help="{{ $defaultDescription ? 'Empty = the text shown in grey above.' : 'Empty = the default description.' }}" />
                        <x-admin.input name="seo_{{ $key }}_keywords" label="SEO keywords" :value="$v('seo_' . $key . '_keywords')" maxlength="255" help="Empty = the default keywords." />
                    </div>
                    <div class="col-lg-5">
                        <x-admin.image name="seo_{{ $key }}_image" label="Share image" :value="$values['seo_' . $key . '_image'] ?? null" :allow-url="false" help="Empty = the default share image." />
                    </div>
                </div>
                @if($key === 'search')
                    <div class="small text-muted mt-2"><i class="ri-eye-off-line me-1"></i> Search result pages are marked “noindex” so Google does not list them (recommended).</div>
                @endif
            </div>
        </div>
    @endforeach

    @include('admin.partials.savebar', ['label' => 'Save SEO settings', 'cancel' => route('admin.dashboard')])
</form>
@endsection
