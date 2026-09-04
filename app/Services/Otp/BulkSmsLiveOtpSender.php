<?php

namespace App\Services\Otp;

use App\Contracts\OtpSender;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BulkSmsLiveOtpSender implements OtpSender
{
    public function send(string $phone, string $code): void
    {
        $key = config('services.bulksmslive.api_key');
        if (! $key) {
            throw new RuntimeException('BulkSMSLive API key is not configured.');
        }
        $response = Http::withToken($key)->acceptJson()->timeout(15)->retry(2, 300)
            ->post(rtrim(config('services.bulksmslive.base_url'), '/').'/v2/app/sendsms', [
                'message' => "Your Plus36 SchoolOS code is {$code}. It expires in 10 minutes.",
                'sender_name' => str(config('services.bulksmslive.sender_name', 'SchoolOS'))->limit(11, ''),
                'recipients' => $this->recipient($phone),
                'forcednd' => config('services.bulksmslive.force_dnd', true) ? 1 : 0,
            ])->throw();
        if ((string) data_get($response->json(), 'status') !== '1') {
            throw new RuntimeException('BulkSMSLive rejected the message: '.(data_get($response->json(), 'msg') ?: 'unknown provider error'));
        }
    }

    private function recipient(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '0')) {
            $digits = config('services.bulksmslive.default_country_code', '234').substr($digits, 1);
        }
        if (! preg_match('/^[1-9][0-9]{7,14}$/', $digits)) {
            throw new RuntimeException('Phone number is invalid for SMS delivery.');
        }

        return $digits;
    }
}
