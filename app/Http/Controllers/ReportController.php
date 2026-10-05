<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\ReportController as AdminReports;
use App\Models\Report;
use App\Support\PostFeed;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Public report pages (content: Admin → Content → Reports).
 *
 *   /reports                    list (filter: ?type=market)
 *   /report/{slug}              one report
 *   /reports/{slug}/download    counts the download, then serves the file / external link
 */
class ReportController extends Controller
{
    public const PER_PAGE = 10;

    public function index(Request $request)
    {
        $types = $this->types();
        $type = array_key_exists((string) $request->type, $types) ? $request->type : null;

        $query = Report::where('status', 1)
            ->when($type, fn ($q) => $q->where('report_type', $type))
            ->orderByDesc('report_date')
            ->latest('id');

        // Big card at the top: the "Exclusive" report, otherwise the newest one (page 1 only)
        $hero = null;
        if ($request->integer('page', 1) === 1) {
            $hero = (clone $query)->where('is_exclusive', true)->first() ?: (clone $query)->first();
        }

        $reports = (clone $query)
            ->when($hero, fn ($q) => $q->where('id', '!=', $hero->id))
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {            // "Load more" button
            return response()->json([
                'html' => view('partials.cards.report-items', ['reports' => $reports])->render(),
                'next' => $reports->nextPageUrl(),
            ]);
        }

        Seo::page('reports');   // Admin → System → SEO

        return view('reports.index', [
            'hero' => $hero,
            'reports' => $reports,
            'types' => $types,
            'type' => $type,
            'featured' => PostFeed::featured(6),
        ]);
    }

    public function show(string $slug)
    {
        $report = Report::where('slug', $slug)->where('status', 1)->firstOrFail();

        Report::whereKey($report->id)->increment('view_count');

        // SEO: the report's SEO box (Admin → Reports), else title / description / cover
        Seo::model($report, [
            'title' => $report->title,
            'description' => $report->description ?: $report->content,
            'image' => $report->cover_url,
            'type' => 'article',
            'published' => optional($report->display_date)->toIso8601String(),
            'author' => $report->author_name,
        ]);

        $more = Report::where('status', 1)
            ->where('id', '!=', $report->id)
            ->orderByRaw('report_type = ? DESC', [$report->report_type])
            ->orderByDesc('report_date')
            ->limit(3)
            ->get();

        return view('reports.show', [
            'report' => $report,
            'more' => $more,
            'featured' => PostFeed::featured(6),
            'typeLabel' => AdminReports::TYPES[$report->report_type] ?? Str::headline((string) $report->report_type),
        ]);
    }

    /**
     * Counts the download, then serves the uploaded file or redirects to the external link.
     */
    public function download(string $slug)
    {
        $report = Report::where('slug', $slug)->where('status', 1)->firstOrFail();

        Report::whereKey($report->id)->increment('download_count');

        if ($report->file_path && Storage::disk('public')->exists($report->file_path)) {
            return Storage::disk('public')->download(
                $report->file_path,
                Str::slug($report->title) . '.' . pathinfo($report->file_path, PATHINFO_EXTENSION)
            );
        }

        if ($report->external_url) {
            return redirect()->away($report->external_url);
        }

        return redirect()->route('report', $report->slug)->with('success', 'This report is not available for download yet.');
    }

    /** Report types that have published reports, e.g. ['market' => 'Market']. */
    protected function types(): array
    {
        $used = Report::where('status', 1)->distinct()->pluck('report_type')->filter()->all();

        return collect(AdminReports::TYPES)
            ->only($used)
            ->union(collect($used)->diff(array_keys(AdminReports::TYPES))->mapWithKeys(fn ($t) => [$t => Str::headline($t)]))
            ->all();
    }
}
