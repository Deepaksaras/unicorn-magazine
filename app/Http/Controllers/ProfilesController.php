<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\ProfileController as AdminProfiles;
use App\Models\Profile;
use App\Support\PostFeed;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Public "Stories & Profiles" pages (content: Admin → Content → Profiles).
 *
 *   /profiles               list of all profiles (filter: ?type=founder)
 *   /profile/{slug}         one profile
 */
class ProfilesController extends Controller
{
    public const PER_PAGE = 12;

    public function index(Request $request)
    {
        $types = $this->types();
        $type = array_key_exists((string) $request->type, $types) ? $request->type : null;

        $query = Profile::with('post:id,slug,status,title')
            ->where('status', 1)
            ->when($type, fn ($q) => $q->where('profile_type', $type))
            ->orderBy('position')
            ->latest('id');

        // First profile = big card at the top (only on page 1)
        $hero = $request->integer('page', 1) === 1 ? (clone $query)->first() : null;

        $profiles = (clone $query)
            ->when($hero, fn ($q) => $q->where('id', '!=', $hero->id))
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {           // "Load more" button
            return response()->json([
                'html' => view('partials.cards.profile-grid-items', ['profiles' => $profiles])->render(),
                'next' => $profiles->nextPageUrl(),
            ]);
        }

        Seo::page('profiles');   // Admin → System → SEO

        return view('profiles.index', [
            'hero' => $hero,
            'profiles' => $profiles,
            'types' => $types,
            'type' => $type,
            'featured' => PostFeed::featured(6),
            'stories' => PostFeed::fromCategory('stories-profiles', 4),
        ]);
    }

    public function show(string $slug)
    {
        $profile = Profile::with('post:id,slug,status,title,excerpt,featured_image,published_at,created_at,user_id')
            ->where('slug', $slug)
            ->where('status', 1)
            ->firstOrFail();

        Profile::whereKey($profile->id)->increment('view_count');

        // SEO: the profile's SEO box (Admin → Profiles), else name / summary / photo
        Seo::model($profile, [
            'title' => $profile->display_name . ($profile->role_line ? ' – ' . $profile->role_line : ''),
            'description' => $profile->summary ?: $profile->biography ?: $profile->display_name . ($profile->role_line ? ', ' . $profile->role_line : ''),
            'keywords' => collect([$profile->display_name, $profile->company_name, $profile->industry])->filter()->implode(', '),
            'image' => $profile->image_url,
            'type' => 'profile',
        ]);

        // Same type first, then the rest
        $more = Profile::where('status', 1)
            ->where('id', '!=', $profile->id)
            ->orderByRaw('profile_type = ? DESC', [$profile->profile_type])
            ->orderBy('position')
            ->limit(5)
            ->get();

        // Stories that mention this person
        $mentions = PostFeed::base()
            ->where(fn ($q) => $q->where('title', 'like', '%' . $profile->display_name . '%')
                ->when($profile->company_name, fn ($w) => $w->orWhere('title', 'like', '%' . $profile->company_name . '%')))
            ->when($profile->post_id, fn ($q) => $q->where('posts.id', '!=', $profile->post_id))
            ->limit(4)
            ->get();

        return view('profiles.show', [
            'profile' => $profile,
            'more' => $more,
            'mentions' => $mentions,
            'featured' => PostFeed::featured(6),
            'typeLabel' => AdminProfiles::TYPES[$profile->profile_type] ?? Str::headline((string) $profile->profile_type),
        ]);
    }

    /** Types that actually have published profiles, e.g. ['founder' => 'Founders']. */
    protected function types(): array
    {
        $used = Profile::where('status', 1)->distinct()->pluck('profile_type')->filter()->all();

        return collect(AdminProfiles::TYPES)
            ->only($used)
            ->map(fn ($label) => Str::plural($label))
            ->union(collect($used)->diff(array_keys(AdminProfiles::TYPES))->mapWithKeys(fn ($t) => [$t => Str::headline(Str::plural($t))]))
            ->all();
    }
}
