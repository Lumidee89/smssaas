<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Classes;
use App\Models\HomeworkAssignment;
use App\Models\SchoolEvent;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PlanningController extends Controller
{
    public function index()
    {
        $schoolId = Auth::user()->school_id;
        return view('planning.index', [
            'homework' => HomeworkAssignment::where('school_id', $schoolId)->with(['class', 'subject'])->latest('due_at')->paginate(15, ['*'], 'homework_page'),
            'events' => SchoolEvent::where('school_id', $schoolId)->orderBy('starts_at')->paginate(15, ['*'], 'event_page'),
            'classes' => Classes::where('school_id', $schoolId)->orderBy('name')->get(),
            'subjects' => Subject::where('school_id', $schoolId)->orderBy('name')->get(),
            'terms' => AcademicTerm::where('school_id', $schoolId)->orderByDesc('starts_on')->get(),
        ]);
    }

    public function storeHomework(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $data = $request->validate([
            'class_id' => ['required', Rule::exists('classes', 'id')->where('school_id', $schoolId)],
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_id', $schoolId)],
            'academic_term_id' => ['nullable', Rule::exists('academic_terms', 'id')->where('school_id', $schoolId)],
            'title' => ['required', 'string', 'max:255'], 'instructions' => ['required', 'string', 'max:10000'],
            'due_at' => ['required', 'date', 'after:now'], 'publish_now' => ['nullable', 'boolean'],
        ]);
        HomeworkAssignment::create([...$data, 'school_id' => $schoolId, 'teacher_id' => Auth::id(), 'published_at' => ($data['publish_now'] ?? false) ? now() : null]);
        return back()->with('success', 'Homework saved.');
    }

    public function storeEvent(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $data = $request->validate([
            'academic_term_id' => ['nullable', Rule::exists('academic_terms', 'id')->where('school_id', $schoolId)],
            'title' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:10000'],
            'audience' => ['required', Rule::in(['all', 'parents', 'students', 'teachers'])],
            'starts_at' => ['required', 'date'], 'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'], 'location' => ['nullable', 'string', 'max:255'],
        ]);
        SchoolEvent::create([...$data, 'school_id' => $schoolId, 'created_by' => Auth::id()]);
        return back()->with('success', 'School event created.');
    }
}
