{{-- @include('partials.cards.top-pick', ['post' => $post, 'showCategory' => true]) --}}
<article class="top-pick-card">
    <a href="{{ $post->url }}" class="top-pick-link">
        <div class="top-pick-image">
            <img src="{{ $post->thumb_url }}" alt="{{ $post->title }}" loading="lazy">
        </div>
        <div class="top-pick-content">
            @if(!empty($showCategory) && $post->category)
                <span class="top-pick-category">{{ $post->category->name }}</span>
            @endif
            <h3 class="top-pick-title">{{ $post->title }}</h3>
            <div class="author-date">
                <span>By {{ $post->author_name }}</span>
                <span class="meta-dot"></span>
                <span>{{ $post->display_date->format('M d, Y') }}</span>
            </div>
        </div>
    </a>
</article>
