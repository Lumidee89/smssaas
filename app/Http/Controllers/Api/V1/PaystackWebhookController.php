<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Finance\HandlePaystackWebhook;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaystackWebhookController extends Controller
{
    public function __invoke(Request $request, HandlePaystackWebhook $handler): Response
    {
        $secret = (string) config('payment.paystack.secret_key');
        $signature = (string) $request->header('X-Paystack-Signature');
        if (! $secret || ! hash_equals(hash_hmac('sha512', $request->getContent(), $secret), $signature)) {
            return response('', 401);
        }$handler->execute($request->json()->all());

        return response('', 200);
    }
}
