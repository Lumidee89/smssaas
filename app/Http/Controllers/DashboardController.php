<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Payment;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'super_admin') {
            return $this->platformAdminDashboard();
        } elseif (in_array($user->role, ['school_admin', 'principal', 'vice_principal', 'academic_admin', 'bursar'], true)) {
            return $this->schoolAdminDashboard();
        } elseif ($user->role === 'teacher') {
            return $this->teacherDashboard();
        } elseif ($user->role === 'parent') {
            return $this->parentDashboard();
        }

        return view('dashboard.index');
    }

    private function platformAdminDashboard()
    {
        $stats = [
            'total_students' => Student::count(),
            'total_teachers' => User::where('role', 'teacher')->count(),
            'total_parents' => User::where('role', 'parent')->count(),
            'total_classes' => Classes::count(),
            'total_subjects' => Subject::count(),
            'total_revenue' => Payment::where('status', 'paid')->sum('amount'),
            'pending_payments' => Payment::where('status', 'pending')->sum('amount'),
        ];

        $enrollments = Student::whereYear('created_at', date('Y'))->get(['created_at']);
        $monthlyEnrollment = collect(range(1, 12))->map(fn ($month) => (object) [
            'month' => $month,
            'count' => $enrollments->filter(fn ($student) => $student->created_at->month === $month)->count(),
        ]);
        $recentPayments = Payment::with(['student', 'parent'])->latest()->limit(10)->get();
        $recentStudents = Student::with('class')->latest()->limit(10)->get();

        return view('dashboard.school-admin', compact('stats', 'monthlyEnrollment', 'recentPayments', 'recentStudents'));
    }

    private function schoolAdminDashboard()
    {
        $schoolId = Auth::user()->school_id;

        $stats = [
            'total_students' => Student::where('school_id', $schoolId)->count(),
            'total_teachers' => User::where('school_id', $schoolId)->where('role', 'teacher')->count(),
            'total_parents' => User::where('school_id', $schoolId)->where('role', 'parent')->count(),
            'total_classes' => Classes::where('school_id', $schoolId)->count(),
            'total_subjects' => Subject::where('school_id', $schoolId)->count(),
            'total_revenue' => Payment::where('school_id', $schoolId)->where('status', 'paid')->sum('amount'),
            'pending_payments' => Payment::where('school_id', $schoolId)->where('status', 'pending')->sum('amount'),
        ];

        // Monthly enrollment data
        $enrollments = Student::where('school_id', $schoolId)
            ->whereYear('created_at', date('Y'))->get(['created_at']);
        $monthlyEnrollment = collect(range(1, 12))->map(fn ($month) => (object) [
            'month' => $month,
            'count' => $enrollments->filter(fn ($student) => $student->created_at->month === $month)->count(),
        ]);

        // Recent payments
        $recentPayments = Payment::where('school_id', $schoolId)
            ->with(['student', 'parent'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent students
        $recentStudents = Student::where('school_id', $schoolId)
            ->with('class')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.school-admin', compact('stats', 'monthlyEnrollment', 'recentPayments', 'recentStudents'));
    }

    private function teacherDashboard()
    {
        $teacherId = Auth::id();

        $stats = [
            'total_students' => Result::where('teacher_id', $teacherId)
                ->distinct('student_id')->count('student_id'),
            'total_subjects' => Subject::whereHas('teachers', function ($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            })->count(),
            'total_results' => Result::where('teacher_id', $teacherId)->count(),
            'average_score' => Result::where('teacher_id', $teacherId)->avg('score'),
        ];

        $recentResults = Result::where('teacher_id', $teacherId)
            ->with(['student', 'subject'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.teacher', compact('stats', 'recentResults'));
    }

    private function parentDashboard()
    {
        $parentId = Auth::id();

        $students = Student::where('parent_id', $parentId)->with('class')->get();
        $studentIds = $students->pluck('id');

        $recentResults = Result::whereIn('student_id', $studentIds)
            ->with(['student', 'subject'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $payments = Payment::where('parent_id', $parentId)
            ->with('student')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $pendingPayments = Payment::where('parent_id', $parentId)
            ->where('status', 'pending')
            ->sum('amount');

        return view('dashboard.parent', compact('students', 'recentResults', 'payments', 'pendingPayments'));
    }
}
