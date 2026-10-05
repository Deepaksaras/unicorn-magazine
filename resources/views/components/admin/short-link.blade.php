{{--
    "Share link" box on the edit screens of Articles, Profiles and Reports.
    <x-admin.short-link :item="$post" />
--}}
@props(['item'])
@if($item->exists)
    @php
        $link = $item->short_url;
        $isShort = str_contains($link, '/s/');
        $live = (int) $item->status === 1 && (!isset($item->published_at) || !$item->published_at || !$item->published_at->isFuture());
    @endphp
    <div class="a-card">
        <div class="a-card-head"><div><h3><i class="ri-link me-1"></i>Share link</h3><div class="a-card-sub">Short link to share anywhere</div></div></div>
        <div class="a-card-body">
            @if($isShort)
                <div class="input-group">
                    <input type="text" class="form-control" value="{{ $link }}" readonly onclick="this.select()" aria-label="Short link">
                    <button type="button" class="btn btn-ink" data-copy="{{ $link }}"><i class="ri-file-copy-line me-1"></i>Copy</button>
                </div>
                <div class="d-flex justify-content-between small text-muted mt-2">
                    <span><i class="ri-cursor-line me-1"></i>Opened <strong class="text-body">{{ number_format((int) $item->short_clicks) }}</strong> times</span>
                    @unless($live)<span class="text-warning"><i class="ri-time-line me-1"></i>Works once published</span>@endunless
                </div>
            @else
                <div class="small text-muted">Short links appear here after the database update (database_updates.sql).</div>
            @endif
        </div>
    </div>
@endif
