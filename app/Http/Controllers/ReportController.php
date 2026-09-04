<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Payment;
use App\Models\Result;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $section = in_array($request->query('section'), ['students', 'finance', 'academics'], true)
            ? $request->query('section') : 'students';

        $students = Student::where('school_id', $schoolId)->with('class')->latest()->limit(10)->get();
        $payments = Payment::where('school_id', $schoolId)->with('student')->latest()->limit(10)->get();
        $results = Result::whereHas('student', fn ($query) => $query->where('school_id', $schoolId))
            ->with(['student', 'subject'])->latest()->limit(10)->get();

        $stats = [
            'students' => Student::where('school_id', $schoolId)->count(),
            'classes' => Classes::where('school_id', $schoolId)->count(),
            'collected' => Payment::where('school_id', $schoolId)->where('status', 'paid')->sum('amount'),
            'outstanding' => Payment::where('school_id', $schoolId)->where('status', 'pending')->sum('amount'),
            'average' => round((float) Result::whereHas('student', fn ($query) => $query->where('school_id', $schoolId))->avg('score'), 1),
        ];

        return view('reports.index', compact('section', 'students', 'payments', 'results', 'stats'));
    }
}
