<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Campus;
use App\Models\Course;
use App\Models\CourseRegistration;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AcademicStructureController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = $request->user()->school_id;

        return view('academic-structure.index', [
            'campuses' => Campus::where('school_id', $schoolId)->withCount(['faculties', 'departments'])->orderByDesc('is_main')->get(),
            'faculties' => Faculty::where('school_id', $schoolId)->with(['campus', 'dean'])->withCount('departments')->get(),
            'departments' => Department::where('school_id', $schoolId)->with(['campus', 'faculty', 'head'])->withCount('courses')->get(),
            'courses' => Course::where('school_id', $schoolId)->with('department')->withCount('registrations')->get(),
            'registrations' => CourseRegistration::where('school_id', $schoolId)->with(['student', 'course', 'academicTerm.academicYear'])->latest('registered_at')->limit(50)->get(),
            'students' => Student::where('school_id', $schoolId)->orderBy('first_name')->get(),
            'staff' => User::where('school_id', $schoolId)->whereIn('role', ['school_admin', 'teacher'])->orderBy('name')->get(),
            'terms' => AcademicTerm::where('school_id', $schoolId)->with('academicYear')->latest('starts_on')->get(),
            'section' => $request->string('section')->value() ?: 'campuses',
        ]);
    }

    public function storeCampus(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'code' => ['required', 'alpha_dash', 'max:30', Rule::unique('campuses')->where('school_id', $schoolId)], 'email' => ['nullable', 'email'], 'phone' => ['nullable', 'string', 'max:40'], 'address' => ['nullable', 'string', 'max:2000'], 'is_main' => ['nullable', 'boolean']]);
        DB::transaction(function () use ($schoolId, $data) {
            if ($data['is_main'] ?? false) {
                Campus::where('school_id', $schoolId)->update(['is_main' => false]);
            }
            Campus::create($data + ['school_id' => $schoolId]);
        });

        return back()->with('success', 'Campus created.');
    }

    public function storeFaculty(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $data = $request->validate(['campus_id' => ['nullable', Rule::exists('campuses', 'id')->where('school_id', $schoolId)], 'name' => ['required', 'string', 'max:255'], 'code' => ['required', 'alpha_dash', 'max:30', Rule::unique('faculties')->where('school_id', $schoolId)], 'dean_id' => ['nullable', Rule::exists('users', 'id')->where('school_id', $schoolId)]]);
        Faculty::create($data + ['school_id' => $schoolId]);

        return back()->with('success', 'Faculty created.');
    }

    public function storeDepartment(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $data = $request->validate(['campus_id' => ['nullable', Rule::exists('campuses', 'id')->where('school_id', $schoolId)], 'faculty_id' => ['nullable', Rule::exists('faculties', 'id')->where('school_id', $schoolId)], 'name' => ['required', 'string', 'max:255'], 'code' => ['required', 'alpha_dash', 'max:30', Rule::unique('departments')->where('school_id', $schoolId)], 'head_id' => ['nullable', Rule::exists('users', 'id')->where('school_id', $schoolId)]]);
        Department::create($data + ['school_id' => $schoolId]);

        return back()->with('success', 'Department created.');
    }

    public function storeCourse(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $data = $request->validate(['department_id' => ['nullable', Rule::exists('departments', 'id')->where('school_id', $schoolId)], 'name' => ['required', 'string', 'max:255'], 'code' => ['required', 'max:40', Rule::unique('courses')->where('school_id', $schoolId)], 'credit_units' => ['required', 'integer', 'min:1', 'max:30'], 'level' => ['nullable', 'integer', 'min:1', 'max:9999'], 'description' => ['nullable', 'string', 'max:5000']]);
        Course::create($data + ['school_id' => $schoolId]);

        return back()->with('success', 'Course created.');
    }

    public function registerCourse(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $data = $request->validate(['student_id' => ['required', Rule::exists('students', 'id')->where('school_id', $schoolId)], 'course_id' => ['required', Rule::exists('courses', 'id')->where('school_id', $schoolId)], 'academic_term_id' => ['required', Rule::exists('academic_terms', 'id')->where('school_id', $schoolId)]]);
        CourseRegistration::firstOrCreate($data, ['school_id' => $schoolId, 'status' => 'registered', 'registered_by' => $request->user()->id, 'registered_at' => now()]);

        return back()->with('success', 'Course registration saved.');
    }
}
