@extends('layouts.admin')

@section('title', 'Articles')
@section('breadcrumb', 'Articles')
@section('page_title', 'Articles')
@section('page_subtitle', 'Write, schedule and feature stories. Featured stories appear in the home hero and sidebars.')
@section('page_actions')
    <a href="{{ route('admin.articles.create') }}" class="btn btn-ink"><i class="ri-add-line me-1"></i> New article</a>
@endsection

@section('content')
{{-- Top viewed articles (follows the date filter below) --}}
<div class="a-card">
    <div class="a-card-head">
        <div>
            <h3><i class="ri-fire-line text-warning me-1"></i>Top viewed articles</h3>
            <div class="a-card-sub">
                {{ $range->active() ? 'Most read · ' . $range->label . ' (views recorded in these dates)' : 'Most read of all time' }}
            </div>
        </div>
        <a href="{{ route('admin.articles.index', array_merge(request()->except('page'), ['sort' => $range->active() ? 'period_views' : 'views'])) }}" class="a-link-muted small">Sort list by views <i class="ri-arrow-down-line"></i></a>
    </div>
    <div class="a-card-body">
        @if($topViewed->count())
            <div class="a-top-list">
                @foreach($topViewed as $i => $row)
                    <a href="{{ route('admin.articles.edit', $row['post']->id) }}" class="a-top-item">
                        <span class="a-top-rank">{{ $i + 1 }}</span>
                        <span style="min-width:0">
                            <span class="a-top-title">{{ $row['post']->title }}</span>
                            <span class="a-top-views d-block"><strong>{{ number_format($row['views']) }}</strong> views</span>
                        </span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-muted small">No article views {{ $range->active() ? 'recorded for ' . $range->label : 'yet' }}.</div>
        @endif
    </div>
</div>

<div class="a-card">
    <x-admin.date-filter :range="$range" :count="$posts->total()" noun="articles" column="by publish date" />

    <x-admin.toolbar placeholder="Search titles…">
        <select name="category" class="form-select" data-autosubmit>
            <option value="">All categories</option>
            @foreach($categories as $id => $name)
                <option value="{{ $id }}" @selected(request('category') == $id)>{{ $name }}</option>
            @endforeach
        </select>
        <select name="status" class="form-select" data-autosubmit>
            <option value="">Any status</option>
            <option value="1" @selected(request('status') === '1')>Published</option>
            <option value="scheduled" @selected(request('status') === 'scheduled')>Scheduled</option>
            <option value="0" @selected(request('status') === '0')>Draft</option>
        </select>
        <select name="filter" class="form-select" data-autosubmit>
            <option value="">All</option>
            <option value="featured" @selected(request('filter') === 'featured')>Featured only</option>
        </select>
        <select name="sort" class="form-select" data-autosubmit aria-label="Sort">
            <option value="latest" @selected($sort === 'latest')>Recently updated</option>
            <option value="newest" @selected($sort === 'newest')>Newest published</option>
            <option value="views" @selected($sort === 'views')>Most viewed (all time)</option>
            @if($range->active())
                <option value="period_views" @selected($sort === 'period_views')>Most viewed in this period</option>
            @endif
        </select>
    </x-admin.toolbar>

    @if($posts->count())
        <div class="table-responsive">
            <table class="a-table">
                <thead>
                    <tr>
                        <th style="width:72px"></th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th class="text-end">Views</th>
                        @if($range->active())<th class="text-end text-nowrap">Views · {{ $range->period === 'custom' ? 'range' : $range->label }}</th>@endif
                        <th>Updated</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($posts as $post)
                        <tr>
                            <td><img src="{{ $post->thumb_url }}" class="a-thumb" alt=""></td>
                            <td class="a-title-cell">
                                <strong>
                                    <a href="{{ route('admin.articles.edit', $post) }}" class="text-reset text-decoration-none">{{ $post->title }}</a>
                                    @if($post->is_featured)<i class="ri-star-fill text-warning ms-1" title="Featured"></i>@endif
                                </strong>
                                <small>By {{ $post->author->name ?? '—' }} @if($post->badge) · <span class="a-pill gold py-0">{{ $post->badge }}</span>@endif</small>
                            </td>
                            <td>{{ $post->category->name ?? '—' }}</td>
                            <td>
                                @if($post->status == 1 && $post->published_at && $post->published_at->isFuture())
                                    <span class="a-pill info">Scheduled</span>
                                @else
                                    <x-admin.pill :status="$post->status" on="Published" off="Draft" />
                                @endif
                            </td>
                            <td class="text-end">{{ number_format($post->view_count) }}</td>
                            @if($range->active())
                                <td class="text-end fw-semibold">{{ number_format($periodViews[$post->slug] ?? 0) }}</td>
                            @endif
                            <td class="text-muted small text-nowrap">{{ $post->updated_at->diffForHumans() }}</td>
                            <td>
                                <div class="a-row-actions">
                                    @if($post->status == 1)<button type="button" class="btn btn-soft btn-icon btn-sm" data-copy="{{ $post->short_url }}" title="Copy short link"><i class="ri-link"></i></button>@endif
                                    @if($post->status == 1)
                                        <a href="{{ route('article', $post->slug) }}" target="_blank" class="btn btn-soft btn-icon btn-sm" title="View"><i class="ri-external-link-line"></i></a>
                                    @endif
                                    <x-admin.edit-link :href="route('admin.articles.edit', $post)" />
                                    <x-admin.delete :action="route('admin.articles.destroy', $post)" message="Move “{{ $post->title }}” to trash?" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['items' => $posts])
    @else
        <x-admin.empty icon="ri-article-line" title="No articles found" text="Try a different filter, or write your first story." :action="route('admin.articles.create')" actionLabel="New article" />
    @endif
</div>
@endsection
