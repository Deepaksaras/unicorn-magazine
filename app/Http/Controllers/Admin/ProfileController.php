<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImages;
use App\Http\Controllers\Admin\Concerns\SavesSeo;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    use HandlesImages, SavesSeo;

    public const TYPES = [
        'entrepreneur' => 'Entrepreneur',
        'billionaire' => 'Billionaire',
        'executive' => 'Executive',
        'founder' => 'Founder',
        'other' => 'Other',
    ];

    public function index(Request $request)
    {
        $profiles = Profile::where('status', '!=', 4)
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('name', 'like', "%{$t}%")->orWhere('company_name', 'like', "%{$t}%")))
            ->when($request->type, fn ($q, $t) => $q->where('profile_type', $t))
            ->orderBy('position')->latest('id')
            ->paginate(20)->withQueryString();

        return view('admin.profiles.index', ['profiles' => $profiles, 'types' => self::TYPES]);
    }

    public function create()
    {
        $next = (int) Profile::where('status', '!=', 4)->max('position') + 1;

        return view('admin.profiles.form', $this->formData(new Profile(['status' => 1, 'position' => $next, 'profile_type' => 'entrepreneur', 'currency' => 'INR'])));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['profile_image'] = $this->resolveImage($request, 'profile_image', null, 'profiles', 900);
        $profile = Profile::create($data);
        $this->saveSeo($request, $profile);

        return redirect()->route('admin.profiles.index')->with('success', 'Profile created.');
    }

    public function show(Profile $profile)
    {
        return redirect()->route('admin.profiles.edit', $profile);
    }

    public function edit(Profile $profile)
    {
        return view('admin.profiles.form', $this->formData($profile));
    }

    public function update(Request $request, Profile $profile)
    {
        $data = $this->validated($request, $profile);
        $data['profile_image'] = $this->resolveImage($request, 'profile_image', $profile->profile_image, 'profiles', 900);
        $profile->update($data);
        $this->saveSeo($request, $profile);

        return redirect()->route('admin.profiles.index')->with('success', 'Profile updated.');
    }

    public function destroy(Profile $profile)
    {
        $profile->update(['status' => 4, 'slug' => $profile->slug . '-deleted-' . $profile->id]);

        return redirect()->route('admin.profiles.index')->with('success', 'Profile deleted.');
    }

    protected function formData(Profile $profile): array
    {
        return [
            'profile' => $profile,
            'types' => self::TYPES,
            'posts' => Post::where('status', 1)->latest('published_at')->limit(300)->pluck('title', 'id'),
        ];
    }

    protected function validated(Request $request, ?Profile $profile = null): array
    {
        $request->validate($this->seoRules());   // SEO box

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'profile_type' => 'required|string|max:50',
            'label' => 'nullable|string|max:100',
            'summary' => 'nullable|string|max:500',
            'company_name' => 'nullable|string|max:191',
            'designation' => 'nullable|string|max:191',
            'industry' => 'nullable|string|max:191',
            'net_worth' => 'nullable|string|max:100',
            'currency' => 'nullable|string|size:3',
            'biography' => 'nullable|string|max:60000',
            'achievements' => 'nullable|string|max:5000',
            'slug' => 'nullable|string|max:191',
            'website' => 'nullable|url|max:191',
            'linkedin_url' => 'nullable|url|max:191',
            'twitter_url' => 'nullable|url|max:191',
            'post_id' => 'nullable|exists:posts,id',
            'position' => 'nullable|integer',
            'status' => 'required|in:0,1',
        ] + $this->imageRules('profile_image'));

        unset($data['profile_image'], $data['profile_image_file']);

        // Page address /profile/{slug} – unique, taken from the name when left empty
        $slug = Str::slug($data['slug'] ?: $data['name']) ?: 'profile';
        $base = $slug;
        $i = 2;
        while (Profile::withDeleted()->where('slug', $slug)->when($profile, fn ($q) => $q->where('id', '!=', $profile->id))->exists()) {
            $slug = $base . '-' . $i++;
        }
        $data['slug'] = $slug;

        // "Key achievements" – one per line in the form, saved as a list
        $data['achievements'] = collect(preg_split('/\r\n|\r|\n/', (string) ($data['achievements'] ?? '')))
            ->map(fn ($line) => trim(ltrim(trim($line), '-•*')))
            ->filter()
            ->values()
            ->all() ?: null;
        $data['position'] = (int) ($data['position'] ?? 0);
        $data['currency'] = strtoupper($data['currency'] ?? 'INR');

        return $data;
    }
}
