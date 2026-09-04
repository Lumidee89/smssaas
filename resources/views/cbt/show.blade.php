@extends('layouts.app')
@section('title',$exam->title)
@section('page_title','CBT exam')
@section('content')
<div class="space-y-6">
 <div class="flex flex-wrap items-start justify-between gap-4">
  <div><h2 class="text-3xl font-extrabold text-[#06322C]">{{ $exam->title }}</h2><p class="text-slate-500">{{ $exam->subject->name }} · {{ $exam->schoolClass->full_name }} · {{ $exam->duration_minutes }} minutes</p></div>
  <div class="flex flex-wrap items-center gap-2">@if($exam->attempts_count===0)<a href="{{ route('cbt.edit',$exam) }}" class="rounded-xl border border-[#3A7B72] px-4 py-2 font-bold text-[#06322C]">Edit exam</a>@endif @if($exam->status==='draft')<form method="POST" action="{{ route('cbt.publish',$exam) }}">@csrf<button class="rounded-xl bg-[#06322C] px-5 py-3 font-bold text-white">Publish exam</button></form>@else<a href="{{ route('student.cbt.show',$exam) }}" target="_blank" class="rounded-xl border border-[#3A7B72] px-4 py-2 font-bold text-[#06322C]">Open student portal</a><button type="button" onclick="navigator.clipboard.writeText(@js(route('student.cbt.show',$exam)));this.textContent='Link copied'" class="rounded-xl bg-[#06322C] px-4 py-2 font-bold text-white">Copy exam link</button><span class="rounded-full bg-emerald-100 px-4 py-2 font-bold text-emerald-800">{{ ucfirst($exam->status) }}</span>@endif</div>
 </div>
 <div class="grid gap-4 sm:grid-cols-4">@foreach([['Questions',$exam->questions->count()],['Attempts',$exam->attempts_count],['Opens',$exam->opens_at->format('M j, H:i')],['Closes',$exam->closes_at->format('M j, H:i')]] as [$label,$value])<div class="rounded-2xl bg-white p-5 shadow-sm"><small class="text-slate-400">{{ $label }}</small><strong class="mt-1 block text-xl text-[#06322C]">{{ $value }}</strong></div>@endforeach</div>
 <div class="grid gap-6 xl:grid-cols-2">
  <div class="rounded-2xl bg-white p-6 shadow-sm"><h3 class="mb-4 text-lg font-extrabold">Question set</h3><ol class="space-y-3">@foreach($exam->questions as $question)<li class="rounded-xl bg-slate-50 p-4"><strong>{{ $loop->iteration }}. {{ $question->prompt }}</strong><small class="mt-1 block text-slate-400">{{ ucfirst(str_replace('_',' ',$question->type)) }} · {{ $question->pivot->points }} points</small></li>@endforeach</ol></div>
  <div class="overflow-hidden rounded-2xl bg-white shadow-sm"><h3 class="p-6 pb-3 text-lg font-extrabold">Attempts</h3><table class="w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-400"><tr><th class="p-4">Student</th><th>Status</th><th>Score</th></tr></thead><tbody class="divide-y">@forelse($attempts as $attempt)<tr><td class="p-4 font-semibold">{{ $attempt->student->full_name }}</td><td>{{ ucfirst(str_replace('_',' ',$attempt->status)) }}</td><td>{{ $attempt->submitted_at ? $attempt->score.'/'.$attempt->maximum_score : '—' }}</td></tr>@empty<tr><td colspan="3" class="p-10 text-center text-slate-400">No attempts yet.</td></tr>@endforelse</tbody></table></div>
 </div>
 @php($pendingAnswers=$attempts->getCollection()->flatMap(fn($attempt)=>$attempt->answers->where('requires_review',true)->map(fn($answer)=>[$attempt,$answer])))
 @if($pendingAnswers->isNotEmpty())
 <div class="rounded-2xl bg-white p-6 shadow-sm"><h3 class="text-lg font-extrabold">Manual review queue</h3><p class="mb-4 text-sm text-slate-500">Award points for written responses before releasing the final score.</p><div class="space-y-4">
  @foreach($pendingAnswers as [$attempt,$answer])<form method="POST" action="{{ route('cbt.answers.review',[$exam,$attempt,$answer]) }}" class="rounded-xl border border-slate-100 p-4">@csrf<strong>{{ $attempt->student->full_name }} · {{ $answer->question->prompt }}</strong><p class="my-2 rounded-lg bg-slate-50 p-3 text-sm">{{ implode(', ',$answer->answer??[]) }}</p><div class="flex flex-wrap gap-3"><input type="number" name="awarded_points" min="0" step="0.01" required placeholder="Points" class="rounded-xl border border-slate-300"><input name="review_comment" placeholder="Review comment" class="min-w-64 flex-1 rounded-xl border border-slate-300"><button class="rounded-xl bg-[#06322C] px-5 py-2 font-bold text-white">Complete review</button></div></form>@endforeach
 </div></div>
 @endif
 {{ $attempts->links() }}
</div>
@endsection
