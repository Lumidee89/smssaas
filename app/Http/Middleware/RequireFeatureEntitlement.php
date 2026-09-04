<?php

namespace App\Http\Middleware;

use App\Services\Saas\EntitlementService;
use Closure;
use Illuminate\Http\Request;

class RequireFeatureEntitlement
{
    public function __construct(private readonly EntitlementService $entitlements) {}

    public function handle(Request $request, Closure $next, string $feature)
    {
        $school = $request->user()?->school;
        abort_unless($school && $this->entitlements->allows($school, $feature), 402, "The {$feature} module is not included in this subscription plan.");

        return $next($request);
    }
}
