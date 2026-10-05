<div class="a-savebar">
    <span class="me-auto d-none d-md-inline">{{ $note ?? 'Changes are published as soon as you save.' }}</span>
    <a href="{{ $cancel }}" class="btn btn-soft">Cancel</a>
    <button type="submit" class="btn btn-ink"><i class="ri-save-3-line me-1"></i> {{ $label ?? 'Save changes' }}</button>
</div>
