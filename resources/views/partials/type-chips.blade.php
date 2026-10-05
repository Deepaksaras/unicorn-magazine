{{-- Filter chips: @include('partials.type-chips', ['types' => [...], 'active' => $type, 'route' => 'profiles.index', 'all' => 'All profiles']) --}}
@if(count($types) > 1)
    <nav class="type-chips" aria-label="Filter">
        <a href="{{ route($route) }}" class="{{ $active ? '' : 'active' }}">{{ $all ?? 'All' }}</a>
        @foreach($types as $key => $label)
            <a href="{{ route($route, ['type' => $key]) }}" class="{{ $active === $key ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </nav>
@endif
