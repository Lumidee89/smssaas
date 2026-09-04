<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\Classes;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_enters_and_submits_scores_then_admin_approves(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $teacher = User::factory()->create(['school_id' => $school->id, 'role' => 'teacher']);
        $class = Classes::create(['school_id' => $school->id, 'name' => 'SS 1', 'capacity' => 30]);
        $subject = Subject::create(['school_id' => $school->id, 'name' => 'Physics', 'code' => 'PHY-'.uniqid(), 'credit_hours' => 3]);
        $year = AcademicYear::create(['school_id' => $school->id, 'name' => '2026/27', 'starts_on' => '2026-09-01', 'ends_on' => '2027-07-01']);
        $term = AcademicTerm::create(['school_id' => $school->id, 'academic_year_id' => $year->id, 'name' => 'First Term', 'sequence' => 1, 'starts_on' => '2026-09-01', 'ends_on' => '2026-12-01']);
        $student = Student::factory()->create(['school_id' => $school->id, 'class_id' => $class->id]);
        $this->actingAs($teacher)->post(route('assessments.store'), ['academic_term_id' => $term->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'title' => 'Midterm', 'type' => 'test', 'maximum_score' => 50, 'weight_percentage' => 40])->assertRedirect();
        $assessment = Assessment::first();
        $this->assertSame($teacher->id, $assessment->teacher_id);
        $this->actingAs($teacher)->put(route('assessments.scores', $assessment), ['scores' => [$student->id => 42], 'comments' => [$student->id => 'Strong work']])->assertSessionHasNoErrors();
        $this->actingAs($teacher)->post(route('assessments.submit', $assessment))->assertSessionHasNoErrors();
        $this->assertSame('submitted', $assessment->fresh()->status);
        $this->actingAs($admin)->post(route('assessments.approve', $assessment))->assertSessionHasNoErrors();
        $this->assertSame('approved', $assessment->fresh()->status);
        $this->assertSame($admin->id, $assessment->fresh()->approved_by);
        $this->actingAs($teacher)->put(route('assessments.scores', $assessment), ['scores' => [$student->id => 1]])->assertStatus(422);
    }

    public function test_cross_tenant_assessment_relations_are_rejected(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $foreign = School::factory()->create();
        $class = Classes::create(['school_id' => $foreign->id, 'name' => 'Foreign', 'capacity' => 30]);
        $subject = Subject::create(['school_id' => $foreign->id, 'name' => 'Foreign subject', 'code' => 'X-'.uniqid()]);
        $year = AcademicYear::create(['school_id' => $foreign->id, 'name' => '2026', 'starts_on' => '2026-01-01', 'ends_on' => '2026-12-01']);
        $term = AcademicTerm::create(['school_id' => $foreign->id, 'academic_year_id' => $year->id, 'name' => 'Term', 'sequence' => 1, 'starts_on' => '2026-01-01', 'ends_on' => '2026-03-01']);
        $teacher = User::factory()->create(['school_id' => $foreign->id, 'role' => 'teacher']);
        $this->actingAs($admin)->post(route('assessments.store'), ['academic_term_id' => $term->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_id' => $teacher->id, 'title' => 'Escape', 'type' => 'test', 'maximum_score' => 100, 'weight_percentage' => 100])->assertSessionHasErrors(['academic_term_id', 'class_id', 'subject_id', 'teacher_id']);
    }
}
