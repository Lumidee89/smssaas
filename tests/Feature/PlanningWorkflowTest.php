<?php

namespace Tests\Feature;

use App\Models\{Classes, School, Student, Subject, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanningWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_creates_published_homework_and_parent_event_visible_to_linked_parent(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->create(['school_id' => $school->id, 'role' => 'teacher']);
        $parent = User::factory()->create(['school_id' => $school->id, 'role' => 'parent']);
        $class = Classes::create(['school_id' => $school->id, 'name' => 'JSS 2', 'capacity' => 30]);
        $subject = Subject::create(['school_id' => $school->id, 'name' => 'Mathematics', 'code' => 'MTH']);
        $student = Student::factory()->create(['school_id' => $school->id, 'class_id' => $class->id, 'parent_id' => $parent->id]);
        $parent->children()->attach($student, ['school_id' => $school->id, 'relationship' => 'guardian']);

        $this->actingAs($teacher)->post(route('planning.homework.store'), [
            'class_id' => $class->id, 'subject_id' => $subject->id, 'title' => 'Algebra practice',
            'instructions' => 'Complete questions 1–10.', 'due_at' => now()->addDays(2)->format('Y-m-d H:i:s'), 'publish_now' => '1',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->post(route('planning.events.store'), [
            'title' => 'Parents meeting', 'description' => 'Term review.', 'audience' => 'parents',
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'), 'location' => 'Main hall',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertDatabaseHas('homework_assignments', ['school_id' => $school->id, 'title' => 'Algebra practice']);
        $this->assertDatabaseHas('school_events', ['school_id' => $school->id, 'title' => 'Parents meeting']);
        $this->app['auth']->guard('web')->logout();
        $token = $parent->createToken('parent-dashboard')->plainTextToken;
        $this->withToken($token)->getJson('/api/v1/parent/dashboard')->assertOk()
            ->assertJsonPath('data.homework.0.title', 'Algebra practice')
            ->assertJsonPath('data.homework.0.class_id', $class->id)
            ->assertJsonPath('data.upcoming_events.0.title', 'Parents meeting');
    }

    public function test_draft_or_other_class_homework_is_not_visible_to_parent(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->create(['school_id' => $school->id, 'role' => 'teacher']);
        $parent = User::factory()->create(['school_id' => $school->id, 'role' => 'parent']);
        $ownClass = Classes::create(['school_id' => $school->id, 'name' => 'JSS 1', 'capacity' => 30]);
        $otherClass = Classes::create(['school_id' => $school->id, 'name' => 'JSS 3', 'capacity' => 30]);
        $subject = Subject::create(['school_id' => $school->id, 'name' => 'English', 'code' => 'ENG']);
        $child = Student::factory()->create(['school_id' => $school->id, 'class_id' => $ownClass->id]);
        $parent->children()->attach($child, ['school_id' => $school->id, 'relationship' => 'guardian']);
        foreach ([[$ownClass, false], [$otherClass, true]] as [$class, $published]) {
            $this->actingAs($teacher)->post(route('planning.homework.store'), ['class_id' => $class->id, 'subject_id' => $subject->id, 'title' => $published ? 'Wrong class' : 'Draft', 'instructions' => 'Hidden', 'due_at' => now()->addDay(), 'publish_now' => $published ? '1' : '0']);
        }
        $this->app['auth']->guard('web')->logout();
        $token = $parent->createToken('parent-dashboard')->plainTextToken;
        $this->withToken($token)->getJson('/api/v1/parent/dashboard')->assertOk()->assertJsonCount(0, 'data.homework');
    }
}
