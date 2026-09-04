<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Tenancy\CurrentTenant;

class SchoolMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.', 'code' => 'UNAUTHENTICATED'], 401);
            }

            return redirect()->route('login');
        }

        // Super admin bypass
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        $tenant = app(CurrentTenant::class);
        if ($tenant->resolved() && $tenant->id() !== $user->school_id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account does not belong to this institution.', 'code' => 'TENANT_MISMATCH'], 403);
            }
            Auth::logout();
            return redirect()->route('login')->with('error', 'Use your institution workspace to sign in.');
        }

        if (! $user->school) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Account is not assigned to a school.', 'code' => 'SCHOOL_REQUIRED'], 403);
            }
            Auth::logout();

            return redirect()->route('login')->with('error', 'Your account is not assigned to a school.');
        }

        // Check if user's school is active
        if (! $user->school->isSubscriptionActive()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'School subscription is inactive.', 'code' => 'SUBSCRIPTION_INACTIVE'], 403);
            }
            Auth::logout();

            return redirect()->route('login')->with('error', 'Your school subscription has expired.');
        }

        return $next($request);
    }
}
