{{-- Square ad. @include('partials.ads.square', ['placement' => 'sidebar-ad', 'class' => 'my-3']) --}}
@php $ad = \App\Support\Ads::for($placement ?? 'sidebar-ad'); @endphp
<div class="square-ad {{ $class ?? '' }}">
    <span class="square-ad-label">Advertisement</span>

    @if($ad && $ad->code)
        <div class="cms-ad-code">{!! $ad->code !!}</div>
    @elseif($ad && $ad->image)
        <a href="{{ \App\Support\Ads::link($ad) }}" target="{{ $ad->target ?: '_blank' }}" rel="noopener sponsored" class="cms-ad-image">
            <img src="{{ $ad->image_url }}" alt="{{ $ad->alt_text ?: $ad->title }}">
        </a>
    @else
        <div class="square-ad-inner">
            <div class="square-ad-icon">AD</div>
            <h4>{{ $ad->title ?? 'Grow Your Business' }}</h4>
            <p>{{ $ad->description ?? 'Reach the right audience with premium advertising opportunities.' }}</p>
            <a href="{{ $ad ? \App\Support\Ads::link($ad) : route('advertise') }}" class="square-ad-btn" @if($ad && $ad->url) target="{{ $ad->target ?: '_blank' }}" rel="noopener sponsored" @endif>
                {{ $ad->button_text ?? 'Learn More' }}
                <i class="ri-arrow-right-line"></i>
            </a>
        </div>
    @endif
</div>
