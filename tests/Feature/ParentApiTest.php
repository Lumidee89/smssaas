<?php

namespace Tests\Feature;

use App\Contracts\OtpSender;
use App\Models\Classes;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\Payment;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_request_otp_login_and_read_only_linked_children(): void
    {
        $sender = new class implements OtpSender
        {
            public ?string $code = null;

            public function send(string $phone, string $code): void
            {
                $this->code = $code;
            }
        };
        $this->app->instance(OtpSender::class, $sender);
        $school = School::factory()->create();
        $parent = User::factory()->create(['school_id' => $school->id, 'role' => 'parent', 'phone' => '+234 800 111 2222']);
        $class = Classes::create(['school_id' => $school->id, 'name' => 'JSS 1', 'capacity' => 30]);
        $child = Student::factory()->create(['school_id' => $school->id, 'class_id' => $class->id, 'parent_id' => $parent->id]);
        $other = Student::factory()->create(['school_id' => $school->id, 'class_id' => $class->id, 'parent_id' => null]);
        $parent->children()->attach($child, ['school_id' => $school->id, 'relationship' => 'father', 'is_primary' => true]);

        $this->postJson('/api/v1/parent/otp/request', ['phone' => '+234 800 111 2222'])->assertOk();
        $this->assertNotNull($sender->code);
        $login = $this->postJson('/api/v1/parent/login', ['phone' => '+2348001112222', 'code' => $sender->code, 'device_name' => 'Test phone'])->assertOk()->assertJsonPath('data.user.id', $parent->id);
        $token = $login->json('data.token');
        $this->withToken($token)->getJson('/api/v1/parent/dashboard')->assertOk()->assertJsonPath('data.children.0.id', $child->id);
        $this->withToken($token)->getJson("/api/v1/parent/children/{$other->id}/progress")->assertForbidden();
        $this->withToken($token)->postJson('/api/v1/parent/logout')->assertOk();
    }

    public function test_otp_request_does_not_disclose_unknown_phone_numbers(): void
    {
        $sender = new class implements OtpSender
        {
            public bool $sent = false;

            public function send(string $phone, string $code): void
            {
                $this->sent = true;
            }
        };
        $this->app->instance(OtpSender::class, $sender);
        $this->postJson('/api/v1/parent/otp/request', ['phone' => '+2348000000000'])->assertOk()->assertJsonStructure(['message']);
        $this->assertFalse($sender->sent);
    }

    public function test_parent_calendar_only_returns_own_school_academic_setup(): void
    {
        $school = School::factory()->create(['name' => 'Own School']);
        $otherSchool = School::factory()->create();
        $parent = User::factory()->create(['school_id' => $school->id, 'role' => 'parent']);
        $year = AcademicYear::create(['school_id' => $school->id, 'name' => '2026/2027', 'starts_on' => '2026-09-01', 'ends_on' => '2027-07-31', 'is_current' => true]);
        AcademicTerm::create(['school_id' => $school->id, 'academic_year_id' => $year->id, 'name' => 'First Term', 'sequence' => 1, 'starts_on' => '2026-09-01', 'ends_on' => '2026-12-18', 'is_current' => true]);
        AcademicYear::create(['school_id' => $otherSchool->id, 'name' => 'Private Year', 'starts_on' => '2026-01-01', 'ends_on' => '2026-12-31']);
        $token = $parent->createToken('calendar-test', ['parent:read'])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/parent/calendar')
            ->assertOk()
            ->assertJsonPath('data.school', 'Own School')
            ->assertJsonPath('data.years.0.name', '2026/2027')
            ->assertJsonPath('data.years.0.terms.0.name', 'First Term')
            ->assertJsonMissing(['name' => 'Private Year']);
    }

    public function test_parent_can_only_read_linked_fees_and_participating_conversations(): void
    {
        $school = School::factory()->create();
        $parent = User::factory()->create(['school_id' => $school->id, 'role' => 'parent']);
        $administrator = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $teacher = User::factory()->create(['school_id' => $school->id, 'role' => 'teacher']);
        $child = Student::factory()->create(['school_id' => $school->id, 'parent_id' => $parent->id]);
        $other = Student::factory()->create(['school_id' => $school->id]);
        $parent->children()->attach($child, ['school_id' => $school->id, 'relationship' => 'mother']);
        $own = Payment::create(['school_id' => $school->id, 'student_id' => $child->id, 'parent_id' => $parent->id, 'invoice_number' => 'INV-OWN', 'amount' => 5000, 'payment_type' => 'tuition', 'term' => 'First Term', 'status' => 'pending']);
        $foreign = Payment::create(['school_id' => $school->id, 'student_id' => $other->id, 'parent_id' => $parent->id, 'invoice_number' => 'INV-OTHER', 'amount' => 9000, 'payment_type' => 'tuition', 'term' => 'First Term', 'status' => 'pending']);
        $token = $parent->createToken('test', ['parent:read'])->plainTextToken;
        $this->withToken($token)->getJson('/api/v1/parent/payments')->assertOk()->assertJsonPath('data.0.id', $own->id)->assertJsonMissing(['invoice_number' => 'INV-OTHER']);
        $this->withToken($token)->getJson("/api/v1/parent/payments/{$foreign->id}")->assertForbidden();
        $created = $this->withToken($token)->postJson('/api/v1/parent/conversations', ['recipient_id' => $teacher->id, 'subject' => 'Homework', 'body' => 'Please clarify the assignment.'])->assertCreated();
        $conversationId = $created->json('data.id');
        $this->assertDatabaseHas('conversation_user', ['conversation_id' => $conversationId, 'user_id' => $teacher->id]);
        $this->assertDatabaseMissing('conversation_user', ['conversation_id' => $conversationId, 'user_id' => $administrator->id]);
        $this->withToken($token)->getJson('/api/v1/parent/conversations')->assertOk()->assertJsonPath('data.0.id', $conversationId);
        $this->withToken($token)->postJson("/api/v1/parent/conversations/{$conversationId}/messages", ['body' => 'Thank you.'])->assertCreated();
    }
}
