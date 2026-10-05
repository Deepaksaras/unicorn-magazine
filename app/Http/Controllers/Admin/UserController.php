<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesImages;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    use HandlesImages;

    public function index(Request $request)
    {
        $users = User::with('roles:id,name,slug')
            ->withCount('posts')
            ->where('status', '!=', 4)
            ->when($request->q, fn ($q, $t) => $q->where(fn ($w) => $w->where('name', 'like', "%{$t}%")->orWhere('email', 'like', "%{$t}%")))
            ->when($request->role, fn ($q, $r) => $q->whereHas('roles', fn ($w) => $w->where('roles.id', $r)))
            ->orderBy('name')
            ->paginate(20)->withQueryString();

        return view('admin.users.index', ['users' => $users, 'roles' => Role::orderBy('id')->pluck('name', 'id')]);
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User(['status' => 1]), 'roles' => Role::orderBy('id')->pluck('name', 'id'), 'currentRole' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['avatar'] = $this->resolveImage($request, 'avatar', null, 'avatars', 600);
        $user = User::create($data);
        $user->roles()->sync($request->filled('role') ? [$request->role] : []);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function show(User $user)
    {
        return redirect()->route('admin.users.edit', $user);
    }

    public function edit(User $user)
    {
        return view('admin.users.form', [
            'user' => $user,
            'roles' => Role::orderBy('id')->pluck('name', 'id'),
            'currentRole' => $user->roles()->orderBy('roles.id')->value('roles.id'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);
        $data['avatar'] = $this->resolveImage($request, 'avatar', $user->avatar, 'avatars', 600);

        if ((int) $user->id === (int) auth()->id()) {
            // never lock yourself out
            $data['status'] = 1;
        }

        $user->update($data);

        if ((int) $user->id !== (int) auth()->id() || $request->filled('role')) {
            $user->roles()->sync($request->filled('role') ? [$request->role] : []);
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        if ((int) $user->id === (int) auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->update(['status' => 4]);
        $user->roles()->detach();

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    protected function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'phone' => 'nullable|string|max:30',
            'designation' => 'nullable|string|max:191',
            'bio' => 'nullable|string|max:2000',
            'role' => 'nullable|exists:roles,id',
            'status' => 'required|in:0,1',
        ] + $this->imageRules('avatar'));

        if (empty($data['password'])) {
            unset($data['password']);
        }

        unset($data['role'], $data['password_confirmation'], $data['avatar'], $data['avatar_file']);

        return $data;
    }
}
