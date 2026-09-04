<?php

// app/Http/Controllers/SubjectController.php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index()
    {
        $schoolId = Auth::user()->school_id;

        // Get subjects with their teachers and classes
        $subjects = Subject::where('school_id', $schoolId)
            ->with(['teachers', 'classes'])
            ->orderBy('name')
            ->paginate(10);

        // Get statistics
        $totalSubjects = Subject::where('school_id', $schoolId)->count();
        $totalTeachers = User::where('school_id', $schoolId)->where('role', 'teacher')->count();
        $totalClasses = Classes::where('school_id', $schoolId)->count();
        $avgCreditHours = Subject::where('school_id', $schoolId)->avg('credit_hours');

        return view('subjects.index', compact('subjects', 'totalSubjects', 'totalTeachers', 'totalClasses', 'avgCreditHours'));
    }

    public function create()
    {
        $schoolId = Auth::user()->school_id;
        $teachers = User::where('school_id', $schoolId)
            ->where('role', 'teacher')
            ->get();
        $classes = Classes::where('school_id', $schoolId)->get();

        return view('subjects.create', compact('teachers', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code',
            'credit_hours' => 'nullable|integer|min:1|max:10',
            'description' => 'nullable|string',
            'teachers' => 'array',
            'teachers.*' => [Rule::exists('users', 'id')->where(fn ($q) => $q->where('school_id', Auth::user()->school_id)->where('role', 'teacher'))],
            'classes' => 'array',
            'classes.*' => [Rule::exists('classes', 'id')->where('school_id', Auth::user()->school_id)],
        ]);

        // Check if subject with same code already exists
        $exists = Subject::where('school_id', Auth::user()->school_id)
            ->where('code', $request->code)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Subject with this code already exists.')->withInput();
        }

        $subject = Subject::create([
            'school_id' => Auth::user()->school_id,
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'credit_hours' => $request->credit_hours ?? 1,
            'description' => $request->description,
        ]);

        // Assign teachers
        if ($request->has('teachers')) {
            $subject->teachers()->attach($request->teachers);
        }

        // Assign classes
        if ($request->has('classes')) {
            $subject->classes()->attach($request->classes);
        }

        return redirect()->route('subjects.index')->with('success', 'Subject created successfully.');
    }

    public function show(Subject $subject)
    {
        // Check if subject belongs to the admin's school
        if ($subject->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $subject->load(['teachers', 'classes', 'results.student']);

        $totalTeachers = $subject->teachers->count();
        $totalClasses = $subject->classes->count();
        $totalResults = $subject->results->count();
        $averageScore = $subject->results->avg('score');

        return view('subjects.show', compact('subject', 'totalTeachers', 'totalClasses', 'totalResults', 'averageScore'));
    }

    public function edit(Subject $subject)
    {
        // Check if subject belongs to the admin's school
        if ($subject->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $schoolId = Auth::user()->school_id;
        $teachers = User::where('school_id', $schoolId)
            ->where('role', 'teacher')
            ->get();
        $classes = Classes::where('school_id', $schoolId)->get();
        $assignedTeachers = $subject->teachers->pluck('id')->toArray();
        $assignedClasses = $subject->classes->pluck('id')->toArray();

        return view('subjects.edit', compact('subject', 'teachers', 'classes', 'assignedTeachers', 'assignedClasses'));
    }

    public function update(Request $request, Subject $subject)
    {
        // Check if subject belongs to the admin's school
        if ($subject->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code,'.$subject->id,
            'credit_hours' => 'nullable|integer|min:1|max:10',
            'description' => 'nullable|string',
            'teachers' => 'array',
            'teachers.*' => [Rule::exists('users', 'id')->where(fn ($q) => $q->where('school_id', Auth::user()->school_id)->where('role', 'teacher'))],
            'classes' => 'array',
            'classes.*' => [Rule::exists('classes', 'id')->where('school_id', Auth::user()->school_id)],
        ]);

        // Check if another subject with same code exists
        $exists = Subject::where('school_id', Auth::user()->school_id)
            ->where('code', $request->code)
            ->where('id', '!=', $subject->id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Another subject with this code already exists.')->withInput();
        }

        $subject->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'credit_hours' => $request->credit_hours ?? 1,
            'description' => $request->description,
        ]);

        // Sync teachers and classes
        $subject->teachers()->sync($request->teachers ?? []);
        $subject->classes()->sync($request->classes ?? []);

        return redirect()->route('subjects.index')->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        // Check if subject belongs to the admin's school
        if ($subject->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if subject has results
        if ($subject->results()->count() > 0) {
            return redirect()->route('subjects.index')->with('error', 'Cannot delete subject with existing results. Please delete results first.');
        }

        // Detach all relationships
        $subject->teachers()->detach();
        $subject->classes()->detach();

        // Delete subject
        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'Subject deleted successfully.');
    }

    public function assignTeacher(Request $request, Subject $subject)
    {
        // Check if subject belongs to the admin's school
        if ($subject->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'teacher_id' => ['required', Rule::exists('users', 'id')->where(fn ($q) => $q->where('school_id', Auth::user()->school_id)->where('role', 'teacher'))],
        ]);

        $subject->teachers()->attach($request->teacher_id);

        return redirect()->route('subjects.show', $subject)->with('success', 'Teacher assigned successfully.');
    }

    public function assignClass(Request $request, Subject $subject)
    {
        // Check if subject belongs to the admin's school
        if ($subject->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'class_id' => ['required', Rule::exists('classes', 'id')->where('school_id', Auth::user()->school_id)],
        ]);

        $subject->classes()->attach($request->class_id);

        return redirect()->route('subjects.show', $subject)->with('success', 'Class assigned successfully.');
    }
}
