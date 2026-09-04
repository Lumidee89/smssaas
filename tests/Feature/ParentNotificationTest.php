<?php

namespace Tests\Feature;

use App\Contracts\PushSender;
use App\Models\DeviceToken;
use App\Models\School;
use App\Models\User;
use App\Notifications\SchoolAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_reads_notifications_and_manages_device_registration(): void
    {
        $school = School::factory()->create();
        $parent = User::factory()->create(['school_id' => $school->id, 'role' => 'parent']);
        $token = $parent->createToken('test', ['parent:read'])->plainTextToken;
        $parent->notify(new SchoolAlert([
            'kind' => 'announcement',
            'title' => 'School closes early',
            'body' => 'Collection starts at 1 PM.',
        ]));
        $notification = $parent->notifications()->firstOrFail();

        $this->withToken($token)->getJson('/api/v1/parent/notifications')
            ->assertOk()->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.title', 'School closes early');
        $this->withToken($token)->patchJson("/api/v1/parent/notifications/{$notification->id}/read")
            ->assertOk();
        $this->assertNotNull($notification->fresh()->read_at);

        $this->withToken($token)->postJson('/api/v1/parent/devices', [
            'token' => 'fcm-test-token', 'platform' => 'android',
        ])->assertCreated();
        $this->assertDatabaseHas('device_tokens', [
            'school_id' => $school->id, 'user_id' => $parent->id, 'token' => 'fcm-test-token',
        ]);
        $this->withToken($token)->deleteJson('/api/v1/parent/devices', ['token' => 'fcm-test-token'])
            ->assertOk();
        $this->assertDatabaseMissing('device_tokens', ['token' => 'fcm-test-token']);
    }

    public function test_parent_cannot_mark_another_users_notification_as_read(): void
    {
        $school = School::factory()->create();
        $parent = User::factory()->create(['school_id' => $school->id, 'role' => 'parent']);
        $other = User::factory()->create(['school_id' => $school->id, 'role' => 'parent']);
        $other->notify(new SchoolAlert(['kind' => 'test', 'title' => 'Private', 'body' => 'Private']));
        $notification = $other->notifications()->firstOrFail();
        $token = $parent->createToken('test', ['parent:read'])->plainTextToken;

        $this->withToken($token)->patchJson("/api/v1/parent/notifications/{$notification->id}/read")
            ->assertNotFound();
    }

    public function test_fcm_channel_delivers_payload_and_removes_invalid_token(): void
    {
        config(['services.firebase.project_id' => 'schoolos-test']);
        $sender = new class implements PushSender
        {
            public array $sent = [];

            public function send(string $token, string $title, string $body, array $data = []): bool
            {
                $this->sent[] = compact('token', 'title', 'body', 'data');

                return false;
            }
        };
        $this->app->instance(PushSender::class, $sender);
        $school = School::factory()->create();
        $parent = User::factory()->create(['school_id' => $school->id, 'role' => 'parent']);
        DeviceToken::create([
            'school_id' => $school->id,
            'user_id' => $parent->id,
            'token' => 'expired-fcm-token',
            'platform' => 'android',
            'last_seen_at' => now(),
        ]);

        $parent->notify(new SchoolAlert([
            'kind' => 'result', 'title' => 'Result ready', 'body' => 'Open your report.',
        ]));

        $this->assertSame('Result ready', $sender->sent[0]['title']);
        $this->assertSame('result', $sender->sent[0]['data']['kind']);
        $this->assertDatabaseMissing('device_tokens', ['token' => 'expired-fcm-token']);
    }
}
