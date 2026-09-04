<?php

namespace App\Notifications;

use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SchoolAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly array $payload, public readonly bool $email = false)
    {
        $this->onQueue('notifications')->afterCommit();
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if ($this->email && filled($notifiable->email)) {
            $channels[] = 'mail';
        }
        if (config('services.firebase.project_id')) {
            $channels[] = FcmChannel::class;
        }

        return $channels;
    }

    public function toArray(object $notifiable): array
    {
        return $this->payload;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->payload['title'])
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->payload['body'])
            ->action('Open SchoolOS', url('/dashboard'));
    }
}
