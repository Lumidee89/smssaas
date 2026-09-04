<?php

namespace App\Listeners;

use App\Events\AnnouncementCreated;
use App\Services\Notifications\ParentNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAnnouncementAudience implements ShouldQueue
{
    public string $queue = 'notifications';
    public function __construct(private ParentNotificationService $notifications) {}
    public function handle(AnnouncementCreated $event): void
    {
        $item = $event->announcement;
        $this->notifications->schoolAudience($item->school_id, $item->audience, ['kind' => 'announcement', 'title' => $item->title, 'body' => $item->body, 'announcement_id' => $item->id]);
    }
}
