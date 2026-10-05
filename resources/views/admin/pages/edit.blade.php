@extends('layouts.admin')
@php $page = $content->page; @endphp
@section('title', 'Edit ' . $blueprint['title'])
@section('breadcrumb')
    <a href="{{ route('admin.pages.index') }}">Pages</a> <i class="ri-arrow-right-s-line"></i> {{ $blueprint['title'] }}
@endsection
@section('page_title', $page->title ?? $blueprint['title'])
@section('page_subtitle', $blueprint['description'])
@section('page_actions')
    <a href="{{ route($blueprint['route']) }}" target="_blank" class="btn btn-soft"><i class="ri-external-link-line me-1"></i> View page</a>
@endsection

@include('admin.partials.editor')

@section('content')
<form method="POST" action="{{ route('admin.pages.update', $key) }}" enctype="multipart/form-data" data-dirty-check>
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-lg-3">
            <div class="a-section-nav">
                <div class="a-card mb-3">
                    <div class="a-card-body">
                        <x-admin.input name="title" label="Page name" :value="$page->title ?? $blueprint['title']" required />
                        <x-admin.status :value="$page->status ?? 1" :labels="['1' => 'Published', '0' => 'Draft (hidden)']" />
                    </div>
                </div>
                <div class="small text-muted text-uppercase fw-semibold px-2 mb-2" style="letter-spacing:.8px;font-size:11px">Sections</div>
                @foreach($blueprint['sections'] as $sectionKey => $section)
                    <a href="#sec-{{ $sectionKey }}"><span class="dot {{ $content->enabled($sectionKey) ? '' : 'off' }}"></span>{{ $section['label'] }}</a>
                @endforeach
                <a href="#sec-seo"><span class="dot" style="background:var(--gold)"></span>SEO</a>
            </div>
        </div>

        <div class="col-lg-9">
            @foreach($blueprint['sections'] as $sectionKey => $section)
                <div class="a-card a-section {{ $content->enabled($sectionKey) ? '' : 'is-off' }}" id="sec-{{ $sectionKey }}">
                    <div class="a-card-head">
                        <div>
                            <h3>{{ $section['label'] }}</h3>
                            @if(!empty($section['help']))<div class="a-card-sub">{{ $section['help'] }}</div>@endif
                        </div>
                        <div class="form-check form-switch m-0" title="Show this section on the page">
                            <input type="hidden" name="enabled[{{ $sectionKey }}]" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" name="enabled[{{ $sectionKey }}]" value="1"
                                   id="en_{{ $sectionKey }}" @checked($content->enabled($sectionKey))
                                   onchange="this.closest('.a-section').classList.toggle('is-off', !this.checked)">
                            <label class="form-check-label small" for="en_{{ $sectionKey }}">Visible</label>
                        </div>
                    </div>
                    <div class="a-card-body">
                        <div class="row g-3">
                            @foreach($section['fields'] as $fieldKey => $field)
                                @php
                                    $full = in_array($field['type'], ['richtext', 'repeater', 'textarea', 'lines']);
                                    $col = $full ? 'col-12' : ($field['type'] === 'image' ? 'col-md-6' : 'col-md-6');
                                @endphp
                                <div class="{{ $col }}">
                                    @include('admin.pages._field', [
                                        'field' => $field,
                                        'name' => "sections[{$sectionKey}][{$fieldKey}]",
                                        'value' => $content->get("{$sectionKey}.{$fieldKey}"),
                                    ])
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="a-section">
                <x-admin.seo :model="$page" :title="$page->title ?? $blueprint['title']" :url="route($blueprint['route'])" image-help="Shown when this page is shared. Empty = the site's default share image." />
            </div>
        </div>
    </div>

    @include('admin.partials.savebar', ['cancel' => route('admin.pages.index'), 'label' => 'Save page', 'note' => 'Tip: turn a section off with its “Visible” switch to hide it without losing the content.'])
</form>
@endsection
