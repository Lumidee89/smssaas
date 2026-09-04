<?php

namespace App\Http\Controllers;

use App\Actions\Cbt\SyncCbtAttemptToResult;
use App\Models\AcademicTerm;
use App\Models\CbtAnswer;
use App\Models\CbtAttempt;
use App\Models\CbtExam;
use App\Models\CbtQuestion;
use App\Models\Classes;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CbtAdminController extends Controller
{
    public function index()
    {
        $schoolId = Auth::user()->school_id;
        $exams = CbtExam::where('school_id', $schoolId)->with(['subject', 'schoolClass'])->withCount(['questions', 'attempts'])->latest()->paginate(20);

        return view('cbt.index', compact('exams'));
    }

    public function create()
    {
        return view('cbt.create', $this->formData());
    }

    public function edit(CbtExam $exam)
    {
        $this->tenant($exam);
        abort_if($exam->attempts()->exists(), 422, 'This exam is locked because a student has already started it.');
        $exam->load('questions');

        return view('cbt.edit', [...$this->formData(), 'exam' => $exam]);
    }

    public function storeExam(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $data = $request->validate([
            'academic_term_id' => ['required', Rule::exists('academic_terms', 'id')->where('school_id', $schoolId)],
            'class_id' => ['required', Rule::exists('classes', 'id')->where('school_id', $schoolId)],
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_id', $schoolId)],
            'title' => ['required', 'string', 'max:255'], 'instructions' => ['nullable', 'string', 'max:5000'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:480'], 'opens_at' => ['required', 'date'],
            'closes_at' => ['required', 'date', 'after:opens_at'], 'max_attempts' => ['required', 'integer', 'min:1', 'max:5'],
            'pass_percentage' => ['required', 'numeric', 'min:0', 'max:100'], 'access_code' => ['nullable', 'string', 'min:4', 'max:50'],
            'question_ids' => ['nullable', 'array'], 'question_ids.*' => [Rule::exists('cbt_questions', 'id')->where(fn ($q) => $q->where('school_id', $schoolId)->where('subject_id', $request->subject_id))],
            'shuffle_questions' => ['nullable', 'boolean'], 'shuffle_options' => ['nullable', 'boolean'],
        ]);
        $exam = CbtExam::create([...collect($data)->except(['question_ids', 'access_code'])->all(), 'school_id' => $schoolId, 'created_by' => Auth::id(), 'access_code_hash' => filled($data['access_code'] ?? null) ? Hash::make($data['access_code']) : null]);
        $questions = CbtQuestion::whereIn('id', $data['question_ids'] ?? [])->get();
        if ($questions->isNotEmpty()) {
            $exam->questions()->attach($questions->mapWithKeys(fn ($question, $index) => [$question->id => ['points' => $question->default_points, 'position' => $index + 1]])->all());
        }

        return redirect()->route('cbt.edit', $exam)->with('success', 'Exam draft created. Add questions for '.$exam->title.'.');
    }

    public function updateExam(Request $request, CbtExam $exam)
    {
        $this->tenant($exam);
        abort_if($exam->attempts()->exists(), 422, 'This exam is locked because a student has already started it.');
        $schoolId = Auth::user()->school_id;
        $data = $request->validate([
            'academic_term_id' => ['required', Rule::exists('academic_terms', 'id')->where('school_id', $schoolId)],
            'class_id' => ['required', Rule::exists('classes', 'id')->where('school_id', $schoolId)],
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_id', $schoolId)],
            'title' => ['required', 'string', 'max:255'], 'instructions' => ['nullable', 'string', 'max:5000'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:480'], 'opens_at' => ['required', 'date'],
            'closes_at' => ['required', 'date', 'after:opens_at'], 'max_attempts' => ['required', 'integer', 'min:1', 'max:5'],
            'pass_percentage' => ['required', 'numeric', 'min:0', 'max:100'], 'access_code' => ['nullable', 'string', 'min:4', 'max:50'],
            'clear_access_code' => ['nullable', 'boolean'],
            'question_ids' => ['required', 'array', 'min:1'], 'question_ids.*' => [Rule::exists('cbt_questions', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)->where('subject_id', $request->subject_id)->where('is_active', true))],
            'shuffle_questions' => ['nullable', 'boolean'], 'shuffle_options' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($exam, $data): void {
            $accessCodeHash = $exam->access_code_hash;
            if (filled($data['access_code'] ?? null)) {
                $accessCodeHash = Hash::make($data['access_code']);
            } elseif ($data['clear_access_code'] ?? false) {
                $accessCodeHash = null;
            }
            $exam->update([
                ...collect($data)->except(['question_ids', 'access_code', 'clear_access_code', 'shuffle_questions', 'shuffle_options'])->all(),
                'shuffle_questions' => (bool) ($data['shuffle_questions'] ?? false),
                'shuffle_options' => (bool) ($data['shuffle_options'] ?? false),
                'access_code_hash' => $accessCodeHash,
            ]);
            $questions = CbtQuestion::whereIn('id', $data['question_ids'])->get();
            $exam->questions()->sync($questions->mapWithKeys(fn ($question, $index) => [$question->id => ['points' => $question->default_points, 'position' => $index + 1]])->all());
        });

        return redirect()->route('cbt.show', $exam)->with('success', 'CBT exam updated successfully.');
    }

    public function storeQuestion(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $exam = $request->filled('exam_id') ? CbtExam::where('school_id', $schoolId)->findOrFail($request->integer('exam_id')) : null;
        if ($exam) {
            abort_if($exam->attempts()->exists(), 422, 'Questions cannot be changed after a student starts the exam.');
            $request->merge(['subject_id' => $exam->subject_id]);
        }
        $request->merge([
            'options' => collect($request->input('options', []))->filter(fn ($value) => filled($value))->values()->all(),
            'correct_answers' => collect($request->input('correct_answers', []))->filter(fn ($value) => filled($value))->values()->all(),
        ]);
        $data = $request->validate([
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_id', $schoolId)],
            'type' => ['required', Rule::in(['single_choice', 'multiple_choice', 'true_false', 'short_text'])],
            'prompt' => ['required', 'string', 'max:10000'], 'options' => ['nullable', 'array'], 'options.*' => ['string', 'max:1000'],
            'correct_answers' => ['required', 'array', 'min:1'], 'correct_answers.*' => ['string', 'max:1000'],
            'explanation' => ['nullable', 'string', 'max:5000'], 'default_points' => ['required', 'numeric', 'min:0.01', 'max:1000'],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
        ]);
        if (in_array($data['type'], ['single_choice', 'multiple_choice'], true)) {
            abort_unless(count($data['options'] ?? []) >= 2 && collect($data['correct_answers'])->diff($data['options'])->isEmpty(), 422, 'Correct answers must be included in the options.');
        }
        $question = CbtQuestion::create([...$data, 'school_id' => $schoolId, 'author_id' => Auth::id(), 'is_active' => true]);
        if ($exam) {
            $position = ((int) $exam->questions()->max('cbt_exam_question.position')) + 1;
            $exam->questions()->attach($question->id, ['points' => $question->default_points, 'position' => $position]);

            return redirect()->route('cbt.edit', $exam)->with('success', 'Question added to this exam.');
        }

        return back()->with('success', 'Question added to the bank.');
    }

    public function show(CbtExam $exam)
    {
        $this->tenant($exam);
        $exam->load(['subject', 'schoolClass', 'term.academicYear', 'questions'])->loadCount('attempts');
        $attempts = $exam->attempts()->with(['student', 'answers.question'])->latest()->paginate(30);

        return view('cbt.show', compact('exam', 'attempts'));
    }

    public function publish(CbtExam $exam)
    {
        $this->tenant($exam);
        abort_unless($exam->status === 'draft' && $exam->questions()->exists(), 422, 'Only a populated draft can be published.');
        $exam->update(['status' => 'published']);

        return back()->with('success', 'Exam published.');
    }

    public function reviewAnswer(Request $request, CbtExam $exam, CbtAttempt $attempt, CbtAnswer $answer)
    {
        $this->tenant($exam);
        abort_unless($attempt->cbt_exam_id === $exam->id && $answer->cbt_attempt_id === $attempt->id && $answer->requires_review, 404);
        $points = (float) $exam->questions()->whereKey($answer->cbt_question_id)->firstOrFail()->pivot->points;
        $data = $request->validate(['awarded_points' => ['required', 'numeric', 'min:0', 'max:'.$points], 'review_comment' => ['nullable', 'string', 'max:2000']]);
        $answer->update([...$data, 'requires_review' => false, 'is_correct' => (float) $data['awarded_points'] >= $points]);
        $score = (float) $attempt->answers()->sum('awarded_points');
        $maximum = (float) $exam->questions()->sum('cbt_exam_question.points');
        $pending = $attempt->answers()->where('requires_review', true)->exists();
        $attempt->update(['score' => $score, 'maximum_score' => $maximum, 'percentage' => $maximum > 0 ? round($score / $maximum * 100, 2) : 0, 'status' => $pending ? 'pending_review' : 'graded']);
        if (! $pending) {
            app(SyncCbtAttemptToResult::class)->execute($attempt->fresh());
        }

        return back()->with('success', $pending ? 'Answer reviewed.' : 'Attempt grading completed.');
    }

    private function tenant(CbtExam $exam): void
    {
        abort_unless($exam->school_id === Auth::user()->school_id, 403);
    }

    private function formData(): array
    {
        $schoolId = Auth::user()->school_id;

        return [
            'terms' => AcademicTerm::where('school_id', $schoolId)->with('academicYear')->orderByDesc('starts_on')->get(),
            'classes' => Classes::where('school_id', $schoolId)->orderBy('name')->get(),
            'subjects' => Subject::where('school_id', $schoolId)->orderBy('name')->get(),
            'questions' => CbtQuestion::where('school_id', $schoolId)->where('is_active', true)->with('subject')->get(),
        ];
    }
}
