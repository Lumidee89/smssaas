<?php

// app/Http/Controllers/ClassController.php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClassController extends Controller
{
    public function index()
    {
        $schoolId = Auth::user()->school_id;

        // Get classes with their teacher and subjects
        $classes = Classes::where('school_id', $schoolId)
            ->with(['teacher', 'subjects'])
            ->orderBy('name')
            ->orderBy('section')
            ->paginate(10);

        // Manually add student count to avoid column name issues
        foreach ($classes as $class) {
            $class->students_count = Student::where('class_id', $class->id)->count();
        }

        // Get statistics
        $totalClasses = Classes::where('school_id', $schoolId)->count();
        $totalStudents = Student::where('school_id', $schoolId)->count();
        $totalSubjects = Subject::where('school_id', $schoolId)->count();
        $averageClassSize = $totalClasses > 0 ? round($totalStudents / $totalClasses) : 0;

        return view('classes.index', compact('classes', 'totalClasses', 'totalStudents', 'totalSubjects', 'averageClassSize'));
    }

    public function create()
    {
        $schoolId = Auth::user()->school_id;
        $teachers = User::where('school_id', $schoolId)
            ->where('role', 'teacher')
            ->get();
        $subjects = Subject::where('school_id', $schoolId)->get();

        return view('classes.create', compact('teachers', 'subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'section' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1|max:500',
            'teacher_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->where('school_id', Auth::user()->school_id)->where('role', 'teacher'))],
            'subjects' => 'array',
            'subjects.*' => [Rule::exists('subjects', 'id')->where('school_id', Auth::user()->school_id)],
        ]);

        // Check if class with same name and section already exists
        $exists = Classes::where('school_id', Auth::user()->school_id)
            ->where('name', $request->name)
            ->where('section', $request->section)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Class with this name and section already exists.')->withInput();
        }

        $class = Classes::create([
            'school_id' => Auth::user()->school_id,
            'name' => $request->name,
            'section' => $request->section,
            'capacity' => $request->capacity,
            'teacher_id' => $request->teacher_id,
        ]);

        // Assign subjects
        if ($request->has('subjects')) {
            $class->subjects()->attach($request->subjects);
        }

        return redirect()->route('classes.index')->with('success', 'Class created successfully.');
    }

    public function show(Classes $class)
    {
        // Check if class belongs to the admin's school
        if ($class->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $class->load(['teacher', 'subjects']);
        $students = Student::where('class_id', $class->id)->orderBy('first_name')->orderBy('last_name')->get();

        $totalStudents = $students->count();
        $availableSeats = $class->capacity ? $class->capacity - $totalStudents : 'Unlimited';
        $subjectsCount = $class->subjects->count();

        return view('classes.show', compact('class', 'students', 'totalStudents', 'availableSeats', 'subjectsCount'));
    }

    public function edit(Classes $class)
    {
        // Check if class belongs to the admin's school
        if ($class->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $schoolId = Auth::user()->school_id;
        $teachers = User::where('school_id', $schoolId)
            ->where('role', 'teacher')
            ->get();
        $subjects = Subject::where('school_id', $schoolId)->get();
        $assignedSubjects = $class->subjects->pluck('id')->toArray();

        return view('classes.edit', compact('class', 'teachers', 'subjects', 'assignedSubjects'));
    }

    public function update(Request $request, Classes $class)
    {
        // Check if class belongs to the admin's school
        if ($class->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'section' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1|max:500',
            'teacher_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->where('school_id', Auth::user()->school_id)->where('role', 'teacher'))],
            'subjects' => 'array',
            'subjects.*' => [Rule::exists('subjects', 'id')->where('school_id', Auth::user()->school_id)],
        ]);

        // Check if another class with same name and section exists
        $exists = Classes::where('school_id', Auth::user()->school_id)
            ->where('name', $request->name)
            ->where('section', $request->section)
            ->where('id', '!=', $class->id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Another class with this name and section already exists.')->withInput();
        }

        $class->update([
            'name' => $request->name,
            'section' => $request->section,
            'capacity' => $request->capacity,
            'teacher_id' => $request->teacher_id,
        ]);

        // Sync subjects
        $class->subjects()->sync($request->subjects ?? []);

        return redirect()->route('classes.index')->with('success', 'Class updated successfully.');
    }

    public function destroy(Classes $class)
    {
        // Check if class belongs to the admin's school
        if ($class->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if class has students
        $studentCount = Student::where('class_id', $class->id)->count();
        if ($studentCount > 0) {
            return redirect()->route('classes.index')->with('error', 'Cannot delete class with enrolled students. Please transfer students first.');
        }

        // Detach all subjects
        $class->subjects()->detach();

        // Delete class
        $class->delete();

        return redirect()->route('classes.index')->with('success', 'Class deleted successfully.');
    }

    public function students(Classes $class)
    {
        // Check if class belongs to the admin's school
        if ($class->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $students = Student::where('class_id', $class->id)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(20);

        return view('classes.students', compact('class', 'students'));
    }

    public function assignTeacher(Request $request, Classes $class)
    {
        // Check if class belongs to the admin's school
        if ($class->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'teacher_id' => ['required', Rule::exists('users', 'id')->where(fn ($q) => $q->where('school_id', Auth::user()->school_id)->where('role', 'teacher'))],
        ]);

        $class->update(['teacher_id' => $request->teacher_id]);

        return redirect()->route('classes.show', $class)->with('success', 'Teacher assigned successfully.');
    }
}
