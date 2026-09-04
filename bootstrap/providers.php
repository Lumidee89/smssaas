<?php

use App\Providers\AppServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\PaymentServiceProvider;
use App\Providers\SchoolServiceProvider;

return [
    AppServiceProvider::class,
    HorizonServiceProvider::class,
    PaymentServiceProvider::class,
    SchoolServiceProvider::class,
];
