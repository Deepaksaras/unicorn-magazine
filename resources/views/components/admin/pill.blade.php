@props(['status' => 1, 'on' => 'Active', 'off' => 'Inactive'])
@if((int) $status === 1)
    <span class="a-pill success">{{ $on }}</span>
@elseif((int) $status === 4)
    <span class="a-pill danger">Deleted</span>
@else
    <span class="a-pill muted">{{ $off }}</span>
@endif
