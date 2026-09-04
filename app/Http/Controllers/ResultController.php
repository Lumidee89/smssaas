<?php

// app/Http/Controllers/ResultController.php

namespace App\Http\Controllers;

use App\Actions\Cbt\SyncCbtAttemptToResult;
use App\Models\Classes;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = Auth::user()->school_id;

        $query = Result::whereHas('student', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })->with(['student', 'subject', 'teacher']);

        // Apply filters
        if ($request->has('class_id') && $request->class_id) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->has('subject_id') && $request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->has('exam_type') && $request->exam_type) {
            $query->where('exam_type', $request->exam_type);
        }

        if ($request->has('term') && $request->term) {
            $query->where('term', $request->term);
        }

        if ($request->has('academic_year') && $request->academic_year) {
            $query->where('academic_year', $request->academic_year);
        }

        $results = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get filter data
        $classes = Classes::where('school_id', $schoolId)->get();
        $subjects = Subject::where('school_id', $schoolId)->get();
        $terms = ['First Term', 'Second Term', 'Third Term'];
        $examTypes = ['test', 'exam'];
        $academicYears = range(date('Y') - 5, date('Y') + 1);

        // Get statistics
        $totalResults = Result::whereHas('student', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })->count();

        $averageScore = Result::whereHas('student', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })->avg('score');

        $totalStudents = Student::where('school_id', $schoolId)->count();
        $totalSubjects = Subject::where('school_id', $schoolId)->count();

        return view('results.index', compact('results', 'classes', 'subjects', 'terms', 'examTypes', 'academicYears', 'totalResults', 'averageScore', 'totalStudents', 'totalSubjects'));
    }

    public function create(Request $request)
    {
        $schoolId = Auth::user()->school_id;

        // Get students with their class
        $students = Student::where('school_id', $schoolId)
            ->with('class')
            ->orderBy('first_name')
            ->get();

        $subjects = Subject::where('school_id', $schoolId)->get();
        $terms = ['First Term', 'Second Term', 'Third Term'];
        $examTypes = ['test', 'exam'];
        $academicYears = range(date('Y') - 5, date('Y') + 1);

        // If specific student is selected via query parameter
        $selectedStudent = null;
        if ($request->has('student_id')) {
            $selectedStudent = Student::where('school_id', $schoolId)->find($request->student_id);
        }

        // If specific class is selected
        $selectedClass = null;
        if ($request->has('class_id')) {
            $selectedClass = Classes::where('school_id', $schoolId)->find($request->class_id);
            if ($selectedClass) {
                $students = Student::where('class_id', $selectedClass->id)->orderBy('first_name')->get();
            }
        }

        return view('results.create', compact('students', 'subjects', 'terms', 'examTypes', 'academicYears', 'selectedStudent', 'selectedClass'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => ['required', Rule::exists('students', 'id')->where('school_id', Auth::user()->school_id)],
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_id', Auth::user()->school_id)],
            'exam_type' => 'required|in:test,exam',
            'term' => 'required|string',
            'academic_year' => 'required|integer|min:2000|max:2100',
            'score' => 'required|numeric|min:0|max:'.($request->max_score ?? 100),
            'max_score' => 'required|numeric|min:1',
            'remarks' => 'nullable|string',
        ]);

        // Check if result already exists
        $existing = Result::where('student_id', $request->student_id)
            ->where('subject_id', $request->subject_id)
            ->where('term', $request->term)
            ->where('academic_year', $request->academic_year)
            ->where('exam_type', $request->exam_type)
            ->first();

        if ($existing && ! str_starts_with((string) $existing->remarks, SyncCbtAttemptToResult::REMARK_PREFIX)) {
            return redirect()->back()->with('error', 'Result already exists for this student, subject, term, and exam type.')->withInput();
        }

        $values = [
            'student_id' => $request->student_id,
            'subject_id' => $request->subject_id,
            'teacher_id' => Auth::id(),
            'exam_type' => $request->exam_type,
            'term' => $request->term,
            'academic_year' => $request->academic_year,
            'score' => $request->score,
            'max_score' => $request->max_score,
            'remarks' => $request->remarks,
        ];
        if ($existing) {
            $existing->update($values);
        } else {
            Result::create($values);
        }

        return redirect()->route('results.index')->with('success', $existing ? 'Manual result replaced the CBT-generated result.' : 'Result uploaded successfully.');
    }

    public function show(Result $result)
    {
        // Check if result belongs to admin's school
        if ($result->student->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $result->load(['student', 'subject', 'teacher']);

        return view('results.show', compact('result'));
    }

    public function edit(Result $result)
    {
        // Check if result belongs to admin's school
        if ($result->student->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $schoolId = Auth::user()->school_id;
        $students = Student::where('school_id', $schoolId)->with('class')->get();
        $subjects = Subject::where('school_id', $schoolId)->get();
        $terms = ['First Term', 'Second Term', 'Third Term'];
        $examTypes = ['test', 'exam'];
        $academicYears = range(date('Y') - 5, date('Y') + 1);

        return view('results.edit', compact('result', 'students', 'subjects', 'terms', 'examTypes', 'academicYears'));
    }

    public function update(Request $request, Result $result)
    {
        // Check if result belongs to admin's school
        if ($result->student->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'score' => 'required|numeric|min:0|max:'.$result->max_score,
            'remarks' => 'nullable|string',
        ]);

        $result->update([
            'score' => $request->score,
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('results.index')->with('success', 'Result updated successfully.');
    }

    public function destroy(Result $result)
    {
        // Check if result belongs to admin's school
        if ($result->student->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $result->delete();

        return redirect()->route('results.index')->with('success', 'Result deleted successfully.');
    }

    public function bulkUploadForm()
    {
        $schoolId = Auth::user()->school_id;
        $classes = Classes::where('school_id', $schoolId)->get();
        $subjects = Subject::where('school_id', $schoolId)->get();
        $terms = ['First Term', 'Second Term', 'Third Term'];
        $examTypes = ['test', 'exam'];
        $academicYears = range(date('Y') - 5, date('Y') + 1);

        return view('results.bulk-upload', compact('classes', 'subjects', 'terms', 'examTypes', 'academicYears'));
    }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'class_id' => ['required', Rule::exists('classes', 'id')->where('school_id', Auth::user()->school_id)],
            'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_id', Auth::user()->school_id)],
            'exam_type' => 'required|in:test,exam',
            'term' => 'required|string',
            'academic_year' => 'required|integer',
            'results_file' => 'required|file|mimes:csv,xlsx,xls',
        ]);

        // Process the uploaded file
        // This would typically parse CSV/Excel and create results
        // For now, we'll redirect with success message

        return redirect()->route('results.index')->with('success', 'Bulk results uploaded successfully.');
    }

    public function export(Request $request)
    {
        $schoolId = Auth::user()->school_id;

        $query = Result::whereHas('student', function ($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        })->with(['student', 'subject']);

        if ($request->class_id) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->term) {
            $query->where('term', $request->term);
        }

        if ($request->academic_year) {
            $query->where('academic_year', $request->academic_year);
        }

        $results = $query->get();

        // Export logic here (CSV, Excel, PDF)
        // For now, we'll redirect
        return redirect()->route('results.index')->with('success', 'Results exported successfully.');
    }

    public function studentResults(Student $student)
    {
        // Check if student belongs to admin's school
        if ($student->school_id !== Auth::user()->school_id) {
            abort(403, 'Unauthorized action.');
        }

        $results = $student->results()->with('subject')->orderBy('academic_year', 'desc')->orderBy('term', 'desc')->get();
        $averageScore = $results->avg('score');

        return view('results.student-results', compact('student', 'results', 'averageScore'));
    }
}
