<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentSuccessScore;
use App\Models\HomeworkAssignment;
use App\Models\SchoolEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentDashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $parent = $request->user();
        $children = $this->children($parent)->load(['class', 'attendanceRecords' => fn ($q) => $q->latest('attendance_date')->limit(1)]);
        $studentIds = $children->pluck('id');
        $announcements = Announcement::published()->where('school_id', $parent->school_id)->whereIn('audience', ['all', 'parents'])->latest('published_at')->limit(10)->get(['id', 'title', 'body', 'published_at', 'expires_at']);
        $events = SchoolEvent::where('school_id', $parent->school_id)->whereIn('audience', ['all', 'parents'])
            ->where('starts_at', '>=', now())->orderBy('starts_at')->limit(10)->get(['id', 'title', 'description', 'starts_at', 'ends_at', 'location']);
        $homework = HomeworkAssignment::where('school_id', $parent->school_id)->whereIn('class_id', $children->pluck('class_id'))
            ->whereNotNull('published_at')->where('due_at', '>=', now())->with('subject:id,name')->orderBy('due_at')->limit(20)->get()
            ->map(fn ($item) => ['id' => $item->id, 'class_id' => $item->class_id, 'subject' => $item->subject?->name, 'title' => $item->title, 'instructions' => $item->instructions, 'due_at' => $item->due_at->toISOString()]);

        return response()->json(['data' => ['children' => $children->map(fn ($child) => ['id' => $child->id, 'class_id' => $child->class_id, 'admission_number' => $child->admission_number, 'name' => $child->full_name, 'class' => $child->class?->full_name, 'current_average' => round((float) $child->results()->avg('score'), 1), 'today_attendance' => $child->attendanceRecords->first()?->status, 'outstanding_fees' => (float) $child->payments()->where('status', 'pending')->sum('amount')]), 'announcements' => $announcements, 'upcoming_events' => $events, 'homework' => $homework]]);
    }

    public function progress(Request $request, Student $student): JsonResponse
    {
        abort_unless($this->children($request->user())->contains('id', $student->id), 403);
        $results = $student->results()->with('subject:id,name,code')->latest()->get();
        $cbtAttempts = $student->cbtAttempts()
            ->whereNotNull('submitted_at')
            ->with(['exam.subject:id,name,code', 'exam.term.academicYear:id,name'])
            ->latest('submitted_at')
            ->get();

        return response()->json(['data' => ['student' => ['id' => $student->id, 'name' => $student->full_name], 'average' => round((float) $results->avg('score'), 1), 'subjects' => $results->map(fn ($r) => ['subject' => $r->subject?->name, 'code' => $r->subject?->code, 'assessment' => $r->exam_type, 'term' => $r->term, 'academic_year' => $r->academic_year, 'score' => (float) $r->score, 'max_score' => (float) $r->max_score, 'percentage' => round($r->percentage, 1), 'grade' => $r->grade, 'teacher_comment' => $r->remarks]), 'cbt_attempts' => $cbtAttempts->map(fn ($attempt) => ['id' => $attempt->id, 'exam' => $attempt->exam->title, 'subject' => $attempt->exam->subject?->name, 'term' => $attempt->exam->term?->name, 'academic_year' => $attempt->exam->term?->academicYear?->name, 'attempt_number' => $attempt->attempt_number, 'status' => $attempt->status, 'score' => (float) $attempt->score, 'maximum_score' => (float) $attempt->maximum_score, 'percentage' => $attempt->percentage === null ? null : (float) $attempt->percentage, 'passed' => $attempt->status === 'graded' ? (float) $attempt->percentage >= (float) $attempt->exam->pass_percentage : null, 'submitted_at' => $attempt->submitted_at?->toISOString()])]]);
    }

    public function calendar(Request $request): JsonResponse
    {
        $years = AcademicYear::where('school_id', $request->user()->school_id)
            ->with(['terms' => fn ($query) => $query->orderBy('sequence')])
            ->orderByDesc('is_current')
            ->orderByDesc('starts_on')
            ->get();

        return response()->json(['data' => [
            'school' => $request->user()->school?->name,
            'years' => $years->map(fn ($year) => [
                'id' => $year->id,
                'name' => $year->name,
                'starts_on' => $year->starts_on->toDateString(),
                'ends_on' => $year->ends_on->toDateString(),
                'is_current' => $year->is_current,
                'terms' => $year->terms->map(fn ($term) => [
                    'id' => $term->id,
                    'name' => $term->name,
                    'sequence' => $term->sequence,
                    'starts_on' => $term->starts_on->toDateString(),
                    'ends_on' => $term->ends_on->toDateString(),
                    'is_current' => $term->is_current,
                ])->values(),
            ])->values(),
        ]]);
    }

    public function attendance(Request $request, Student $student): JsonResponse
    {
        abort_unless($this->children($request->user())->contains('id', $student->id), 403);
        $records = $student->attendanceRecords()->latest('attendance_date')->paginate(31);

        return response()->json(['data' => $records->items(), 'meta' => ['current_page' => $records->currentPage(), 'last_page' => $records->lastPage(), 'total' => $records->total()]]);
    }

    public function recommendations(Request $request, Student $student): JsonResponse
    {
        abort_unless($this->children($request->user())->contains('id', $student->id), 403);
        $score = StudentSuccessScore::where('student_id', $student->id)->latest('calculated_on')->first();

        return response()->json(['data' => ['student' => ['id' => $student->id, 'name' => $student->full_name], 'success_score' => $score ? (float) $score->score : null, 'risk_level' => $score?->risk_level, 'recommendations' => $score?->recommendations ?? ['Ask the school to calculate the latest Student Success Score.'], 'risk_factors' => $score?->risk_factors ?? [], 'calculated_on' => $score?->calculated_on?->toDateString(), 'disclaimer' => 'Recommendations are educational guidance generated from recorded school data and should be reviewed with the student’s teacher.']]);
    }

    private function children($parent)
    {
        $linked = $parent->children()->get();

        return $linked->isNotEmpty() ? $linked : Student::where('parent_id', $parent->id)->get();
    }
}
