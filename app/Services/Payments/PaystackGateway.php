<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackGateway implements PaymentGateway
{
    public function initialize(string $email, float $amount, string $currency, string $reference, array $metadata = []): array
    {
        $secret = config('payment.paystack.secret_key');
        if (! $secret) {
            throw new RuntimeException('Paystack secret key is not configured.');
        }$callbackUrl = $metadata['callback_url'] ?? config('payment.paystack.callback_url');
        unset($metadata['callback_url']);
        $response = Http::baseUrl(config('payment.paystack.base_url'))->withToken($secret)->acceptJson()->timeout(20)->retry(2, 300)->post('/transaction/initialize', ['email' => $email, 'amount' => (int) round($amount * 100), 'currency' => $currency, 'reference' => $reference, 'callback_url' => $callbackUrl, 'metadata' => $metadata])->throw()->json();
        if (! ($response['status'] ?? false)) {
            throw new RuntimeException($response['message'] ?? 'Payment initialization failed.');
        }

        return $response['data'];
    }

    public function verify(string $reference): array
    {
        $secret = config('payment.paystack.secret_key');
        if (! $secret) {
            throw new RuntimeException('Paystack secret key is not configured.');
        }
        $response = Http::baseUrl(config('payment.paystack.base_url'))->withToken($secret)->acceptJson()->timeout(20)->retry(2, 300)->get('/transaction/verify/'.rawurlencode($reference))->throw()->json();
        if (! ($response['status'] ?? false)) {
            throw new RuntimeException($response['message'] ?? 'Payment verification failed.');
        }

        return $response['data'];
    }
}
