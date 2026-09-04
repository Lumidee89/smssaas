<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.', 'code' => 'UNAUTHENTICATED'], 401);
            }

            return redirect()->route('login');
        }

        if ($user->role !== 'super_admin' && ! in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden.', 'code' => 'FORBIDDEN'], 403);
            }
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
