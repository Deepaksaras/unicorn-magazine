<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImages;
use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeamMemberController extends Controller
{
    use HandlesImages;

    public function index(Request $request)
    {
        $members = TeamMember::where('status', '!=', 4)
            ->when($request->q, fn ($q, $t) => $q->where('name', 'like', "%{$t}%"))
            ->orderBy('sort_order')->orderBy('id')
            ->paginate(30)->withQueryString();

        return view('admin.team-members.index', compact('members'));
    }

    public function create()
    {
        $next = (int) TeamMember::where('status', '!=', 4)->max('sort_order') + 1;

        return view('admin.team-members.form', ['member' => new TeamMember(['status' => 1, 'sort_order' => $next])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['photo'] = $this->resolveImage($request, 'photo', null, 'team', 900);
        TeamMember::create($data);

        return redirect()->route('admin.team-members.index')->with('success', 'Team member added.');
    }

    public function edit(TeamMember $teamMember)
    {
        return view('admin.team-members.form', ['member' => $teamMember]);
    }

    public function update(Request $request, TeamMember $teamMember)
    {
        $data = $this->validated($request, $teamMember);
        $data['photo'] = $this->resolveImage($request, 'photo', $teamMember->photo, 'team', 900);
        $teamMember->update($data);

        return redirect()->route('admin.team-members.index')->with('success', 'Team member updated.');
    }

    public function destroy(TeamMember $teamMember)
    {
        $teamMember->update(['status' => 4, 'slug' => $teamMember->slug . '-deleted-' . $teamMember->id]);

        return redirect()->route('admin.team-members.index')->with('success', 'Team member removed.');
    }

    public function reorder(Request $request)
    {
        foreach ((array) $request->input('ids', []) as $i => $id) {
            TeamMember::whereKey($id)->update(['sort_order' => $i + 1]);
        }

        return response()->json(['message' => 'Order saved.']);
    }

    protected function validated(Request $request, ?TeamMember $member = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'role_title' => 'nullable|string|max:191',
            'email' => 'nullable|email|max:191',
            'biography' => 'nullable|string|max:3000',
            'linkedin' => 'nullable|url|max:255',
            'x' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'sort_order' => 'nullable|integer',
            'status' => 'required|in:0,1',
        ] + $this->imageRules('photo'));

        $slug = Str::slug($data['name']);
        $base = $slug; $i = 2;
        while (TeamMember::withDeleted()->where('slug', $slug)->when($member, fn ($q) => $q->where('id', '!=', $member->id))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return [
            'name' => $data['name'],
            'slug' => $slug,
            'role_title' => $data['role_title'] ?? null,
            'email' => $data['email'] ?? null,
            'biography' => $data['biography'] ?? null,
            'social_links' => array_filter([
                'linkedin' => $data['linkedin'] ?? null,
                'x' => $data['x'] ?? null,
                'instagram' => $data['instagram'] ?? null,
            ]),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'status' => $data['status'],
        ];
    }
}
