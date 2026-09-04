<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ParentAccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_admin_creates_parent_and_links_only_own_student(): void
    {
        $school = School::factory()->create();
        $otherSchool = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $student = Student::factory()->create(['school_id' => $school->id]);
        $foreignStudent = Student::factory()->create(['school_id' => $otherSchool->id]);

        $this->actingAs($admin)->post(route('parents.store'), [
            'name' => 'Ada Parent', 'phone' => '+2348012345678', 'email' => 'ada.parent@example.test',
            'password' => 'secure-password', 'password_confirmation' => 'secure-password',
            'relationship' => 'mother', 'student_ids' => [$student->id],
        ])->assertSessionHasNoErrors();

        $parent = User::where('email', 'ada.parent@example.test')->firstOrFail();
        $this->assertSame($school->id, $parent->school_id);
        $this->assertTrue($parent->children->contains($student));

        $this->actingAs($admin)->post(route('parents.store'), [
            'name' => 'Bad Parent', 'phone' => '+2348099999999', 'email' => 'bad@example.test',
            'password' => 'secure-password', 'password_confirmation' => 'secure-password',
            'relationship' => 'guardian', 'student_ids' => [$foreignStudent->id],
        ])->assertSessionHasErrors('student_ids.0');
    }

    public function test_parent_logs_in_with_email_and_changes_password(): void
    {
        $school = School::factory()->create();
        $parent = User::factory()->create(['school_id' => $school->id, 'role' => 'parent', 'email' => 'parent@example.test', 'password' => Hash::make('old-password')]);

        $login = $this->postJson('/api/v1/parent/login', ['email' => 'PARENT@example.test', 'password' => 'old-password', 'device_name' => 'iPhone'])->assertOk();
        $token = $login->json('data.token');
        $this->withToken($token)->putJson('/api/v1/parent/password', ['current_password' => 'old-password', 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertOk();
        $this->assertTrue(Hash::check('new-password', $parent->fresh()->password));
        $this->postJson('/api/v1/parent/login', ['email' => 'parent@example.test', 'password' => 'old-password', 'device_name' => 'iPhone'])->assertStatus(422);
    }

    public function test_school_admin_can_edit_parent_and_add_another_student(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $parent = User::factory()->create(['school_id' => $school->id, 'role' => 'parent', 'phone' => '+2348011122233']);
        $firstChild = Student::factory()->create(['school_id' => $school->id, 'parent_id' => $parent->id]);
        $secondChild = Student::factory()->create(['school_id' => $school->id]);
        $parent->children()->attach($firstChild, ['school_id' => $school->id, 'relationship' => 'mother']);

        $this->actingAs($admin)->put(route('parents.update', $parent), [
            'name' => 'Updated Parent', 'phone' => $parent->phone, 'email' => $parent->email,
            'relationship' => 'mother', 'student_ids' => [$firstChild->id, $secondChild->id],
        ])->assertSessionHasNoErrors();

        $this->assertSame('Updated Parent', $parent->fresh()->name);
        $this->assertEqualsCanonicalizing([$firstChild->id, $secondChild->id], $parent->children()->pluck('students.id')->all());
        $this->assertSame($parent->id, $secondChild->fresh()->parent_id);
    }
}
