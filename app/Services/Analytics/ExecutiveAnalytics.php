<?php

namespace App\Services\Analytics;

use App\Models\AssessmentScore;
use App\Models\AttendanceRecord;
use App\Models\FeeInvoice;
use App\Models\Payment;
use App\Models\StudentSuccessScore;

class ExecutiveAnalytics
{
    public function forSchool(int $schoolId): array
    {
        $months = collect(range(11, 0))->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset));
        $revenue = $months->map(fn ($month) => ['label' => $month->format('M y'), 'value' => (float) Payment::where('school_id', $schoolId)->where('status', 'paid')->whereBetween('paid_at', [$month, $month->copy()->endOfMonth()])->sum('amount')]);
        $attendance = $months->map(function ($month) use ($schoolId) {
            $rows = AttendanceRecord::where('school_id', $schoolId)->whereBetween('attendance_date', [$month, $month->copy()->endOfMonth()])->get('status');
            return ['label' => $month->format('M y'), 'value' => $rows->isEmpty() ? null : round($rows->whereIn('status', ['present', 'late', 'excused'])->count() / $rows->count() * 100, 1)];
        });

        $latestDate = StudentSuccessScore::where('school_id', $schoolId)->max('calculated_on');
        $risk = StudentSuccessScore::where('school_id', $schoolId)->when($latestDate, fn ($query) => $query->whereDate('calculated_on', $latestDate))
            ->with('student.class')->get()->map(function ($score) {
                $probability = min(95, max(2, round((100 - (float) $score->score) * .75 + max(0, -(float) $score->trend) * 1.5)));
                return ['student' => $score->student, 'probability' => $probability, 'level' => $probability >= 65 ? 'high' : ($probability >= 35 ? 'medium' : 'low'), 'trend' => (float) $score->trend, 'factors' => $score->risk_factors, 'data_quality' => $score->data_quality];
            })->sortByDesc('probability')->values();

        $invoiceRows = FeeInvoice::where('school_id', $schoolId)->whereIn('status', ['issued', 'partial', 'overdue'])->get();
        $aging = collect(['current' => 0, '1–30 days' => 0, '31–60 days' => 0, '61+ days' => 0]);
        foreach ($invoiceRows as $invoice) {
            $balance = max(0, (float) $invoice->amount_due - (float) $invoice->amount_paid);
            $days = $invoice->due_on ? $invoice->due_on->diffInDays(now(), false) : 0;
            $bucket = $days <= 0 ? 'current' : ($days <= 30 ? '1–30 days' : ($days <= 60 ? '31–60 days' : '61+ days'));
            $aging[$bucket] += $balance;
        }

        $percentages = AssessmentScore::where('assessment_scores.school_id', $schoolId)->whereHas('assessment', fn ($query) => $query->where('status', 'approved'))->with('assessment:id,maximum_score')->get()->map(fn ($row) => (float) $row->assessment->maximum_score > 0 ? (float) $row->score / (float) $row->assessment->maximum_score * 100 : 0);
        $distribution = collect(['A (70–100)' => 0, 'B (60–69)' => 0, 'C (50–59)' => 0, 'D (45–49)' => 0, 'E/F (<45)' => 0]);
        foreach ($percentages as $value) {
            $bucket = match (true) {
                $value >= 70 => 'A (70–100)', $value >= 60 => 'B (60–69)',
                $value >= 50 => 'C (50–59)', $value >= 45 => 'D (45–49)',
                default => 'E/F (<45)',
            };
            $distribution[$bucket]++;
        }

        $average = $revenue->take(-3)->avg('value') ?: 0;
        $growth = $this->growth($revenue->pluck('value')->all());
        $forecast = collect(range(1, 3))->map(fn ($offset) => ['label' => now()->addMonths($offset)->format('M y'), 'value' => round(max(0, $average * pow(1 + $growth, $offset)), 2)]);

        return compact('revenue', 'attendance', 'risk', 'aging', 'distribution', 'forecast', 'growth');
    }

    private function growth(array $values): float
    {
        $positive = array_values(array_filter($values, fn ($value) => $value > 0));
        if (count($positive) < 2) return 0;
        return max(-.25, min(.25, (end($positive) - $positive[count($positive) - 2]) / $positive[count($positive) - 2]));
    }
}
