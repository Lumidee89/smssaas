<?php

namespace App\Actions\Saas;

use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\DB;

class ActivateSubscriptionPayment
{
    public function execute(SubscriptionPayment $payment, array $providerData): bool
    {
        return DB::transaction(function () use ($payment, $providerData) {
            $payment = SubscriptionPayment::lockForUpdate()->findOrFail($payment->id);
            if ($payment->status === 'paid') {
                return true;
            }
            $expected = (int) round((float) $payment->amount * 100);
            if (($providerData['status'] ?? null) !== 'success' || (int) ($providerData['amount'] ?? 0) !== $expected) {
                return false;
            }

            $payment->update(['status' => 'paid', 'provider_reference' => (string) ($providerData['reference'] ?? $payment->reference), 'paid_at' => now(), 'metadata' => array_merge($payment->metadata ?? [], ['provider_data' => $providerData])]);
            $subscription = $payment->subscription()->lockForUpdate()->firstOrFail();
            $billingCycle = ($payment->metadata['billing_cycle'] ?? 'monthly') === 'yearly' ? 'yearly' : 'monthly';
            $periodEnd = $billingCycle === 'yearly' ? now()->addYear() : now()->addMonth();
            $subscription->update(['status' => 'active', 'starts_at' => now(), 'trial_ends_at' => null, 'current_period_ends_at' => $periodEnd]);
            $subscription->school()->update(['is_active' => true, 'subscription_end_date' => $periodEnd]);

            return true;
        });
    }
}
