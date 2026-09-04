<?php

// app/Http/Controllers/TeacherController.php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\User;
use App\Services\Saas\EntitlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function index()
    {
        $schoolId = Auth::user()->school_id;

        // Get teachers with their subjects
        $teachers = User::where('school_id', $schoolId)
            ->where('role', 'teacher')
            ->with(['taughtSubjects', 'school'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get statistics
        $totalTeachers = User::where('school_id', $schoolId)->where('role', 'teacher')->count();
        $totalSubjects = Subject::where('school_id', $schoolId)->count();
        $activeTeachers = User::where('school_id', $schoolId)->where('role', 'teacher')->where('email_verified_at', '!=', null)->count();

        return view('teachers.index', compact('teachers', 'totalTeachers', 'totalSubjects', 'activeTeachers'));
    }

    public function create()
    {
        $schoolId = Auth::user()->school_id;
        $subjects = Subject::where('school_id', $schoolId)->get();

        return view('teachers.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        app(EntitlementService::class)->assertCapacity(Auth::user()->school, 'staff', User::where('school_id', Auth::user()->school_id)->where('role', 'teacher')->count());
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'subjects' => 'array',
            'subjects.*' => [Rule::exists('subjects', 'id')->where('school_id', Auth::user()->school_id)],
            'qualification' => 'nullable|string',
            'address' => 'nullable|string',
            'hire_date' => 'nullable|date',
        ]);

        // Create teacher
        $teacher = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'teacher',
            'school_id' => Auth::user()->school_id,
            'email_verified_at' => now(), // Auto verify
        ]);

        // Assign subjects
        if ($request->has('subjects')) {
            $teacher->taughtSubjects()->attach($request->subjects);
        }

        // Store additional teacher info if you have a teacher_details table
        // You can create a teacher_details table for qualification, hire_date, etc.

        return redirect()->route('teachers.index')->with('success', 'Teacher created successfully. Login credentials have been sent to their email.');
    }

    public function show(User $teacher)
    {
        // Check if teacher belongs to the admin's school
        if ($teacher->role !== 'teacher' || $teacher->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $teacher->load(['taughtSubjects', 'results.student']);

        // Get statistics
        $totalStudentsTaught = $teacher->results()->distinct('student_id')->count('student_id');
        $totalResultsUploaded = $teacher->results()->count();
        $averageScoreGiven = $teacher->results()->avg('score');
        $recentResults = $teacher->results()->with(['student', 'subject'])->orderBy('created_at', 'desc')->limit(10)->get();

        return view('teachers.show', compact('teacher', 'totalStudentsTaught', 'totalResultsUploaded', 'averageScoreGiven', 'recentResults'));
    }

    public function edit(User $teacher)
    {
        // Check if teacher belongs to the admin's school
        if ($teacher->role !== 'teacher' || $teacher->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $schoolId = Auth::user()->school_id;
        $subjects = Subject::where('school_id', $schoolId)->get();
        $assignedSubjects = $teacher->taughtSubjects->pluck('id')->toArray();

        return view('teachers.edit', compact('teacher', 'subjects', 'assignedSubjects'));
    }

    public function update(Request $request, User $teacher)
    {
        // Check if teacher belongs to the admin's school
        if ($teacher->role !== 'teacher' || $teacher->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$teacher->id,
            'phone' => 'nullable|string|max:20',
            'subjects' => 'array',
            'subjects.*' => [Rule::exists('subjects', 'id')->where('school_id', Auth::user()->school_id)],
        ]);

        // Update teacher info
        $teacher->name = $request->name;
        $teacher->email = $request->email;
        $teacher->phone = $request->phone;
        $teacher->save();

        // Sync subjects
        $teacher->taughtSubjects()->sync($request->subjects ?? []);

        return redirect()->route('teachers.index')->with('success', 'Teacher updated successfully.');
    }

    public function destroy(User $teacher)
    {
        // Check if teacher belongs to the admin's school
        if ($teacher->role !== 'teacher' || $teacher->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        // Detach all subjects first
        $teacher->taughtSubjects()->detach();

        // Delete teacher
        $teacher->delete();

        return redirect()->route('teachers.index')->with('success', 'Teacher deleted successfully.');
    }

    public function resetPassword(Request $request, User $teacher)
    {
        // Check if teacher belongs to the admin's school
        if ($teacher->role !== 'teacher' || $teacher->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $newPassword = Str::random(10);
        $teacher->password = Hash::make($newPassword);
        $teacher->save();

        // Send email with new password (implement mail functionality)
        // Mail::to($teacher->email)->send(new TeacherPasswordResetMail($teacher, $newPassword));

        return redirect()->route('teachers.index')->with('success', 'Password reset successfully. New password: '.$newPassword);
    }
}
