@props(['action', 'message' => 'This item will be removed. You can’t undo this.', 'label' => null, 'size' => 'sm'])
<form method="POST" action="{{ $action }}" class="d-inline" data-confirm="{{ $message }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-soft-danger {{ $label ? 'btn-' . $size : 'btn-icon btn-' . $size }}" title="Delete">
        <i class="ri-delete-bin-6-line"></i>@if($label) {{ $label }}@endif
    </button>
</form>
