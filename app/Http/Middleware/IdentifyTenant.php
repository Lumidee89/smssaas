<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Tenancy\CurrentTenant;
use App\Tenancy\TenantSchemaManager;
use Closure;
use Illuminate\Http\Request;

class IdentifyTenant
{
    public function __construct(private TenantSchemaManager $schemas) {}

    public function handle(Request $request, Closure $next)
    {
        $host = strtolower(preg_replace('/:\d+$/', '', $request->getHost()));
        $base = strtolower((string) config('tenancy.base_domain'));
        $school = null;
        try {
            $school = School::whereNotNull('domain_verified_at')->whereRaw('LOWER(custom_domain) = ?', [$host])->first();
            if (! $school && $base && str_ends_with($host, '.'.$base)) {
                $subdomain = substr($host, 0, -strlen('.'.$base));
                if (! str_contains($subdomain, '.') && ! in_array($subdomain, config('tenancy.reserved_subdomains'), true)) {
                    $school = School::where('subdomain', $subdomain)->first();
                }
            }
        } catch (\Throwable $error) {
            report($error);
        }
        app()->instance(CurrentTenant::class, new CurrentTenant($school));
        if ($school) $this->schemas->activate($school);
        try {
            return $next($request);
        } finally {
            $this->schemas->reset();
        }
    }
}
