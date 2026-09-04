<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateAiContent;
use App\Models\AiGenerationRequest;
use App\Models\AttendanceRecord;
use App\Models\FeeInvoice;
use App\Models\Student;
use App\Models\StudentSuccessScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AiCopilotController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $requests = AiGenerationRequest::where('school_id', $user->school_id)->when(! $user->isAcademicManager(), fn ($q) => $q->where('requested_by', $user->id))->latest()->paginate(20);

        return view('ai.index', compact('requests'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_if(AiGenerationRequest::where('requested_by', $user->id)->where('created_at', '>=', now()->startOfDay())->count() >= 50, 429, 'Daily copilot generation limit reached.');
        $type = $request->validate(['type' => ['required', Rule::in(['lesson_plan', 'exam_questions', 'report_comment', 'principal_summary'])]])['type'];
        abort_if($type === 'principal_summary' && ! in_array($user->role, ['school_admin', 'principal', 'vice_principal'], true), 403);
        $input = match ($type) {
            'lesson_plan' => $request->validate(['subject' => ['required', 'string', 'max:150'], 'class_level' => ['required', 'string', 'max:100'], 'topic' => ['required', 'string', 'max:255'], 'duration_minutes' => ['required', 'integer', 'min:10', 'max:240'], 'learning_context' => ['nullable', 'string', 'max:3000']]),
            'exam_questions' => $request->validate(['subject' => ['required', 'string', 'max:150'], 'class_level' => ['required', 'string', 'max:100'], 'topic' => ['required', 'string', 'max:255'], 'question_count' => ['required', 'integer', 'min:1', 'max:20'], 'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard', 'mixed'])], 'curriculum_notes' => ['nullable', 'string', 'max:3000']]),
            'report_comment' => $request->validate(['subject' => ['required', 'string', 'max:150'], 'class_level' => ['required', 'string', 'max:100'], 'score_percentage' => ['required', 'numeric', 'min:0', 'max:100'], 'performance_evidence' => ['required', 'string', 'max:3000'], 'teacher_tone' => ['required', Rule::in(['encouraging', 'formal', 'concise'])]]),
            'principal_summary' => $this->principalMetrics($user->school_id),
        };
        $generation = AiGenerationRequest::create(['school_id' => $user->school_id, 'requested_by' => $user->id, 'type' => $type, 'status' => 'queued', 'input' => $input]);
        GenerateAiContent::dispatch($generation->id);

        return redirect()->route('ai.show', $generation)->with('success', 'Copilot request queued. Refresh if it is still processing.');
    }

    public function show(AiGenerationRequest $generation)
    {
        $user = Auth::user();
        abort_unless($generation->school_id === $user->school_id && ($user->isAcademicManager() || $generation->requested_by === $user->id), 403);

        return view('ai.show', compact('generation'));
    }

    private function principalMetrics(int $schoolId): array
    {
        $scores = StudentSuccessScore::where('school_id', $schoolId)->whereDate('calculated_on', StudentSuccessScore::where('school_id', $schoolId)->max('calculated_on'))->get();
        $attendance = AttendanceRecord::where('school_id', $schoolId)->where('attendance_date', '>=', now()->subDays(6))->get();

        return ['period' => now()->subDays(6)->toDateString().' to '.now()->toDateString(), 'enrollment' => Student::where('school_id', $schoolId)->count(), 'attendance_rate' => $attendance->isEmpty() ? null : round($attendance->whereIn('status', ['present', 'late', 'excused'])->count() / $attendance->count() * 100, 1), 'high_risk_students' => $scores->where('risk_level', 'high')->count(), 'average_success_score' => $scores->isEmpty() ? null : round($scores->avg('score'), 1), 'fees_invoiced' => (float) FeeInvoice::where('school_id', $schoolId)->sum('amount_due'), 'fees_collected' => (float) FeeInvoice::where('school_id', $schoolId)->sum('amount_paid')];
    }
}
