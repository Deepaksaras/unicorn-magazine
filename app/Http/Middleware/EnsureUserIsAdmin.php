<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only active users with a CMS role may open /admin.
 * (Anyone can register through Breeze, so "auth" alone is not enough.)
 */
class EnsureUserIsAdmin
{
    public const ROLES = ['super-admin', 'admin', 'editor', 'author'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->guest(route('admin.login'));
        }

        $allowed = (int) $user->status === 1
            && $user->roles()->whereIn('slug', self::ROLES)->exists();

        if (!$allowed) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Your account does not have access to the admin panel.']);
        }

        return $next($request);
    }
}
