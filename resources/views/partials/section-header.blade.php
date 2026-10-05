{{-- @include('partials.section-header', ['title' => '', 'bigText' => '', 'link' => null, 'linkText' => 'View all', 'line' => false, 'class' => '']) --}}
<div class="section-header {{ $class ?? '' }}">
    <div class="featured-heading-wrap">
        @if(!empty($line))
            <span class="featured-heading-line"></span>
        @endif
        <div class="featured-heading-content">
            <h2 class="section-title">{{ $title }}</h2>
            @if(!empty($bigText))
                <div class="featured-big-text">{{ $bigText }}</div>
            @endif
        </div>
    </div>

    @if(!empty($link))
        <a href="{{ \App\Support\Menus::url($link) }}" class="view-all">
            {{ $linkText ?? 'View all' }}
            <i class="ri-arrow-right-line"></i>
        </a>
    @endif
</div>
