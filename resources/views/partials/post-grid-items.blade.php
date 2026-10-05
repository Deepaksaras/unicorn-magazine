@foreach($posts as $post)
    @include('partials.cards.top-pick', ['post' => $post])
@endforeach
