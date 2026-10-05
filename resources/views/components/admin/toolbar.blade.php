@props(['placeholder' => 'Search…'])
<form method="GET" class="a-toolbar">
    {{-- keep the date filter (and sort) when searching --}}
    @foreach(['period', 'from', 'to', 'sort'] as $keep)
        @if(request()->filled($keep) && !is_array(request($keep)))
            <input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">
        @endif
    @endforeach
    <div class="a-search">
        <i class="ri-search-line"></i>
        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ $placeholder }}">
    </div>
    {{ $slot }}
    <button class="btn btn-soft" type="submit">Filter</button>
    @if(collect(request()->only(['q', 'status', 'category', 'type', 'filter', 'placement', 'role', 'period', 'sort']))->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty())
        <a href="{{ url()->current() }}" class="a-link-muted small">Clear</a>
    @endif
</form>
