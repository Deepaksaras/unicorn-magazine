<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvertisementPlacement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdvertisementPlacementController extends Controller
{
    /** Slugs the website templates render. */
    public const SYSTEM = [
        'header-ad' => 'Banner under the navigation (home)',
        'in-content-ad' => 'Banners inside lists and articles',
        'sidebar-ad' => 'Square ad in sidebars',
    ];

    public function index()
    {
        $placements = AdvertisementPlacement::withCount(['advertisements' => fn ($q) => $q->where('status', 1)->where('is_active', true)])
            ->where('status', '!=', 4)->orderBy('name')->paginate(30);

        return view('admin.advertisement-placements.index', ['placements' => $placements, 'system' => self::SYSTEM]);
    }

    public function create()
    {
        return view('admin.advertisement-placements.form', ['placement' => new AdvertisementPlacement(['status' => 1, 'is_active' => true, 'max_ads' => 1]), 'system' => self::SYSTEM]);
    }

    public function store(Request $request)
    {
        AdvertisementPlacement::create($this->validated($request));

        return redirect()->route('admin.advertisement-placements.index')->with('success', 'Placement created.');
    }

    public function show(AdvertisementPlacement $advertisementPlacement)
    {
        return redirect()->route('admin.advertisement-placements.edit', $advertisementPlacement);
    }

    public function edit(AdvertisementPlacement $advertisementPlacement)
    {
        return view('admin.advertisement-placements.form', ['placement' => $advertisementPlacement, 'system' => self::SYSTEM]);
    }

    public function update(Request $request, AdvertisementPlacement $advertisementPlacement)
    {
        $advertisementPlacement->update($this->validated($request, $advertisementPlacement));

        return redirect()->route('admin.advertisement-placements.index')->with('success', 'Placement updated.');
    }

    public function destroy(AdvertisementPlacement $advertisementPlacement)
    {
        if (array_key_exists($advertisementPlacement->slug, self::SYSTEM)) {
            return back()->with('error', 'This placement is used by the website templates and cannot be deleted. Deactivate it instead.');
        }

        $advertisementPlacement->update(['status' => 4, 'slug' => $advertisementPlacement->slug . '-deleted-' . $advertisementPlacement->id]);

        return redirect()->route('admin.advertisement-placements.index')->with('success', 'Placement deleted.');
    }

    protected function validated(Request $request, ?AdvertisementPlacement $placement = null): array
    {
        $isSystem = $placement && array_key_exists($placement->slug, self::SYSTEM);
        $request->merge(['slug' => $isSystem ? $placement->slug : Str::slug($request->input('slug') ?: $request->input('name'))]);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => ['required', 'max:191', Rule::unique('advertisement_placements', 'slug')->ignore($placement?->id)],
            'description' => 'nullable|string|max:191',
            'dimensions' => 'nullable|string|max:50',
            'location' => 'nullable|string|max:100',
            'max_ads' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'status' => 'required|in:0,1',
        ]);

        return array_merge($data, ['is_active' => $request->boolean('is_active'), 'max_ads' => (int) ($data['max_ads'] ?? 1)]);
    }
}
