<?php

return ['default' => env('PAYMENT_GATEWAY', 'paystack'), 'paystack' => ['base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'), 'secret_key' => env('PAYSTACK_SECRET_KEY'), 'callback_url' => env('PAYSTACK_CALLBACK_URL')]];
