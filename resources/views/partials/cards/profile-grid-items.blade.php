@foreach($profiles as $profile)
    @include('partials.cards.profile', ['profile' => $profile])
@endforeach
