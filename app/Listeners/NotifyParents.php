<?php

namespace App\Listeners;

use App\Events\ResultPublished;

class NotifyParents
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
    public function handle(ResultPublished $event): void
    {
        //
    }
}
