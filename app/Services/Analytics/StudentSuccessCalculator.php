<?php

namespace App\Services\Analytics;

use App\Models\AssessmentScore;
use App\Models\AttendanceRecord;
use App\Models\Student;
use App\Models\StudentDevelopmentSignal;
use App\Models\StudentSuccessScore;

class StudentSuccessCalculator
{
    public const WEIGHTS = ['academics' => .40, 'attendance' => .20, 'assignments' => .15, 'behaviour' => .15, 'leadership' => .10];

    public function calculate(Student $student, ?\DateTimeInterface $date = null): StudentSuccessScore
    {
        $day = now()->parse($date ?? now())->startOfDay();
        $academicRows = AssessmentScore::where('student_id', $student->id)
            ->whereHas('assessment', fn ($query) => $query->where('status', 'approved')->where('type', '!=', 'assignment'))
            ->with('assessment:id,maximum_score')->get();
        $assignmentRows = AssessmentScore::where('student_id', $student->id)
            ->whereHas('assessment', fn ($query) => $query->where('status', 'approved')->where('type', 'assignment'))
            ->with('assessment:id,maximum_score')->get();
        $attendanceRows = AttendanceRecord::where('student_id', $student->id)->whereBetween('attendance_date', [$day->copy()->subDays(89), $day->copy()->endOfDay()])->get();
        $signals = StudentDevelopmentSignal::where('student_id', $student->id)->whereBetween('occurred_on', [$day->copy()->subDays(179), $day->copy()->endOfDay()])->get();

        $academics = $this->assessmentAverage($academicRows);
        $assignments = $this->assessmentAverage($assignmentRows);
        $attendance = $attendanceRows->isEmpty() ? 50.0 : round($attendanceRows->avg(fn ($row) => match ($row->status) {
            'present' => 100, 'late' => 70, 'excused' => 80, default => 0
        }), 2);
        $behaviourRows = $signals->where('category', 'behaviour');
        $leadershipRows = $signals->where('category', 'leadership');
        $behaviour = $behaviourRows->isEmpty() ? 50.0 : round($behaviourRows->avg('score'), 2);
        $leadership = $leadershipRows->isEmpty() ? 50.0 : round($leadershipRows->avg('score'), 2);
        $components = compact('academics', 'attendance', 'assignments', 'behaviour', 'leadership');
        $score = round(collect(self::WEIGHTS)->sum(fn ($weight, $key) => $components[$key] * $weight), 2);
        $previous = StudentSuccessScore::where('student_id', $student->id)->where('calculated_on', '<', $day->toDateString())->latest('calculated_on')->first();
        $factors = collect($components)->filter(fn ($value) => $value < 60)->map(fn ($value, $key) => ['metric' => $key, 'score' => $value])->values()->all();
        $recommendations = collect($factors)->map(fn ($factor) => match ($factor['metric']) {
            'academics' => 'Schedule targeted subject support and review the latest assessments.',
            'attendance' => 'Contact the guardian and agree an attendance improvement plan.',
            'assignments' => 'Set a weekly assignment completion check-in.',
            'behaviour' => 'Document a pastoral support plan with measurable goals.',
            'leadership' => 'Offer a structured classroom or extracurricular responsibility.',
        })->values()->all();

        return StudentSuccessScore::updateOrCreate(
            ['student_id' => $student->id, 'calculated_on' => $day->toDateString()],
            [
                'school_id' => $student->school_id, 'score' => $score,
                'academic_score' => $academics, 'attendance_score' => $attendance, 'assignment_score' => $assignments,
                'behaviour_score' => $behaviour, 'leadership_score' => $leadership,
                'risk_level' => $score < 40 ? 'high' : ($score < 60 ? 'medium' : 'low'),
                'trend' => $previous ? round($score - (float) $previous->score, 2) : 0,
                'risk_factors' => $factors, 'recommendations' => $recommendations,
                'data_quality' => [
                    'academic_assessments' => $academicRows->count(), 'assignment_assessments' => $assignmentRows->count(),
                    'attendance_days' => $attendanceRows->count(), 'behaviour_signals' => $behaviourRows->count(), 'leadership_signals' => $leadershipRows->count(),
                    'uses_neutral_defaults' => $academicRows->isEmpty() || $assignmentRows->isEmpty() || $attendanceRows->isEmpty() || $behaviourRows->isEmpty() || $leadershipRows->isEmpty(),
                ],
            ],
        );
    }

    private function assessmentAverage($rows): float
    {
        if ($rows->isEmpty()) {
            return 50.0;
        }

        return round($rows->avg(fn ($row) => (float) $row->assessment->maximum_score > 0 ? min(100, (float) $row->score / (float) $row->assessment->maximum_score * 100) : 0), 2);
    }
}
