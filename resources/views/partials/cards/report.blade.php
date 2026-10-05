{{-- Report row (Home "Reports", /reports, "More reports") – opens the report page --}}
<article class="report-item">
    <a href="{{ $report->link }}" class="report-image">
        <img src="{{ $report->cover_url }}" alt="{{ $report->title }}" loading="lazy">
    </a>

    <div class="report-content">
        <div class="report-meta">
            <span class="report-category">{{ $report->type_label }}</span>
            <span class="report-date">{{ $report->display_date->format('M d, Y') }}</span>
        </div>

        <h3 class="report-title">
            <a href="{{ $report->link }}">{{ $report->title }}</a>
        </h3>

        @if($report->description)
            <p class="report-description">{{ \Illuminate\Support\Str::limit(strip_tags($report->description), 160) }}</p>
        @endif

        <div class="report-bottom">
            <span class="report-reading">
                <i class="ri-pencil-line"></i>
                By {{ $report->author_name ?: ($report->user->name ?? 'Research Desk') }}
            </span>
            <a href="{{ $report->link }}" class="report-arrow" aria-label="Open report">
                <i class="ri-arrow-right-line"></i>
            </a>
        </div>
    </div>
</article>
