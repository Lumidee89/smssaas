<?php

// app/Http/Controllers/StudentController.php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Student;
use App\Models\User;
use App\Services\Saas\EntitlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index()
    {
        $schoolId = Auth::user()->school_id;

        // Get students with their class and parent information
        $students = Student::where('school_id', $schoolId)
            ->with(['class', 'parent'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get statistics
        $totalStudents = Student::where('school_id', $schoolId)->count();
        $totalClasses = Classes::where('school_id', $schoolId)->count();
        $maleStudents = Student::where('school_id', $schoolId)->where('gender', 'male')->count();
        $femaleStudents = Student::where('school_id', $schoolId)->where('gender', 'female')->count();

        return view('students.index', compact('students', 'totalStudents', 'totalClasses', 'maleStudents', 'femaleStudents'));
    }

    public function create()
    {
        $schoolId = Auth::user()->school_id;
        $classes = Classes::where('school_id', $schoolId)->get();
        $parents = User::where('school_id', $schoolId)
            ->where('role', 'parent')
            ->get();

        return view('students.create', compact('classes', 'parents'));
    }

    public function store(Request $request)
    {
        app(EntitlementService::class)->assertCapacity(Auth::user()->school, 'students', Student::where('school_id', Auth::user()->school_id)->count());
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'class_id' => ['nullable', Rule::exists('classes', 'id')->where('school_id', Auth::user()->school_id)],
            'parent_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->where('school_id', Auth::user()->school_id)->where('role', 'parent'))],
            'address' => 'nullable|string',
            'blood_group' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
        ]);

        // Generate unique admission number
        $admissionNumber = 'ADM'.date('Y').str_pad(Student::where('school_id', Auth::user()->school_id)->count() + 1, 5, '0', STR_PAD_LEFT);

        Student::create([
            'school_id' => Auth::user()->school_id,
            'admission_number' => $admissionNumber,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'class_id' => $request->class_id,
            'parent_id' => $request->parent_id,
            'address' => $request->address,
            'blood_group' => $request->blood_group,
            'emergency_contact' => $request->emergency_contact,
        ]);

        return redirect()->route('students.index')->with('success', 'Student created successfully.');
    }

    public function show(Student $student)
    {
        // Check if student belongs to the admin's school
        if ($student->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $student->load(['class', 'parent', 'results.subject', 'payments']);

        $averageScore = $student->results()->avg('score');
        $totalPayments = $student->payments()->where('status', 'paid')->sum('amount');
        $recentResults = $student->results()->with('subject')->orderBy('created_at', 'desc')->limit(5)->get();
        $recentPayments = $student->payments()->orderBy('created_at', 'desc')->limit(5)->get();

        return view('students.show', compact('student', 'averageScore', 'totalPayments', 'recentResults', 'recentPayments'));
    }

    public function createAccount(Request $request, Student $student)
    {
        abort_unless($student->school_id === Auth::user()->school_id, 403);
        $data = $request->validate(['email' => ['required', 'email', Rule::unique('users')->ignore($student->userAccount?->id)], 'password' => ['required', 'string', 'min:8', 'confirmed']]);
        User::updateOrCreate(['student_id' => $student->id], ['school_id' => $student->school_id, 'name' => $student->full_name, 'email' => strtolower($data['email']), 'password' => Hash::make($data['password']), 'role' => 'student']);
        return back()->with('success', 'Student mobile account is ready.');
    }

    public function edit(Student $student)
    {
        // Check if student belongs to the admin's school
        if ($student->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $schoolId = Auth::user()->school_id;
        $classes = Classes::where('school_id', $schoolId)->get();
        $parents = User::where('school_id', $schoolId)
            ->where('role', 'parent')
            ->get();

        return view('students.edit', compact('student', 'classes', 'parents'));
    }

    public function update(Request $request, Student $student)
    {
        // Check if student belongs to the admin's school
        if ($student->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email,'.$student->id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'class_id' => ['nullable', Rule::exists('classes', 'id')->where('school_id', Auth::user()->school_id)],
            'parent_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->where('school_id', Auth::user()->school_id)->where('role', 'parent'))],
            'address' => 'nullable|string',
            'blood_group' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
        ]);

        $student->update($request->all());

        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        // Check if student belongs to the admin's school
        if ($student->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $student->delete();

        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }

    public function results(Student $student)
    {
        if ($student->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $results = $student->results()->with('subject')->orderBy('created_at', 'desc')->paginate(10);

        return view('students.results', compact('student', 'results'));
    }

    public function payments(Student $student)
    {
        if ($student->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $payments = $student->payments()->orderBy('created_at', 'desc')->paginate(10);

        return view('students.payments', compact('student', 'payments'));
    }
}
