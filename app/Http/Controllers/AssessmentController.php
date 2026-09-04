<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\Classes;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\Notifications\ParentNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Support\SchoolRole;

class AssessmentController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $query = Assessment::where('school_id', $schoolId)->with(['term.academicYear', 'subject', 'scores'])->latest();
        if (Auth::user()->role === 'teacher') {
            $query->where('teacher_id', Auth::id());
        }if ($request->filled('status')) {
            $query->where('status', $request->status);
        }$assessments = $query->paginate(20)->withQueryString();

        return view('assessments.index', compact('assessments'));
    }

    public function create()
    {
        return view('assessments.create', $this->formData());
    }

    public function store(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $data = $request->validate($this->rules($schoolId));
        $teacherId = Auth::user()->role === 'teacher' ? Auth::id() : $data['teacher_id'];
        $assessment = Assessment::create([...$data, 'school_id' => $schoolId, 'teacher_id' => $teacherId, 'status' => 'draft']);

        return redirect()->route('assessments.show', $assessment)->with('success', 'Assessment created. Add student scores below.');
    }

    public function show(Assessment $assessment)
    {
        $this->tenant($assessment);
        $assessment->load(['term.academicYear', 'subject', 'scores']);
        $students = Student::where('school_id', $assessment->school_id)->where('class_id', $assessment->class_id)->orderBy('first_name')->get();
        $scores = $assessment->scores->keyBy('student_id');

        return view('assessments.show', compact('assessment', 'students', 'scores'));
    }

    public function saveScores(Request $request, Assessment $assessment)
    {
        $this->canEdit($assessment);
        abort_unless(in_array($assessment->status, ['draft', 'returned'], true), 422, 'Approved or submitted scores cannot be edited.');
        $data = $request->validate(['scores' => ['required', 'array'], 'scores.*' => ['nullable', 'numeric', 'min:0', 'max:'.$assessment->maximum_score], 'comments' => ['sometimes', 'array'], 'comments.*' => ['nullable', 'string', 'max:1000']]);
        $studentIds = Student::where('school_id', $assessment->school_id)->where('class_id', $assessment->class_id)->whereIn('id', array_keys($data['scores']))->pluck('id');
        abort_unless($studentIds->count() === count($data['scores']), 422, 'One or more students are outside the assessment class.');
        DB::transaction(function () use ($studentIds, $data, $assessment) {
            foreach ($studentIds as $id) {
                if ($data['scores'][$id] === null || $data['scores'][$id] === '') {
                    AssessmentScore::where('assessment_id', $assessment->id)->where('student_id', $id)->delete();

                    continue;
                }AssessmentScore::updateOrCreate(['assessment_id' => $assessment->id, 'student_id' => $id], ['school_id' => $assessment->school_id, 'score' => $data['scores'][$id], 'teacher_comment' => $data['comments'][$id] ?? null, 'entered_by' => Auth::id()]);
            }
        });

        return back()->with('success', 'Scores saved as draft.');
    }

    public function submit(Assessment $assessment)
    {
        $this->canEdit($assessment);
        abort_unless(in_array($assessment->status, ['draft', 'returned'], true), 422);
        abort_if($assessment->scores()->count() === 0, 422, 'Enter at least one score before submission.');
        $assessment->update(['status' => 'submitted', 'approved_by' => null, 'approved_at' => null]);

        return back()->with('success', 'Assessment submitted for approval.');
    }

    public function approve(Assessment $assessment)
    {
        $this->tenant($assessment);
        abort_unless(Auth::user()->isAcademicManager(), 403);
        abort_unless($assessment->status === 'submitted', 422, 'Only submitted assessments can be approved.');
        $assessment->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);
        app(ParentNotificationService::class)->assessmentApproved($assessment);

        return back()->with('success', 'Assessment approved and included in academic calculations.');
    }

    public function returnForCorrection(Request $request, Assessment $assessment)
    {
        $this->tenant($assessment);
        abort_unless(Auth::user()->isAcademicManager(), 403);
        $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        abort_unless($assessment->status === 'submitted', 422);
        $assessment->update(['status' => 'returned', 'approved_by' => null, 'approved_at' => null]);

        return back()->with('success', 'Assessment returned for correction: '.$request->reason);
    }

    private function formData(): array
    {
        $schoolId = Auth::user()->school_id;

        return ['terms' => AcademicTerm::where('school_id', $schoolId)->with('academicYear')->latest('starts_on')->get(), 'classes' => Classes::where('school_id', $schoolId)->orderBy('name')->get(), 'subjects' => Subject::where('school_id', $schoolId)->orderBy('name')->get(), 'teachers' => User::where('school_id', $schoolId)->where('role', 'teacher')->orderBy('name')->get()];
    }

    private function rules(int $schoolId): array
    {
        return ['academic_term_id' => ['required', Rule::exists('academic_terms', 'id')->where('school_id', $schoolId)], 'class_id' => ['required', Rule::exists('classes', 'id')->where('school_id', $schoolId)], 'subject_id' => ['required', Rule::exists('subjects', 'id')->where('school_id', $schoolId)], 'teacher_id' => [Auth::user()->role !== SchoolRole::TEACHER ? 'required' : 'nullable', Rule::exists('users', 'id')->where(fn ($q) => $q->where('school_id', $schoolId)->where('role', 'teacher'))], 'title' => ['required', 'string', 'max:255'], 'type' => ['required', Rule::in(['assignment', 'quiz', 'test', 'exam', 'project'])], 'maximum_score' => ['required', 'numeric', 'min:1', 'max:1000'], 'weight_percentage' => ['required', 'numeric', 'min:0.01', 'max:100'], 'assessment_date' => ['nullable', 'date']];
    }

    private function tenant(Assessment $assessment): void
    {
        abort_unless($assessment->school_id === Auth::user()->school_id, 403);
    }

    private function canEdit(Assessment $assessment): void
    {
        $this->tenant($assessment);
        abort_unless(Auth::user()->isAcademicManager() || $assessment->teacher_id === Auth::id(), 403);
    }
}
