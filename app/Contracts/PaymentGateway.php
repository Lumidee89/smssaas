<?php

namespace App\Contracts;

interface PaymentGateway
{
    public function initialize(string $email, float $amount, string $currency, string $reference, array $metadata = []): array;
}
