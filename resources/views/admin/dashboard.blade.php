@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page_title', 'Good ' . (now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening')) . ', ' . \Illuminate\Support\Str::before(auth()->user()->name, ' '))
@section('page_subtitle', 'Here’s what’s happening across the magazine today.')
@section('page_actions')
    <a href="{{ route('admin.pages.index') }}" class="btn btn-soft"><i class="ri-layout-4-line me-1"></i> Edit pages</a>
    <a href="{{ route('admin.articles.create') }}" class="btn btn-ink"><i class="ri-quill-pen-line me-1"></i> Write a story</a>
@endsection

@section('content')
{{-- Website visitors: Online now + Today / This month / Date range --}}
@include('admin.partials.visitors')

<div class="row g-3 mb-4">
    @foreach([
        ['admin.articles.index', 'ri-article-line', number_format($stats['published']), 'Published articles', 'ink', []],
        ['admin.articles.index', 'ri-draft-line', number_format($stats['drafts']), 'Drafts', '', ['status' => 0]],
        ['admin.articles.index', 'ri-eye-line', number_format($stats['views']), 'Total article views', '', []],
        ['admin.subscribers.index', 'ri-mail-star-line', number_format($stats['subscribers']), 'Active subscribers', '', []],
        ['admin.contact-messages.index', 'ri-mail-open-line', $stats['messages'], 'Unread messages', '', ['filter' => 'unread']],
        ['admin.advertising-enquiries.index', 'ri-hand-coin-line', $stats['enquiries'], 'New ad enquiries', '', []],
    ] as [$route, $icon, $value, $label, $tone, $params])
        <div class="col-sm-6 col-xl-4 col-xxl-2">
            <a href="{{ route($route, $params) }}" class="a-stat h-100">
                <span class="a-stat-icon {{ $tone }}"><i class="{{ $icon }}"></i></span>
                <div><div class="a-stat-value">{{ $value }}</div><div class="a-stat-label">{{ $label }}</div></div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="a-card">
            <div class="a-card-head">
                <div><h3>Recently updated stories</h3><div class="a-card-sub">Your latest edits</div></div>
                <a href="{{ route('admin.articles.index') }}" class="a-link-muted small">All articles <i class="ri-arrow-right-line"></i></a>
            </div>
            @if($recentPosts->count())
                <div class="table-responsive">
                    <table class="a-table">
                        <tbody>
                        @foreach($recentPosts as $post)
                            <tr>
                                <td style="width:72px"><img src="{{ $post->thumb_url }}" class="a-thumb" alt=""></td>
                                <td class="a-title-cell"><strong><a href="{{ route('admin.articles.edit', $post) }}" class="text-reset text-decoration-none">{{ $post->title }}</a></strong>
                                    <small>{{ $post->category->name ?? '—' }} · {{ $post->author->name ?? '—' }}</small></td>
                                <td><x-admin.pill :status="$post->status" on="Published" off="Draft" /></td>
                                <td class="small text-muted text-nowrap text-end">{{ $post->updated_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-admin.empty icon="ri-quill-pen-line" title="No stories yet" :action="route('admin.articles.create')" actionLabel="Write the first one" />
            @endif
        </div>

        <div class="a-card">
            <div class="a-card-head"><div><h3>New subscribers</h3><div class="a-card-sub">Last 14 days</div></div></div>
            <div class="a-card-body">
                @php $max = max(1, $subscriberTrend->max('count')); @endphp
                <div class="d-flex align-items-end gap-2" style="height:140px">
                    @foreach($subscriberTrend as $day)
                        <div class="flex-fill text-center" title="{{ \Carbon\Carbon::parse($day['date'])->format('M d') }}: {{ $day['count'] }}">
                            <div style="height:{{ max(4, round($day['count'] / $max * 110)) }}px;background:{{ $day['count'] ? 'linear-gradient(180deg,#c6a65b,#a98a44)' : '#eceef2' }};border-radius:6px 6px 3px 3px;transition:height .4s"></div>
                            <div class="text-muted mt-1" style="font-size:10.5px">{{ \Carbon\Carbon::parse($day['date'])->format('d') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="a-card">
            <div class="a-card-head"><h3>Most read</h3></div>
            <div class="a-card-body">
                @forelse($topPosts as $i => $post)
                    <div class="d-flex gap-3 align-items-start {{ $loop->last ? '' : 'mb-3' }}">
                        <span class="a-brand-mark" style="width:30px;height:30px;font-size:13px;border-radius:9px">{{ $i + 1 }}</span>
                        <div class="flex-fill">
                            <a href="{{ route('admin.articles.edit', $post->id) }}" class="fw-semibold text-reset text-decoration-none d-block" style="line-height:1.35">{{ \Illuminate\Support\Str::limit($post->title, 70) }}</a>
                            <span class="small text-muted">{{ number_format($post->view_count) }} views</span>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">No views recorded yet.</div>
                @endforelse
            </div>
        </div>

        <div class="a-card">
            <div class="a-card-head"><h3>Inbox</h3><a href="{{ route('admin.contact-messages.index') }}" class="a-link-muted small">Open</a></div>
            <div class="a-card-body">
                @forelse($messages->concat($enquiries)->sortByDesc('created_at')->take(6) as $item)
                    @php $isEnquiry = $item instanceof \App\Models\AdvertisingEnquiry; @endphp
                    <a href="{{ $isEnquiry ? route('admin.advertising-enquiries.show', $item) : route('admin.contact-messages.show', $item) }}"
                       class="d-flex gap-3 text-decoration-none text-reset {{ $loop->last ? '' : 'mb-3' }}">
                        <span class="a-stat-icon" style="width:36px;height:36px;font-size:16px"><i class="{{ $isEnquiry ? 'ri-hand-coin-line' : 'ri-mail-line' }}"></i></span>
                        <span class="flex-fill" style="min-width:0">
                            <span class="d-flex justify-content-between"><strong class="{{ $item->is_read ? 'fw-semibold' : '' }}">{{ $item->name }}</strong>
                                <span class="small text-muted">{{ $item->created_at->diffForHumans(null, true) }}</span></span>
                            <span class="small text-muted d-block text-truncate">{{ $isEnquiry ? ($item->company . ' · ' . $item->placement_interest) : ($item->subject . ' — ' . $item->message) }}</span>
                        </span>
                        @unless($item->is_read)<span class="mt-2" style="width:8px;height:8px;border-radius:50%;background:var(--gold);flex-shrink:0"></span>@endunless
                    </a>
                @empty
                    <div class="text-muted small">No messages yet.</div>
                @endforelse
            </div>
        </div>

        <div class="a-card">
            <div class="a-card-head"><h3>Quick links</h3></div>
            <div class="a-card-body d-grid gap-2">
                <a href="{{ route('admin.breaking-news.index') }}" class="btn btn-soft text-start"><i class="ri-flashlight-line me-2"></i>Update the breaking news bar</a>
                <a href="{{ route('admin.pages.edit', 'home') }}" class="btn btn-soft text-start"><i class="ri-home-5-line me-2"></i>Home page sections</a>
                <a href="{{ route('admin.menus.index') }}" class="btn btn-soft text-start"><i class="ri-menu-search-line me-2"></i>Navigation menus</a>
                <a href="{{ route('admin.settings.index') }}" class="btn btn-soft text-start"><i class="ri-settings-4-line me-2"></i>Logo, footer & social links</a>
            </div>
        </div>
    </div>
</div>
@endsection
