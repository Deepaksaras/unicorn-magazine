<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImages;
use App\Http\Controllers\Admin\Concerns\SavesSeo;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Support\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    use HandlesImages, SavesSeo;

    public const TYPES = [
        'research' => 'Research',
        'market' => 'Market',
        'annual' => 'Annual',
        'quarterly' => 'Quarterly',
        'industry' => 'Industry',
    ];

    public function index(Request $request)
    {
        $reports = Report::where('status', '!=', 4)
            ->when($request->q, fn ($q, $t) => $q->where('title', 'like', "%{$t}%"))
            ->orderByDesc('report_date')->latest('id')
            ->paginate(20)->withQueryString();

        return view('admin.reports.index', compact('reports'));
    }

    public function create()
    {
        return view('admin.reports.form', ['report' => new Report(['status' => 1, 'report_type' => 'research', 'report_date' => now()]), 'types' => self::TYPES]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = auth()->id();
        $data['cover_image'] = $this->resolveImage($request, 'cover_image', null, 'reports', 1200);
        $data += $this->handleFile($request, null);

        $report = Report::create($data);
        $this->saveSeo($request, $report);
        $this->syncExclusive($report);

        return redirect()->route('admin.reports.index')->with('success', 'Report created.');
    }

    public function show(Report $report)
    {
        return redirect()->route('admin.reports.edit', $report);
    }

    public function edit(Report $report)
    {
        return view('admin.reports.form', ['report' => $report, 'types' => self::TYPES]);
    }

    public function update(Request $request, Report $report)
    {
        $data = $this->validated($request, $report);
        $data['cover_image'] = $this->resolveImage($request, 'cover_image', $report->cover_image, 'reports', 1200);
        $data += $this->handleFile($request, $report);

        $report->update($data);
        $this->saveSeo($request, $report);
        $this->syncExclusive($report);

        return redirect()->route('admin.reports.index')->with('success', 'Report updated.');
    }

    public function destroy(Report $report)
    {
        $report->update(['status' => 4, 'slug' => $report->slug . '-deleted-' . $report->id]);

        return redirect()->route('admin.reports.index')->with('success', 'Report deleted.');
    }

    protected function handleFile(Request $request, ?Report $report): array
    {
        if ($file = $request->file('report_file')) {
            if ($report && $report->file_path) {
                Storage::disk('public')->delete($report->file_path);
            }

            return [
                'file_path' => ImageUploader::storeFile($file, 'reports/files'),
                'file_type' => strtolower($file->getClientOriginalExtension()),
                'file_size' => $file->getSize(),
            ];
        }

        if ($report && $request->boolean('report_file_remove') && $report->file_path) {
            Storage::disk('public')->delete($report->file_path);

            return ['file_path' => null, 'file_size' => null];
        }

        return [];
    }

    /**
     * Only one report can be the home page "Exclusive".
     */
    protected function syncExclusive(Report $report): void
    {
        if ($report->is_exclusive) {
            Report::where('id', '!=', $report->id)->update(['is_exclusive' => false]);
        }
    }

    protected function validated(Request $request, ?Report $report = null): array
    {
        $request->validate($this->seoRules());   // SEO box

        $data = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:3000',
            'content' => 'nullable|string|max:200000',
            'report_type' => 'required|string|max:50',
            'category_label' => 'nullable|string|max:100',
            'author_name' => 'nullable|string|max:191',
            'report_date' => 'nullable|date',
            'external_url' => 'nullable|url|max:191',
            'report_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip|max:30720',
            'is_premium' => 'boolean',
            'is_exclusive' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'status' => 'required|in:0,1',
        ] + $this->imageRules('cover_image'));

        $slug = Str::slug($data['title']); $base = $slug; $i = 2;
        while (Report::withDeleted()->where('slug', $slug)->when($report, fn ($q) => $q->where('id', '!=', $report->id))->exists()) {
            $slug = $base . '-' . $i++;
        }

        unset($data['report_file'], $data['cover_image'], $data['cover_image_file']);

        return array_merge($data, [
            'slug' => $slug,
            'is_premium' => $request->boolean('is_premium'),
            'is_exclusive' => $request->boolean('is_exclusive'),
            'price' => $data['price'] ?? 0,
        ]);
    }
}
