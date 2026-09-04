<?php

namespace App\Services\Saas;

use App\Models\School;

class EntitlementService
{
    public function allows(School $school, string $feature): bool
    {
        $subscription = $school->subscriptions()->with('plan')->whereIn('status', ['trialing', 'active'])->latest('id')->first();
        if (! $subscription) {
            return true;
        }$features = $subscription->plan->features ?? [];

        return in_array('all', $features, true) || in_array($feature, $features, true);
    }

    public function assertCapacity(School $school, string $resource, int $current): void
    {
        $subscription = $school->subscriptions()->with('plan')->whereIn('status', ['trialing', 'active'])->latest('id')->first();
        if (! $subscription) {
            return;
        }$limit = $resource === 'students' ? $subscription->plan->max_students : $subscription->plan->max_staff;
        abort_if($limit !== null && $current >= $limit, 422, "Your {$subscription->plan->name} plan {$resource} limit has been reached.");
    }
}
