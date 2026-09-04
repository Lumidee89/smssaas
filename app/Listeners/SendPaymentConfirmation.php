<?php

namespace App\Listeners;

use App\Events\PaymentProcessed;

class SendPaymentConfirmation
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentProcessed $event): void
    {
        //
    }
}
