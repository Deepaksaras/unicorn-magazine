<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobOpening;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JobOpeningController extends Controller
{
    public function index(Request $request)
    {
        $jobs = JobOpening::where('status', '!=', 4)
            ->when($request->q, fn ($q, $t) => $q->where('title', 'like', "%{$t}%"))
            ->orderBy('position')->orderByDesc('id')
            ->paginate(30)->withQueryString();

        return view('admin.job-openings.index', compact('jobs'));
    }

    public function create()
    {
        $next = (int) JobOpening::where('status', '!=', 4)->max('position') + 1;

        return view('admin.job-openings.form', ['job' => new JobOpening(['status' => 1, 'position' => $next, 'employment_type' => 'Full Time'])]);
    }

    public function store(Request $request)
    {
        JobOpening::create($this->validated($request));

        return redirect()->route('admin.job-openings.index')->with('success', 'Job opening created.');
    }

    public function edit(JobOpening $jobOpening)
    {
        return view('admin.job-openings.form', ['job' => $jobOpening]);
    }

    public function update(Request $request, JobOpening $jobOpening)
    {
        $jobOpening->update($this->validated($request, $jobOpening));

        return redirect()->route('admin.job-openings.index')->with('success', 'Job opening updated.');
    }

    public function destroy(JobOpening $jobOpening)
    {
        $jobOpening->update(['status' => 4, 'slug' => $jobOpening->slug . '-deleted-' . $jobOpening->id]);

        return redirect()->route('admin.job-openings.index')->with('success', 'Job opening deleted.');
    }

    public function reorder(Request $request)
    {
        foreach ((array) $request->input('ids', []) as $i => $id) {
            JobOpening::whereKey($id)->update(['position' => $i + 1]);
        }

        return response()->json(['message' => 'Order saved.']);
    }

    protected function validated(Request $request, ?JobOpening $job = null): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:191',
            'department' => 'nullable|string|max:100',
            'employment_type' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:191',
            'summary' => 'nullable|string|max:3000',
            'responsibilities' => 'nullable|string|max:5000',
            'requirements' => 'nullable|string|max:5000',
            'apply_url' => 'nullable|string|max:191',
            'position' => 'nullable|integer',
            'status' => 'required|in:0,1',
        ]);

        $lines = fn ($v) => array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $v))));

        $slug = Str::slug($data['title']); $base = $slug; $i = 2;
        while (JobOpening::withDeleted()->where('slug', $slug)->when($job, fn ($q) => $q->where('id', '!=', $job->id))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return array_merge($data, [
            'slug' => $slug,
            'responsibilities' => $lines($data['responsibilities'] ?? ''),
            'requirements' => $lines($data['requirements'] ?? ''),
            'position' => (int) ($data['position'] ?? 0),
        ]);
    }
}
