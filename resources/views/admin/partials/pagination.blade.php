@if($items->hasPages())
    <div class="a-card-foot d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="small text-muted">Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }}</span>
        {{ $items->onEachSide(1)->links() }}
    </div>
@elseif($items->total())
    <div class="a-card-foot small text-muted">{{ $items->total() }} {{ \Illuminate\Support\Str::plural('item', $items->total()) }}</div>
@endif
