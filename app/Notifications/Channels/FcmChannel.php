<?php

namespace App\Notifications\Channels;

use App\Contracts\PushSender;
use App\Models\DeviceToken;
use Illuminate\Notifications\Notification;

class FcmChannel
{
    public function __construct(private readonly PushSender $sender) {}

    public function send(object $notifiable, Notification $notification): void
    {
        $payload = $notification->toArray($notifiable);
        DeviceToken::where('user_id', $notifiable->getKey())->eachById(function (DeviceToken $device) use ($payload): void {
            $valid = $this->sender->send(
                $device->token,
                (string) ($payload['title'] ?? 'SchoolOS update'),
                (string) ($payload['body'] ?? ''),
                collect($payload)->except(['title', 'body'])->all(),
            );
            if (! $valid) {
                $device->delete();
            }
        });
    }
}
