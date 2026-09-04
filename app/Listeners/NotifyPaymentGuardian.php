<?php

namespace App\Listeners;

use App\Events\PaymentCompleted;
use App\Services\Notifications\ParentNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyPaymentGuardian implements ShouldQueue
{
    public string $queue = 'notifications';
    public function __construct(private ParentNotificationService $notifications) {}
    public function handle(PaymentCompleted $event): void
    {
        $payment = $event->payment->loadMissing(['parent', 'school']);
        if ($payment->parent) $this->notifications->paymentReceived($payment->parent, $payment->provider_reference, ($payment->school?->currency ?? 'NGN').' '.number_format((float) $payment->amount, 2));
    }
}
