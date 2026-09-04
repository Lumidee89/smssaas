<?php

namespace Tests\Feature;

use App\Actions\Cbt\SyncCbtAttemptToResult;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\CbtAnswer;
use App\Models\CbtExam;
use App\Models\CbtQuestion;
use App\Models\Classes;
use App\Models\Result;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CbtWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_takes_randomized_exam_and_objective_answers_are_graded(): void
    {
        [$exam, $student] = $this->examFixture();
        $question = CbtQuestion::create([
            'school_id' => $exam->school_id, 'subject_id' => $exam->subject_id, 'author_id' => $exam->created_by,
            'type' => 'single_choice', 'prompt' => 'What is 2 + 2?', 'options' => ['3', '4', '5'],
            'correct_answers' => ['4'], 'default_points' => 5, 'difficulty' => 'easy', 'is_active' => true,
        ]);
        $exam->questions()->attach($question->id, ['points' => 5, 'position' => 1]);

        $started = $this->postJson("/api/v1/cbt/exams/{$exam->id}/start", [
            'admission_number' => $student->admission_number, 'access_code' => 'EXAM42',
        ])->assertCreated()->assertJsonMissing(['correct_answers']);
        $token = $started->json('data.attempt_token');
        $this->withToken($token)->putJson("/api/v1/cbt/attempt/questions/{$question->id}", ['answer' => ['4']])->assertOk();
        $this->withToken($token)->postJson('/api/v1/cbt/attempt/submit')->assertOk()
            ->assertJsonPath('data.status', 'graded')->assertJsonPath('data.percentage', 100);
        $syncedResult = Result::where('student_id', $student->id)->where('subject_id', $exam->subject_id)->firstOrFail();
        $this->assertSame('5.00', $syncedResult->score);
        $this->assertStringStartsWith(SyncCbtAttemptToResult::REMARK_PREFIX, $syncedResult->remarks);
        $parent = User::factory()->create(['school_id' => $exam->school_id, 'role' => 'parent']);
        $parent->children()->attach($student, ['school_id' => $exam->school_id, 'relationship' => 'parent']);
        $parentToken = $parent->createToken('parent-app')->plainTextToken;
        $this->withToken($parentToken)->getJson("/api/v1/parent/children/{$student->id}/progress")
            ->assertOk()
            ->assertJsonPath('data.cbt_attempts.0.exam', $exam->title)
            ->assertJsonPath('data.cbt_attempts.0.percentage', 100)
            ->assertJsonPath('data.cbt_attempts.0.passed', true);
        $admin = User::findOrFail($exam->created_by);
        $this->actingAs($admin)->post(route('results.store'), [
            'student_id' => $student->id, 'subject_id' => $exam->subject_id, 'exam_type' => 'exam',
            'term' => $exam->term->name, 'academic_year' => $exam->term->academicYear->starts_on->year,
            'score' => 4, 'max_score' => 5, 'remarks' => 'Manually verified by teacher.',
        ])->assertRedirect(route('results.index'));
        app(SyncCbtAttemptToResult::class)->execute($exam->attempts()->firstOrFail());
        $this->assertSame('4.00', $syncedResult->fresh()->score);
        $this->assertSame('Manually verified by teacher.', $syncedResult->fresh()->remarks);
        $this->withToken($token)->putJson("/api/v1/cbt/attempt/questions/{$question->id}", ['answer' => ['3']])->assertStatus(422);
        $this->postJson("/api/v1/cbt/exams/{$exam->id}/start", [
            'admission_number' => $student->admission_number, 'access_code' => 'EXAM42',
        ])->assertStatus(422);
    }

    public function test_exam_rejects_wrong_class_wrong_code_and_cross_tenant_question(): void
    {
        [$exam, $student] = $this->examFixture();
        $other = Student::factory()->create(['school_id' => $exam->school_id]);
        $this->postJson("/api/v1/cbt/exams/{$exam->id}/start", ['admission_number' => $student->admission_number, 'access_code' => 'wrong'])->assertForbidden();
        $this->postJson("/api/v1/cbt/exams/{$exam->id}/start", ['admission_number' => $other->admission_number, 'access_code' => 'EXAM42'])->assertNotFound();

        $foreign = School::factory()->create();
        $foreignSubject = Subject::create(['school_id' => $foreign->id, 'name' => 'Foreign', 'code' => 'FOR-'.uniqid()]);
        $foreignTeacher = User::factory()->create(['school_id' => $foreign->id, 'role' => 'teacher']);
        $question = CbtQuestion::create(['school_id' => $foreign->id, 'subject_id' => $foreignSubject->id, 'author_id' => $foreignTeacher->id, 'type' => 'true_false', 'prompt' => 'Foreign?', 'correct_answers' => ['true']]);
        $admin = User::find($exam->created_by);
        $this->actingAs($admin)->post(route('cbt.exams.store'), [
            'academic_term_id' => $exam->academic_term_id, 'class_id' => $exam->class_id, 'subject_id' => $exam->subject_id,
            'title' => 'Invalid', 'duration_minutes' => 30, 'opens_at' => now()->addHour(), 'closes_at' => now()->addHours(2),
            'max_attempts' => 1, 'pass_percentage' => 50, 'question_ids' => [$question->id],
        ])->assertSessionHasErrors(['question_ids.0']);
    }

    public function test_short_text_answer_requires_teacher_review_before_final_grade(): void
    {
        [$exam, $student] = $this->examFixture();
        $question = CbtQuestion::create([
            'school_id' => $exam->school_id, 'subject_id' => $exam->subject_id, 'author_id' => $exam->created_by,
            'type' => 'short_text', 'prompt' => 'Explain the method.', 'correct_answers' => ['Teacher reviewed'],
            'default_points' => 10, 'difficulty' => 'medium', 'is_active' => true,
        ]);
        $exam->questions()->attach($question->id, ['points' => 10, 'position' => 1]);
        $started = $this->postJson("/api/v1/cbt/exams/{$exam->id}/start", [
            'admission_number' => $student->admission_number, 'access_code' => 'EXAM42',
        ])->assertCreated();
        $token = $started->json('data.attempt_token');
        $this->withToken($token)->putJson("/api/v1/cbt/attempt/questions/{$question->id}", ['answer' => ['My explanation']])->assertOk();
        $this->withToken($token)->postJson('/api/v1/cbt/attempt/submit')->assertJsonPath('data.status', 'pending_review');
        $answer = CbtAnswer::firstOrFail();
        $attempt = $answer->cbt_attempt_id;
        $admin = User::find($exam->created_by);
        $this->actingAs($admin)->post(route('cbt.answers.review', [$exam, $attempt, $answer]), [
            'awarded_points' => 8, 'review_comment' => 'Good method.',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cbt_attempts', ['id' => $attempt, 'status' => 'graded', 'score' => 8]);
        $this->assertDatabaseHas('results', ['student_id' => $student->id, 'subject_id' => $exam->subject_id, 'score' => 8, 'max_score' => 10]);
    }

    public function test_published_exam_has_public_student_portal_but_draft_does_not(): void
    {
        [$exam] = $this->examFixture();

        $this->get(route('student.cbt.show', $exam))
            ->assertOk()
            ->assertSee('Student examination portal')
            ->assertSee($exam->title);

        $exam->update(['status' => 'draft']);
        $this->get(route('student.cbt.show', $exam))->assertNotFound();
    }

    public function test_exam_can_be_fully_edited_before_first_attempt_and_is_locked_afterward(): void
    {
        [$exam, $student] = $this->examFixture();
        $question = CbtQuestion::create([
            'school_id' => $exam->school_id, 'subject_id' => $exam->subject_id, 'author_id' => $exam->created_by,
            'type' => 'single_choice', 'prompt' => 'Original question?', 'options' => ['Yes', 'No'],
            'correct_answers' => ['Yes'], 'default_points' => 4, 'difficulty' => 'easy', 'is_active' => true,
        ]);
        $exam->questions()->attach($question->id, ['points' => 4, 'position' => 1]);
        $admin = User::findOrFail($exam->created_by);
        $opens = now()->addHour()->seconds(0);
        $closes = now()->addHours(3)->seconds(0);

        $payload = [
            'academic_term_id' => $exam->academic_term_id, 'class_id' => $exam->class_id, 'subject_id' => $exam->subject_id,
            'title' => 'Updated Mathematics CBT', 'instructions' => 'Read every question carefully.',
            'duration_minutes' => 75, 'opens_at' => $opens->format('Y-m-d H:i:s'), 'closes_at' => $closes->format('Y-m-d H:i:s'),
            'max_attempts' => 2, 'pass_percentage' => 60, 'question_ids' => [$question->id], 'shuffle_options' => 1,
            'access_code' => 'NEWCODE',
        ];
        $this->actingAs($admin)->put(route('cbt.update', $exam), $payload)->assertRedirect(route('cbt.show', $exam));
        $exam->refresh();
        $this->assertSame('Updated Mathematics CBT', $exam->title);
        $this->assertSame(75, $exam->duration_minutes);
        $this->assertFalse($exam->shuffle_questions);
        $this->assertTrue(Hash::check('NEWCODE', $exam->access_code_hash));

        $exam->update(['opens_at' => now()->subMinute(), 'closes_at' => now()->addHour()]);
        $this->postJson("/api/v1/cbt/exams/{$exam->id}/start", ['admission_number' => $student->admission_number, 'access_code' => 'NEWCODE'])->assertCreated();
        $this->actingAs($admin)->put(route('cbt.update', $exam), $payload)->assertStatus(422);
    }

    public function test_exam_draft_is_created_before_subject_question_is_added_in_context(): void
    {
        [$fixture] = $this->examFixture();
        $admin = User::findOrFail($fixture->created_by);
        $response = $this->actingAs($admin)->post(route('cbt.exams.store'), [
            'academic_term_id' => $fixture->academic_term_id, 'class_id' => $fixture->class_id,
            'subject_id' => $fixture->subject_id, 'title' => 'Contextual Questions Exam',
            'duration_minutes' => 40, 'opens_at' => now()->addHour(), 'closes_at' => now()->addHours(2),
            'max_attempts' => 1, 'pass_percentage' => 50,
        ]);
        $exam = CbtExam::where('title', 'Contextual Questions Exam')->firstOrFail();
        $response->assertRedirect(route('cbt.edit', $exam));
        $this->assertSame(0, $exam->questions()->count());

        $this->actingAs($admin)->post(route('cbt.questions.store'), [
            'exam_id' => $exam->id, 'type' => 'single_choice', 'prompt' => 'Which answer is correct?',
            'options' => ['A', 'B'], 'correct_answers' => ['B'], 'default_points' => 3, 'difficulty' => 'medium',
        ])->assertRedirect(route('cbt.edit', $exam));

        $this->assertSame(1, $exam->questions()->count());
        $this->assertSame($exam->subject_id, $exam->questions()->first()->subject_id);
    }

    private function examFixture(): array
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $class = Classes::create(['school_id' => $school->id, 'name' => 'SS 2', 'capacity' => 40]);
        $subject = Subject::create(['school_id' => $school->id, 'name' => 'Mathematics', 'code' => 'MTH-'.uniqid()]);
        $year = AcademicYear::create(['school_id' => $school->id, 'name' => '2026/27', 'starts_on' => now()->subMonth(), 'ends_on' => now()->addYear()]);
        $term = AcademicTerm::create(['school_id' => $school->id, 'academic_year_id' => $year->id, 'name' => 'First Term', 'sequence' => 1, 'starts_on' => now()->subMonth(), 'ends_on' => now()->addMonths(3)]);
        $student = Student::factory()->create(['school_id' => $school->id, 'class_id' => $class->id]);
        $exam = CbtExam::create([
            'school_id' => $school->id, 'academic_term_id' => $term->id, 'class_id' => $class->id, 'subject_id' => $subject->id,
            'created_by' => $admin->id, 'title' => 'Mathematics CBT', 'duration_minutes' => 45,
            'opens_at' => now()->subMinute(), 'closes_at' => now()->addHour(), 'status' => 'published', 'max_attempts' => 1,
            'pass_percentage' => 50, 'access_code_hash' => Hash::make('EXAM42'),
        ]);

        return [$exam, $student];
    }
}
