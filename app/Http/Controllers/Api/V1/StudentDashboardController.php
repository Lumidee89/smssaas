<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CbtExam;
use App\Models\HomeworkAssignment;
use App\Models\SchoolEvent;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $student = $request->user()->student()->with('class')->firstOrFail();
        $results = $student->results()->with('subject:id,name,code')->latest()->limit(20)->get();
        $homework = HomeworkAssignment::where('school_id', $student->school_id)->where('class_id', $student->class_id)->whereNotNull('published_at')->where('due_at', '>=', now())->with('subject:id,name')->orderBy('due_at')->limit(10)->get();
        $events = SchoolEvent::where('school_id', $student->school_id)->whereIn('audience', ['all', 'students'])->where('starts_at', '>=', now())->orderBy('starts_at')->limit(10)->get();
        $exams = CbtExam::where('school_id', $student->school_id)->where('class_id', $student->class_id)->where('status', 'published')->with('subject:id,name')->orderBy('opens_at')->limit(10)->get();
        return response()->json(['data' => [
            'student' => ['id' => $student->id, 'name' => $student->full_name, 'admission_number' => $student->admission_number, 'class' => $student->class?->full_name],
            'average' => round((float) $results->avg('score'), 1), 'attendance' => $student->attendanceRecords()->latest('attendance_date')->limit(30)->get(),
            'results' => $results, 'homework' => $homework, 'events' => $events, 'exams' => $exams,
        ]]);
    }
}
