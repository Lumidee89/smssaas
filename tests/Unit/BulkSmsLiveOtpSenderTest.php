<?php

namespace Tests\Unit;

use App\Services\Otp\BulkSmsLiveOtpSender;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BulkSmsLiveOtpSenderTest extends TestCase
{
    public function test_it_uses_api_key_endpoint_and_normalizes_nigerian_recipient(): void
    {
        config(['services.bulksmslive' => ['base_url' => 'https://api.bulksmslive.com', 'api_key' => 'secret-key', 'sender_name' => 'SchoolOS', 'force_dnd' => true, 'default_country_code' => '234']]);
        Http::fake(['api.bulksmslive.com/*' => Http::response(['status' => 1, 'msg' => 'sent', 'msgid' => 'abc'], 200)]);
        app(BulkSmsLiveOtpSender::class)->send('0801 234 5678', '123456');
        Http::assertSent(fn ($request) => $request->url() === 'https://api.bulksmslive.com/v2/app/sendsms' && $request->hasHeader('Authorization', 'Bearer secret-key') && $request['recipients'] === '2348012345678' && $request['sender_name'] === 'SchoolOS' && $request['forcednd'] === 1);
    }
}
