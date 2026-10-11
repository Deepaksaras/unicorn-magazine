{{-- @include('partials.breadcrumb', ['items' => ['Business' => url, 'Title' => null]]) --}}
<div class="breadcrumb-bar">
    <div class="container-fluid px-4 px-lg-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}">Home</a>
                </li>
                @foreach($items as $label => $link)
                    @if($link && !$loop->last)
                        <li class="breadcrumb-item"><a href="{{ $link }}">{{ $label }}</a></li>
                    @else
                        <li class="breadcrumb-item active">{{ $label }}</li>
                    @endif
                @endforeach
            </ol>
        </nav>
    </div>
</div>

{{-- The same trail as structured data, so Google can show it under the result --}}
@php
    $crumbPosition = 0;
    $crumbList = [];
    $crumbList[] = ['@type' => 'ListItem', 'position' => ++$crumbPosition, 'name' => 'Home', 'item' => route('home')];
    $crumbLast = array_key_last($items);
    foreach ($items as $crumbLabel => $crumbLink) {
        $crumb = ['@type' => 'ListItem', 'position' => ++$crumbPosition, 'name' => (string) $crumbLabel];
        if ($crumbLink && $crumbLabel !== $crumbLast) {
            $crumb['item'] = $crumbLink;
        }
        $crumbList[] = $crumb;
    }
@endphp
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    '@id' => url()->current() . '#breadcrumb',
    'itemListElement' => $crumbList,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
