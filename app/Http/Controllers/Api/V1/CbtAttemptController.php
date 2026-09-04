<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Cbt\GradeCbtAttempt;
use App\Http\Controllers\Controller;
use App\Models\CbtAnswer;
use App\Models\CbtAttempt;
use App\Models\CbtExam;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CbtAttemptController extends Controller
{
    public function start(Request $request, CbtExam $exam): JsonResponse
    {
        $data = $request->validate(['admission_number' => ['required', 'string', 'max:100'], 'access_code' => ['nullable', 'string', 'max:50']]);
        abort_unless($exam->status === 'published' && now()->between($exam->opens_at, $exam->closes_at), 422, 'This exam is not currently open.');
        $student = Student::where('school_id', $exam->school_id)->where('class_id', $exam->class_id)->where('admission_number', $data['admission_number'])->firstOrFail();
        if ($exam->access_code_hash && ! Hash::check((string) ($data['access_code'] ?? ''), $exam->access_code_hash)) {
            abort(403, 'Invalid exam access code.');
        }

        [$attempt, $token] = DB::transaction(function () use ($exam, $student): array {
            $count = CbtAttempt::where('cbt_exam_id', $exam->id)->where('student_id', $student->id)->lockForUpdate()->count();
            abort_if($count >= $exam->max_attempts, 422, 'Maximum attempts reached.');
            $questions = $exam->questions()->pluck('cbt_questions.id')->all();
            if ($exam->shuffle_questions) {
                shuffle($questions);
            }
            $token = Str::random(64);
            $attempt = CbtAttempt::create([
                'school_id' => $exam->school_id, 'cbt_exam_id' => $exam->id, 'student_id' => $student->id,
                'attempt_number' => $count + 1, 'token_hash' => hash('sha256', $token), 'question_order' => $questions,
                'started_at' => now(), 'expires_at' => now()->addMinutes($exam->duration_minutes)->min($exam->closes_at), 'status' => 'in_progress',
            ]);

            return [$attempt, $token];
        });

        return response()->json(['data' => ['attempt_token' => $token, ...$this->attemptPayload($attempt)]], 201);
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->attemptPayload($this->attempt($request))]);
    }

    public function answer(Request $request, int $question): JsonResponse
    {
        $attempt = $this->attempt($request);
        abort_unless($attempt->status === 'in_progress' && now()->lte($attempt->expires_at), 422, 'This attempt can no longer be edited.');
        abort_unless(in_array($question, $attempt->question_order, true), 404);
        $data = $request->validate(['answer' => ['nullable', 'array'], 'answer.*' => ['string', 'max:5000']]);
        CbtAnswer::updateOrCreate(
            ['cbt_attempt_id' => $attempt->id, 'cbt_question_id' => $question],
            ['answer' => array_values($data['answer'] ?? []), 'awarded_points' => null, 'is_correct' => null, 'requires_review' => false],
        );

        return response()->json(['message' => 'Answer saved.']);
    }

    public function submit(Request $request, GradeCbtAttempt $grader): JsonResponse
    {
        $attempt = $grader->execute($this->attempt($request));

        return response()->json(['data' => [
            'status' => $attempt->status,
            'score' => (float) $attempt->score,
            'maximum_score' => (float) $attempt->maximum_score,
            'percentage' => (float) $attempt->percentage,
            'passed' => $attempt->status === 'graded' ? (float) $attempt->percentage >= (float) $attempt->exam->pass_percentage : null,
        ]]);
    }

    private function attempt(Request $request): CbtAttempt
    {
        $token = $request->bearerToken();
        abort_unless($token, 401, 'Attempt token required.');

        return CbtAttempt::where('token_hash', hash('sha256', $token))->with(['exam.questions', 'answers'])->firstOrFail();
    }

    private function attemptPayload(CbtAttempt $attempt): array
    {
        $attempt->loadMissing(['exam.questions', 'answers']);
        $questions = $attempt->exam->questions->keyBy('id');
        $answers = $attempt->answers->keyBy('cbt_question_id');

        return [
            'id' => $attempt->id, 'status' => $attempt->status, 'started_at' => $attempt->started_at->toISOString(),
            'expires_at' => $attempt->expires_at->toISOString(), 'server_time' => now()->toISOString(),
            'score' => $attempt->submitted_at ? (float) $attempt->score : null,
            'maximum_score' => $attempt->submitted_at ? (float) $attempt->maximum_score : null,
            'percentage' => $attempt->submitted_at ? (float) $attempt->percentage : null,
            'exam' => ['id' => $attempt->exam->id, 'title' => $attempt->exam->title, 'instructions' => $attempt->exam->instructions],
            'questions' => collect($attempt->question_order)->map(function ($id) use ($questions, $answers, $attempt) {
                $question = $questions->get($id);
                $options = $question->options;
                if ($attempt->exam->shuffle_options && is_array($options)) {
                    $options = collect($options)->sortBy(fn ($option) => hash('sha256', $attempt->id.'|'.$question->id.'|'.$option))->values()->all();
                }

                return ['id' => $question->id, 'type' => $question->type, 'prompt' => $question->prompt, 'options' => $options, 'points' => (float) $question->pivot->points, 'answer' => $answers->get($id)?->answer];
            })->values(),
        ];
    }
}
