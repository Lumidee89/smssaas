<?php

namespace App\Actions\Cbt;

use App\Models\CbtAttempt;
use Illuminate\Support\Facades\DB;

class GradeCbtAttempt
{
    public function execute(CbtAttempt $attempt): CbtAttempt
    {
        $graded = DB::transaction(function () use ($attempt): CbtAttempt {
            $attempt = CbtAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            if ($attempt->submitted_at) {
                return $attempt;
            }
            $exam = $attempt->exam()->with('questions')->firstOrFail();
            $questions = $exam->questions->keyBy('id');
            $answers = $attempt->answers()->get()->keyBy('cbt_question_id');
            $score = 0.0;
            $maximum = 0.0;
            $needsReview = false;

            foreach ($questions as $question) {
                $points = (float) $question->pivot->points;
                $maximum += $points;
                $answer = $answers->get($question->id);
                if (! $answer) {
                    continue;
                }
                if ($question->type === 'short_text') {
                    $answer->update(['requires_review' => true]);
                    $needsReview = true;

                    continue;
                }
                $given = collect($answer->answer ?? [])->map(fn ($value) => mb_strtolower(trim((string) $value)))->sort()->values()->all();
                $correct = collect($question->correct_answers ?? [])->map(fn ($value) => mb_strtolower(trim((string) $value)))->sort()->values()->all();
                $isCorrect = $given === $correct;
                $awarded = $isCorrect ? $points : 0.0;
                $score += $awarded;
                $answer->update(['awarded_points' => $awarded, 'is_correct' => $isCorrect, 'requires_review' => false]);
            }

            $attempt->update([
                'submitted_at' => now(),
                'status' => $needsReview ? 'pending_review' : 'graded',
                'score' => $score,
                'maximum_score' => $maximum,
                'percentage' => $maximum > 0 ? round(($score / $maximum) * 100, 2) : 0,
            ]);

            return $attempt->fresh();
        });

        if ($graded->status === 'graded') {
            app(SyncCbtAttemptToResult::class)->execute($graded);
        }

        return $graded;
    }
}
