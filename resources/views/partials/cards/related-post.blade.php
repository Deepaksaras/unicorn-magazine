<a href="{{ $post->url }}" class="related-post">
    <div class="related-thumb">
        <img src="{{ $post->thumb_url }}" alt="{{ $post->title }}" loading="lazy">
    </div>
    <div class="related-content">
        <div class="related-top">
            <span class="related-badge">{{ $post->badge ?: ($post->category->name ?? 'Story') }}</span>
            <span class="related-date">{{ $post->display_date->format('M d') }}</span>
        </div>
        <h4 class="related-title">{{ $post->title }}</h4>
        <div class="related-meta">
            <span>By {{ $post->author_name }}</span>
        </div>
    </div>
</a>
