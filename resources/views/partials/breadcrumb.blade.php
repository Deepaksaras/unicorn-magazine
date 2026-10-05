{{-- @include('partials.breadcrumb', ['items' => ['Business' => url, 'Title' => null]]) --}}
<div class="breadcrumb-bar">
    <div class="container-fluid px-4 px-lg-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}">Home</a>
                </li>
                @foreach($items as $label => $link)
                    @if($link && !$loop->last)
                        <li class="breadcrumb-item"><a href="{{ $link }}">{{ $label }}</a></li>
                    @else
                        <li class="breadcrumb-item active">{{ $label }}</li>
                    @endif
                @endforeach
            </ol>
        </nav>
    </div>
</div>
