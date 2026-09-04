<?php

namespace App\Actions\Academics;

use App\Models\AcademicTerm;
use App\Models\GradingPolicy;
use App\Models\Student;
use App\Models\Transcript;
use App\Models\User;
use App\Services\Academics\GpaCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class IssueTranscript
{
    public function __construct(private GpaCalculator $calculator) {}

    public function execute(Student $student, GradingPolicy $policy, User $issuer): Transcript
    {
        if ($student->school_id !== $issuer->school_id || $student->school_id !== $policy->school_id) {
            throw new InvalidArgumentException('Transcript tenant mismatch.');
        }$terms = AcademicTerm::where('school_id', $student->school_id)->with('academicYear')->orderBy('starts_on')->get();
        $records = $terms->map(fn ($term) => $this->calculator->term($student, $term, $policy))->filter(fn ($record) => count($record['subjects']) > 0)->values();
        $credits = $records->sum('total_credits');
        $cgpa = $credits > 0 ? round($records->sum(fn ($record) => $record['gpa'] * $record['total_credits']) / $credits, 2) : 0;

        return DB::transaction(fn () => Transcript::create(['public_id' => (string) Str::uuid(), 'school_id' => $student->school_id, 'student_id' => $student->id, 'grading_policy_id' => $policy->id, 'cgpa' => $cgpa, 'snapshot' => ['student' => ['name' => $student->full_name, 'admission_number' => $student->admission_number], 'policy' => ['name' => $policy->name, 'version' => $policy->version], 'terms' => $records->all()], 'status' => 'issued', 'issued_by' => $issuer->id, 'issued_at' => now()]));
    }
}
