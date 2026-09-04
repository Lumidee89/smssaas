@extends('layouts.app') @section('title','CBT') @section('page_title','Computer-based testing')
@section('content')
<div class="space-y-6">
 <div class="flex flex-wrap items-end justify-between gap-4"><div><h2 class="text-3xl font-extrabold text-[#06322C]">CBT examinations</h2><p class="text-slate-500">Build secure timed assessments and monitor attempts.</p></div><a href="{{ route('cbt.create') }}" class="rounded-xl bg-[#06322C] px-5 py-3 font-bold text-white">+ Create exam</a></div>
 <div class="overflow-hidden rounded-2xl bg-white shadow-sm"><table class="w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-400"><tr><th class="p-4">Exam</th><th>Class</th><th>Questions</th><th>Attempts</th><th>Status</th></tr></thead><tbody class="divide-y">@forelse($exams as $exam)<tr><td class="p-4"><a class="font-bold text-[#06322C]" href="{{ route('cbt.show',$exam) }}">{{ $exam->title }}</a><small class="block text-slate-400">{{ $exam->subject->name }} · {{ $exam->duration_minutes }} min</small></td><td>{{ $exam->schoolClass->full_name }}</td><td>{{ $exam->questions_count }}</td><td>{{ $exam->attempts_count }}</td><td><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold">{{ ucfirst($exam->status) }}</span></td></tr>@empty<tr><td colspan="5" class="p-12 text-center text-slate-400">No CBT exams created yet.</td></tr>@endforelse</tbody></table></div>
 {{ $exams->links() }}
</div>
@endsection
