@props(['icon' => 'ri-inbox-line', 'title' => 'Nothing here yet', 'text' => null, 'action' => null, 'actionLabel' => null])
<div class="a-empty">
    <i class="{{ $icon }}"></i>
    <strong>{{ $title }}</strong>
    @if($text)<div>{{ $text }}</div>@endif
    @if($action)
        <a href="{{ $action }}" class="btn btn-ink btn-sm mt-3"><i class="ri-add-line"></i> {{ $actionLabel }}</a>
    @endif
</div>
