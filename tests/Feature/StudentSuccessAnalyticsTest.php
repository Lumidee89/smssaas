<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\AttendanceRecord;
use App\Models\Classes;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentDevelopmentSignal;
use App\Models\Subject;
use App\Models\User;
use App\Services\Analytics\StudentSuccessCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentSuccessAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_score_uses_blueprint_weights_and_preserves_explanations(): void
    {
        [$school, $admin, $student, $class, $subject, $term] = $this->fixture();
        $academic = Assessment::create(['school_id' => $school->id, 'academic_term_id' => $term->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_id' => $admin->id, 'title' => 'Exam', 'type' => 'exam', 'maximum_score' => 100, 'weight_percentage' => 50, 'status' => 'approved', 'approved_by' => $admin->id, 'approved_at' => now()]);
        $assignment = Assessment::create(['school_id' => $school->id, 'academic_term_id' => $term->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_id' => $admin->id, 'title' => 'Assignment', 'type' => 'assignment', 'maximum_score' => 20, 'weight_percentage' => 50, 'status' => 'approved', 'approved_by' => $admin->id, 'approved_at' => now()]);
        AssessmentScore::create(['school_id' => $school->id, 'assessment_id' => $academic->id, 'student_id' => $student->id, 'score' => 80, 'entered_by' => $admin->id]);
        AssessmentScore::create(['school_id' => $school->id, 'assessment_id' => $assignment->id, 'student_id' => $student->id, 'score' => 20, 'entered_by' => $admin->id]);
        foreach ([['present', now()->subDay()], ['absent', now()]] as [$status,$date]) {
            AttendanceRecord::create(['school_id' => $school->id, 'student_id' => $student->id, 'class_id' => $class->id, 'academic_term_id' => $term->id, 'attendance_date' => $date, 'status' => $status, 'recorded_by' => $admin->id]);
        }
        StudentDevelopmentSignal::create(['school_id' => $school->id, 'student_id' => $student->id, 'recorded_by' => $admin->id, 'category' => 'behaviour', 'score' => 40, 'note' => 'Documented behaviour', 'occurred_on' => now()]);
        StudentDevelopmentSignal::create(['school_id' => $school->id, 'student_id' => $student->id, 'recorded_by' => $admin->id, 'category' => 'leadership', 'score' => 80, 'note' => 'Led group work', 'occurred_on' => now()]);

        $score = app(StudentSuccessCalculator::class)->calculate($student);
        $this->assertSame(80.0, (float) $score->academic_score);
        $this->assertSame(50.0, (float) $score->attendance_score);
        $this->assertSame(100.0, (float) $score->assignment_score);
        $this->assertSame(40.0, (float) $score->behaviour_score);
        $this->assertSame(80.0, (float) $score->leadership_score);
        $this->assertSame('71.00', $score->score);
        $this->assertSame('low', $score->risk_level);
        $this->assertContains('behaviour', collect($score->risk_factors)->pluck('metric')->all());
        $this->assertFalse($score->data_quality['uses_neutral_defaults']);
    }

    public function test_analytics_workspace_is_tenant_scoped_and_interventions_are_protected(): void
    {
        [$school, $admin, $student] = $this->fixture();
        $foreign = School::factory()->create();
        $foreignStudent = Student::factory()->create(['school_id' => $foreign->id]);
        $this->actingAs($admin)->post(route('analytics.recalculate'))->assertRedirect();
        $this->actingAs($admin)->get(route('analytics.index'))->assertOk()->assertSee($student->full_name)->assertDontSee($foreignStudent->full_name);
        $this->actingAs($admin)->post(route('analytics.interventions.store'), ['student_id' => $foreignStudent->id, 'title' => 'Escape', 'action_plan' => 'Invalid'])->assertSessionHasErrors(['student_id']);
    }

    private function fixture(): array
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $class = Classes::create(['school_id' => $school->id, 'name' => 'JSS 2', 'capacity' => 30]);
        $subject = Subject::create(['school_id' => $school->id, 'name' => 'Science', 'code' => 'SCI-'.uniqid()]);
        $year = AcademicYear::create(['school_id' => $school->id, 'name' => '2026/27', 'starts_on' => now()->subMonth(), 'ends_on' => now()->addYear()]);
        $term = AcademicTerm::create(['school_id' => $school->id, 'academic_year_id' => $year->id, 'name' => 'Term 1', 'sequence' => 1, 'starts_on' => now()->subMonth(), 'ends_on' => now()->addMonths(3)]);
        $student = Student::factory()->create(['school_id' => $school->id, 'class_id' => $class->id]);

        return [$school, $admin, $student, $class, $subject, $term];
    }
}
