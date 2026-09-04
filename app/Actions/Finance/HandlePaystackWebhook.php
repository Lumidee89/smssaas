<?php

namespace App\Actions\Finance;

use App\Events\PaymentCompleted;
use App\Actions\Saas\ActivateSubscriptionPayment;
use App\Models\Payment;
use App\Models\SubscriptionPayment;
use App\Services\Notifications\ParentNotificationService;
use Illuminate\Support\Facades\DB;

class HandlePaystackWebhook
{
    public function __construct(private readonly ParentNotificationService $notifications, private readonly ActivateSubscriptionPayment $activateSubscription) {}

    public function execute(array $payload): void
    {
        if (($payload['event'] ?? null) !== 'charge.success') {
            return;
        }
        $data = $payload['data'] ?? [];
        $reference = $data['reference'] ?? null;
        if (! $reference) {
            return;
        }

        if ($subscriptionPayment = SubscriptionPayment::where('reference', $reference)->first()) {
            $this->activateSubscription->execute($subscriptionPayment, $data);

            return;
        }

        $settledPayment = DB::transaction(function () use ($data, $reference): ?Payment {
            $payment = Payment::where('provider', 'paystack')->where('provider_reference', $reference)
                ->lockForUpdate()->first();
            if (! $payment || $payment->status === 'paid') {
                return null;
            }
            $expected = (int) round((float) $payment->amount * 100);
            if (($data['status'] ?? null) !== 'success' || (int) ($data['amount'] ?? 0) !== $expected) {
                return null;
            }

            $payment->update([
                'status' => 'paid',
                'transaction_id' => (string) ($data['id'] ?? $reference),
                'payment_method' => $data['channel'] ?? 'card',
                'paid_at' => now(),
                'provider_payload' => $data,
            ]);

            if ($payment->feeInvoice) {
                $invoice = $payment->feeInvoice()->lockForUpdate()->first();
                $paid = (float) $invoice->payments()->where('status', 'paid')->sum('amount');
                $applied = min($paid, (float) $invoice->amount_due);
                $invoice->update(['amount_paid' => $applied, 'status' => $applied >= (float) $invoice->amount_due ? 'paid' : 'partial']);
            }
            $payment->installmentSchedule?->update(['status' => 'paid']);

            return $payment->fresh(['parent', 'school']);
        });

        if ($settledPayment) PaymentCompleted::dispatch($settledPayment);
    }
}
