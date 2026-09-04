<?php

namespace Tests\Feature;

use App\Actions\Academics\IssueTranscript;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\Classes;
use App\Models\GradingPolicy;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\Academics\GpaCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicRecordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_weighted_assessments_produce_reproducible_gpa_and_transcript(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $class = Classes::create(['school_id' => $school->id, 'name' => 'JSS 1', 'capacity' => 30]);
        $student = Student::factory()->create(['school_id' => $school->id, 'class_id' => $class->id]);
        $subject = Subject::create(['school_id' => $school->id, 'name' => 'Mathematics', 'code' => 'MTH-'.uniqid(), 'credit_hours' => 3]);
        $year = AcademicYear::create(['school_id' => $school->id, 'name' => '2026/2027', 'starts_on' => '2026-09-01', 'ends_on' => '2027-07-30']);
        $term = AcademicTerm::create(['school_id' => $school->id, 'academic_year_id' => $year->id, 'name' => 'First Term', 'sequence' => 1, 'starts_on' => '2026-09-01', 'ends_on' => '2026-12-20']);
        $policy = GradingPolicy::create(['school_id' => $school->id, 'name' => 'Standard', 'version' => 1, 'is_active' => true]);
        $policy->bands()->createMany([['minimum_percentage' => 70, 'maximum_percentage' => 100, 'letter_grade' => 'A', 'grade_point' => 4, 'remark' => 'Excellent'], ['minimum_percentage' => 0, 'maximum_percentage' => 69.99, 'letter_grade' => 'F', 'grade_point' => 0, 'remark' => 'Needs improvement']]);
        $test = Assessment::create(['school_id' => $school->id, 'academic_term_id' => $term->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_id' => $admin->id, 'title' => 'Continuous Assessment', 'type' => 'test', 'maximum_score' => 40, 'weight_percentage' => 40, 'status' => 'approved', 'approved_by' => $admin->id, 'approved_at' => now()]);
        $exam = Assessment::create(['school_id' => $school->id, 'academic_term_id' => $term->id, 'class_id' => $class->id, 'subject_id' => $subject->id, 'teacher_id' => $admin->id, 'title' => 'Final Exam', 'type' => 'exam', 'maximum_score' => 60, 'weight_percentage' => 60, 'status' => 'approved', 'approved_by' => $admin->id, 'approved_at' => now()]);
        AssessmentScore::create(['school_id' => $school->id, 'assessment_id' => $test->id, 'student_id' => $student->id, 'score' => 32, 'entered_by' => $admin->id]);
        AssessmentScore::create(['school_id' => $school->id, 'assessment_id' => $exam->id, 'student_id' => $student->id, 'score' => 48, 'entered_by' => $admin->id]);
        $record = app(GpaCalculator::class)->term($student, $term, $policy);
        $this->assertSame(80.0, $record['subjects'][0]['percentage']);
        $this->assertSame(4.0, $record['gpa']);
        $transcript = app(IssueTranscript::class)->execute($student, $policy, $admin);
        $this->assertSame('4.00', $transcript->cgpa);
        $this->assertSame('issued', $transcript->status);
        $this->assertDatabaseHas('audit_logs', ['event' => 'Transcript.created', 'school_id' => $school->id]);
    }
}
