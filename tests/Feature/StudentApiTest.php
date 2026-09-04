<?php

namespace Tests\Feature;

use App\Models\Classes;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_authenticates_and_reads_only_linked_profile(): void
    {
        $school = School::factory()->create();
        $class = Classes::create(['school_id' => $school->id, 'name' => 'JSS 3', 'capacity' => 30]);
        $student = Student::factory()->create(['school_id' => $school->id, 'class_id' => $class->id]);
        User::factory()->create(['school_id' => $school->id, 'student_id' => $student->id, 'role' => 'student', 'email' => 'learner@example.test', 'password' => Hash::make('StudentPass123!')]);

        $response = $this->postJson('/api/v1/student/login', ['email' => 'learner@example.test', 'password' => 'StudentPass123!', 'device_name' => 'test-phone'])->assertOk();
        $this->withToken($response->json('data.token'))->getJson('/api/v1/student/dashboard')->assertOk()
            ->assertJsonPath('data.student.id', $student->id)->assertJsonPath('data.student.class', $class->full_name);
    }

    public function test_parent_token_cannot_open_student_dashboard(): void
    {
        $school = School::factory()->create();
        $parent = User::factory()->create(['school_id' => $school->id, 'role' => 'parent']);
        $token = $parent->createToken('parent')->plainTextToken;
        $this->withToken($token)->getJson('/api/v1/student/dashboard')->assertForbidden();
    }
}
