<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('role');

        // 🌟 FIX: Admin has full system access, so grant access automatically!
        if ($user->role && $user->role->role_name === 'Admin') {
            return $next($request);
        }

        // For other users, check if their role is in the allowed $roles list
        if (!$user->role || !in_array($user->role->role_name, $roles)) {
            abort(403, 'You do not have access to this page.');
        }

        return $next($request);
    }
}
