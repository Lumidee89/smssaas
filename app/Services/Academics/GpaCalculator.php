<?php

namespace App\Services\Academics;

use App\Models\AcademicTerm;
use App\Models\AssessmentScore;
use App\Models\GradingPolicy;
use App\Models\Student;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class GpaCalculator
{
    public function term(Student $student, AcademicTerm $term, GradingPolicy $policy): array
    {
        if ($student->school_id !== $term->school_id || $student->school_id !== $policy->school_id) {
            throw new InvalidArgumentException('Academic records must use one tenant.');
        }$policy->loadMissing('bands');
        $scores = AssessmentScore::where('school_id', $student->school_id)->where('student_id', $student->id)->whereHas('assessment', fn ($q) => $q->where('academic_term_id', $term->id)->where('status', 'approved'))->with('assessment.subject')->get();
        $subjects = $scores->groupBy(fn ($score) => $score->assessment->subject_id)->map(function (Collection $group) use ($policy) {
            $weighted = $group->sum(fn ($row) => (float) $row->score / (float) $row->assessment->maximum_score * (float) $row->assessment->weight_percentage);
            $weights = $group->sum(fn ($row) => (float) $row->assessment->weight_percentage);
            $percentage = $weights > 0 ? round($weighted / $weights * 100, 2) : 0;
            $band = $policy->bands->first(fn ($b) => $percentage >= (float) $b->minimum_percentage && $percentage <= (float) $b->maximum_percentage);
            $subject = $group->first()->assessment->subject;

            return ['subject_id' => $subject->id, 'subject' => $subject->name, 'code' => $subject->code, 'credit_hours' => $subject->credit_hours, 'percentage' => $percentage, 'letter_grade' => $band?->letter_grade ?? 'N/A', 'grade_point' => (float) ($band?->grade_point ?? 0), 'remark' => $band?->remark];
        })->values();
        $credits = $subjects->sum('credit_hours');
        $gpa = $credits > 0 ? round($subjects->sum(fn ($row) => $row['grade_point'] * $row['credit_hours']) / $credits, 2) : 0;

        return ['term_id' => $term->id, 'term' => $term->name, 'academic_year' => $term->academicYear?->name, 'gpa' => $gpa, 'total_credits' => $credits, 'subjects' => $subjects->all()];
    }
}
