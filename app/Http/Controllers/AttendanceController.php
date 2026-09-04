<?php

namespace App\Http\Controllers;

use App\Events\AttendanceMarked;
use App\Models\AttendanceRecord;
use App\Models\Classes;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $classes = Classes::where('school_id', $schoolId)->orderBy('name')->get();
        $date = $request->date('date')?->toDateString() ?? now()->toDateString();
        $classId = $request->integer('class_id') ?: $classes->first()?->id;
        $selectedClass = $classId ? Classes::where('school_id', $schoolId)->findOrFail($classId) : null;
        $students = $selectedClass ? Student::where('school_id', $schoolId)->where('class_id', $selectedClass->id)->orderBy('first_name')->get() : collect();
        $records = AttendanceRecord::where('school_id', $schoolId)->whereDate('attendance_date', $date)->whereIn('student_id', $students->pluck('id'))->get()->keyBy('student_id');
        $summary = AttendanceRecord::where('school_id', $schoolId)->whereDate('attendance_date', $date)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('attendance.index', compact('classes', 'selectedClass', 'students', 'records', 'date', 'summary'));
    }

    public function store(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $data = $request->validate(['class_id' => ['required', Rule::exists('classes', 'id')->where('school_id', $schoolId)], 'date' => ['required', 'date', 'before_or_equal:today'], 'attendance' => ['required', 'array'], 'attendance.*' => ['required', Rule::in(AttendanceRecord::STATUSES)], 'notes' => ['sometimes', 'array'], 'notes.*' => ['nullable', 'string', 'max:500']]);
        $students = Student::where('school_id', $schoolId)->where('class_id', $data['class_id'])->whereIn('id', array_keys($data['attendance']))->pluck('id');
        abort_unless($students->count() === count($data['attendance']), 422, 'One or more students do not belong to this class.');
        DB::transaction(function () use ($data, $students, $schoolId) {
            foreach ($students as $studentId) {
                $record = AttendanceRecord::updateOrCreate(['student_id' => $studentId, 'attendance_date' => $data['date']], ['school_id' => $schoolId, 'class_id' => $data['class_id'], 'status' => $data['attendance'][$studentId], 'note' => $data['notes'][$studentId] ?? null, 'recorded_by' => Auth::id()]);
                AttendanceMarked::dispatch($record);
            }
        });

        return back()->with('success', 'Attendance saved successfully.');
    }
}
