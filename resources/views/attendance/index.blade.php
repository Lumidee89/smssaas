@extends('layouts.app')
@section('title','Attendance')
@section('page_title','Attendance')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div><h2 class="text-3xl font-extrabold">Daily attendance</h2><p class="text-slate-500">Record one auditable status for every student.</p></div>
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="date" name="date" value="{{ $date }}" class="rounded-xl border-slate-200">
            <select name="class_id" class="rounded-xl border-slate-200">@foreach($classes as $class)<option value="{{ $class->id }}" @selected($selectedClass?->id===$class->id)>{{ $class->full_name }}</option>@endforeach</select>
            <button class="rounded-xl bg-[#06322C] px-5 py-3 font-bold text-white">Load register</button>
        </form>
    </div>
    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">@foreach(['present'=>'Present','absent'=>'Absent','late'=>'Late','excused'=>'Excused'] as $key=>$label)<div class="rounded-2xl bg-white p-5 shadow-sm"><small class="text-slate-400">{{ $label }}</small><strong class="mt-1 block text-3xl">{{ $summary[$key] ?? 0 }}</strong></div>@endforeach</div>
    @if($selectedClass)
    <form method="POST" action="{{ route('attendance.store') }}" class="overflow-hidden rounded-2xl bg-white shadow-sm">@csrf
        <input type="hidden" name="date" value="{{ $date }}"><input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
        <div class="flex justify-between border-b p-5"><div><h3 class="font-bold">{{ $selectedClass->full_name }}</h3><p class="text-sm text-slate-400">{{ date('l, F j, Y', strtotime($date)) }}</p></div><button class="rounded-xl bg-[#06322C] px-5 py-2 text-sm font-bold text-white">Save attendance</button></div>
        <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-50 text-left text-xs uppercase text-slate-400"><tr><th class="p-4">Student</th><th>Status</th><th>Note</th></tr></thead><tbody class="divide-y">
        @forelse($students as $student) @php($record=$records->get($student->id))
        <tr><td class="p-4"><strong>{{ $student->full_name }}</strong><small class="block text-slate-400">{{ $student->admission_number }}</small></td><td><select name="attendance[{{ $student->id }}]" class="rounded-lg border-slate-200">@foreach(['present','absent','late','excused'] as $status)<option value="{{ $status }}" @selected(($record?->status ?? 'present')===$status)>{{ ucfirst($status) }}</option>@endforeach</select></td><td class="pr-4"><input name="notes[{{ $student->id }}]" value="{{ $record?->note }}" placeholder="Optional note" class="w-full rounded-lg border-slate-200"></td></tr>
        @empty <tr><td colspan="3" class="p-10 text-center text-slate-400">No students are assigned to this class.</td></tr> @endforelse
        </tbody></table></div>
    </form>
    @else <div class="rounded-2xl bg-white p-10 text-center text-slate-400">Create a class before recording attendance.</div> @endif
</div>
@endsection
