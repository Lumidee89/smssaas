<?php

namespace Tests\Feature;

use App\Models\Classes;
use App\Models\GradingPolicy;
use App\Models\School;
use App\Models\Student;
use App\Models\Transcript;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OperationsWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_bulk_mark_attendance_and_publish_announcement(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $class = Classes::create(['school_id' => $school->id, 'name' => 'JSS 2', 'capacity' => 30]);
        $student = Student::factory()->create(['school_id' => $school->id, 'class_id' => $class->id]);
        $this->actingAs($admin)->get(route('attendance.index', ['class_id' => $class->id]))->assertOk();
        $this->actingAs($admin)->post(route('attendance.store'), ['class_id' => $class->id, 'date' => now()->toDateString(), 'attendance' => [$student->id => 'present']])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('attendance_records', ['student_id' => $student->id, 'status' => 'present']);
        $this->actingAs($admin)->post(route('announcements.store'), ['title' => 'Open Day', 'body' => 'Families are invited.', 'audience' => 'parents', 'publish_now' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('announcements', ['title' => 'Open Day', 'audience' => 'parents']);
    }

    public function test_public_can_verify_issued_transcript(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $student = Student::factory()->create(['school_id' => $school->id]);
        $policy = GradingPolicy::create(['school_id' => $school->id, 'name' => 'Standard', 'version' => 1]);
        $transcript = Transcript::create(['public_id' => (string) Str::uuid(), 'school_id' => $school->id, 'student_id' => $student->id, 'grading_policy_id' => $policy->id, 'cgpa' => 3.5, 'snapshot' => ['student' => ['name' => $student->full_name, 'admission_number' => $student->admission_number], 'terms' => []], 'status' => 'issued', 'issued_by' => $admin->id, 'issued_at' => now()]);
        $this->get(route('transcripts.verify', $transcript->public_id))->assertOk()->assertSee('VERIFIED')->assertSee($student->full_name);
    }
}
