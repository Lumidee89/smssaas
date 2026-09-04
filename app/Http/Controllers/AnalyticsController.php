<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\FeeInvoice;
use App\Models\Payment;
use App\Models\StaffAttendanceRecord;
use App\Models\Student;
use App\Models\StudentDevelopmentSignal;
use App\Models\StudentIntervention;
use App\Models\StudentSuccessScore;
use App\Models\User;
use App\Services\Analytics\StudentSuccessCalculator;
use App\Services\Analytics\ExecutiveAnalytics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AnalyticsController extends Controller
{
    public function index(ExecutiveAnalytics $executive)
    {
        $schoolId = Auth::user()->school_id;
        $latestDate = StudentSuccessScore::where('school_id', $schoolId)->max('calculated_on');
        $scores = StudentSuccessScore::where('school_id', $schoolId)->when($latestDate, fn ($q) => $q->whereDate('calculated_on', $latestDate))->with('student.class')->orderBy('score')->get();
        $invoiced = (float) FeeInvoice::where('school_id', $schoolId)->whereIn('status', ['issued', 'partial', 'paid', 'overdue'])->sum('amount_due');
        $collected = (float) Payment::where('school_id', $schoolId)->where('status', 'paid')->sum('amount');
        $attendanceRows = AttendanceRecord::where('school_id', $schoolId)->where('attendance_date', '>=', now()->subDays(29))->get();
        $staffRows = StaffAttendanceRecord::where('school_id', $schoolId)->where('attendance_date', '>=', now()->subDays(29))->get();
        $monthlyRevenue = collect(range(5, 0))->map(function ($offset) use ($schoolId) {
            $month = now()->subMonths($offset);

            return ['label' => $month->format('M'), 'value' => (float) Payment::where('school_id', $schoolId)->where('status', 'paid')->whereYear('paid_at', $month->year)->whereMonth('paid_at', $month->month)->sum('amount')];
        });
        $monthlyEnrollment = collect(range(5, 0))->map(function ($offset) use ($schoolId) {
            $month = now()->subMonths($offset);

            return ['label' => $month->format('M'), 'value' => Student::where('school_id', $schoolId)->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count()];
        });
        $kpis = [
            'students' => Student::where('school_id', $schoolId)->count(), 'invoiced' => $invoiced, 'collected' => $collected,
            'outstanding' => max(0, $invoiced - $collected), 'collection_rate' => $invoiced > 0 ? round($collected / $invoiced * 100, 1) : 0,
            'attendance_rate' => $attendanceRows->isEmpty() ? null : round($attendanceRows->whereIn('status', ['present', 'late', 'excused'])->count() / $attendanceRows->count() * 100, 1),
            'teacher_punctuality' => $staffRows->isEmpty() ? null : round($staffRows->where('status', 'present')->count() / $staffRows->count() * 100, 1),
            'at_risk' => $scores->where('risk_level', 'high')->count(), 'average_success' => $scores->isEmpty() ? null : round($scores->avg('score'), 1),
        ];
        $interventions = StudentIntervention::where('school_id', $schoolId)->whereIn('status', ['open', 'in_progress'])->with(['student', 'assignee'])->latest()->limit(20)->get();
        $teachers = User::where('school_id', $schoolId)->where('role', 'teacher')->orderBy('name')->get();
        $students = Student::where('school_id', $schoolId)->orderBy('first_name')->get();

        return view('analytics.index', [...compact('scores', 'latestDate', 'kpis', 'monthlyRevenue', 'monthlyEnrollment', 'interventions', 'teachers', 'students'), ...$executive->forSchool($schoolId)]);
    }

    public function recalculate(StudentSuccessCalculator $calculator)
    {
        Student::where('school_id', Auth::user()->school_id)->orderBy('id')->chunkById(200, fn ($students) => $students->each(fn ($student) => $calculator->calculate($student)));

        return back()->with('success', 'Student Success Scores recalculated.');
    }

    public function storeSignal(Request $request, StudentSuccessCalculator $calculator)
    {
        $schoolId = Auth::user()->school_id;
        $data = $request->validate(['student_id' => ['required', Rule::exists('students', 'id')->where('school_id', $schoolId)], 'category' => ['required', Rule::in(['behaviour', 'leadership'])], 'score' => ['required', 'integer', 'min:0', 'max:100'], 'note' => ['required', 'string', 'max:2000'], 'occurred_on' => ['required', 'date', 'before_or_equal:today']]);
        StudentDevelopmentSignal::create([...$data, 'school_id' => $schoolId, 'recorded_by' => Auth::id()]);
        $calculator->calculate(Student::findOrFail($data['student_id']));

        return back()->with('success', 'Development signal recorded and score updated.');
    }

    public function storeIntervention(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $data = $request->validate(['student_id' => ['required', Rule::exists('students', 'id')->where('school_id', $schoolId)], 'assigned_to' => ['nullable', Rule::exists('users', 'id')->where('school_id', $schoolId)], 'title' => ['required', 'string', 'max:255'], 'action_plan' => ['required', 'string', 'max:5000'], 'due_on' => ['nullable', 'date', 'after_or_equal:today']]);
        StudentIntervention::create([...$data, 'school_id' => $schoolId, 'created_by' => Auth::id(), 'status' => 'open']);

        return back()->with('success', 'Intervention plan created.');
    }

    public function completeIntervention(StudentIntervention $intervention)
    {
        abort_unless($intervention->school_id === Auth::user()->school_id, 403);
        $intervention->update(['status' => 'completed', 'completed_at' => now()]);

        return back()->with('success', 'Intervention completed.');
    }

    public function storeStaffAttendance(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $data = $request->validate(['user_id' => ['required', Rule::exists('users', 'id')->where(fn ($q) => $q->where('school_id', $schoolId)->where('role', 'teacher'))], 'attendance_date' => ['required', 'date', 'before_or_equal:today'], 'status' => ['required', Rule::in(['present', 'absent', 'late', 'excused'])], 'scheduled_at' => ['nullable', 'date_format:H:i'], 'checked_in_at' => ['nullable', 'date_format:H:i'], 'note' => ['nullable', 'string', 'max:1000']]);
        StaffAttendanceRecord::updateOrCreate(['user_id' => $data['user_id'], 'attendance_date' => $data['attendance_date']], [...$data, 'school_id' => $schoolId, 'recorded_by' => Auth::id()]);

        return back()->with('success', 'Staff attendance updated.');
    }
}
