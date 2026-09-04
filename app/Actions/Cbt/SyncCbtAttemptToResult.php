<?php

namespace App\Actions\Cbt;

use App\Models\CbtAttempt;
use App\Models\Result;

class SyncCbtAttemptToResult
{
    public const REMARK_PREFIX = '[CBT:';

    public function execute(CbtAttempt $attempt): ?Result
    {
        if ($attempt->status !== 'graded' || ! $attempt->submitted_at) {
            return null;
        }

        $attempt->loadMissing('exam.term.academicYear');
        $exam = $attempt->exam;
        $term = $exam?->term;
        $year = $term?->academicYear;
        if (! $exam || ! $term || ! $year) {
            return null;
        }

        $identity = [
            'student_id' => $attempt->student_id,
            'subject_id' => $exam->subject_id,
            'term' => $term->name,
            'academic_year' => $year->starts_on->year,
            'exam_type' => 'exam',
        ];
        $existing = Result::where($identity)->first();
        if ($existing && ! str_starts_with((string) $existing->remarks, self::REMARK_PREFIX)) {
            return $existing;
        }

        $values = [
            'teacher_id' => $exam->created_by,
            'score' => (float) $attempt->score,
            'max_score' => (float) $attempt->maximum_score,
            'remarks' => self::REMARK_PREFIX.$attempt->id.'] Automatically synchronized from CBT: '.$exam->title.'.',
        ];

        return $existing ? tap($existing)->update($values) : Result::create([...$identity, ...$values]);
    }
}
