{{-- Right column "Featured" list + square ad used on category / article pages --}}
<div class="reports-sidebar-header">
    <h3>{{ $title ?? 'Featured' }}</h3>
    <span></span>
</div>

<div class="related">
    @forelse($posts as $post)
        @include('partials.cards.related-post', ['post' => $post])
    @empty
        <p class="text-muted small">No featured stories yet.</p>
    @endforelse
</div>

@include('partials.ads.square', ['placement' => 'sidebar-ad', 'class' => 'my-3'])
