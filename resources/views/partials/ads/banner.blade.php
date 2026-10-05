{{-- Horizontal ad. @include('partials.ads.banner', ['placement' => 'header-ad']) --}}
@php $ad = \App\Support\Ads::for($placement ?? 'header-ad'); @endphp
<section class="ad-section">
    <div class="container-fluid px-4 px-lg-5">
        <div class="google-ad">
            <div class="ad-label">Advertisement</div>

            @if($ad && $ad->code)
                <div class="cms-ad-code">{!! $ad->code !!}</div>
            @elseif($ad && $ad->image)
                <a href="{{ \App\Support\Ads::link($ad) }}" target="{{ $ad->target ?: '_blank' }}" rel="noopener sponsored" class="cms-ad-image">
                    <img src="{{ $ad->image_url }}" alt="{{ $ad->alt_text ?: $ad->title }}">
                </a>
            @else
                <div class="ad-content">
                    <div class="ad-placeholder-icon">AD</div>
                    <div class="ad-placeholder-text">
                        <strong>{{ $ad->title ?? 'Your Advertisement Here' }}</strong>
                        <span>{{ $ad->description ?? 'Premium advertising space for brands and businesses' }}</span>
                    </div>
                    <a href="{{ $ad ? \App\Support\Ads::link($ad) : route('advertise') }}" class="ad-placeholder-btn" @if($ad && $ad->url) target="{{ $ad->target ?: '_blank' }}" rel="noopener sponsored" @endif>
                        {{ $ad->button_text ?? 'Learn More' }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>
