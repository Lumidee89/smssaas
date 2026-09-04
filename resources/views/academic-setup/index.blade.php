@extends('layouts.app')
@section('title', 'Academic setup')
@section('page_title', 'Academic setup')

@section('content')
<div class="space-y-6" x-data="{ yearForm: {{ $errors->has('academic_year_id') || $errors->has('sequence') ? 'false' : 'true' }} }">
    <div><p class="text-xs font-bold uppercase tracking-[.2em] text-[#3A7B72]">School calendar</p><h1 class="mt-2 text-3xl font-extrabold">Academic years and terms</h1><p class="mt-1 text-sm text-slate-500">Create these periods before setting up assessments, CBT exams, attendance, and transcripts.</p></div>

    @if($errors->any())<div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><strong>Check the form:</strong> {{ $errors->first() }}</div>@endif

    <div class="grid gap-6 xl:grid-cols-[380px_minmax(0,1fr)]">
        <section class="h-fit rounded-3xl bg-white p-6 shadow-sm">
            <div class="grid grid-cols-2 rounded-xl bg-slate-100 p-1 text-sm font-bold"><button @click="yearForm=true" :class="yearForm ? 'bg-white text-[#06322C] shadow-sm':'text-slate-500'" class="rounded-lg px-3 py-2">New year</button><button @click="yearForm=false" :class="!yearForm ? 'bg-white text-[#06322C] shadow-sm':'text-slate-500'" class="rounded-lg px-3 py-2">New term</button></div>
            <form x-show="yearForm" method="POST" action="{{ route('academic-setup.years.store') }}" class="mt-6 space-y-4">@csrf
                <div><label class="mb-1 block text-sm font-bold">Academic year name</label><input name="name" required value="{{ old('name') }}" placeholder="2026/2027" class="w-full rounded-xl px-4 py-3"></div>
                <div class="grid grid-cols-2 gap-3"><div><label class="mb-1 block text-sm font-bold">Starts</label><input type="date" name="starts_on" required value="{{ old('starts_on') }}" class="w-full rounded-xl px-3 py-3"></div><div><label class="mb-1 block text-sm font-bold">Ends</label><input type="date" name="ends_on" required value="{{ old('ends_on') }}" class="w-full rounded-xl px-3 py-3"></div></div>
                <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="is_current" value="1" class="h-4 w-4"> Make this the current year</label>
                <button class="w-full rounded-xl bg-[#06322C] py-3 font-extrabold text-white">Create academic year</button>
            </form>
            <form x-show="!yearForm" x-cloak method="POST" action="{{ route('academic-setup.terms.store') }}" class="mt-6 space-y-4">@csrf
                <div><label class="mb-1 block text-sm font-bold">Academic year</label><select name="academic_year_id" required class="w-full rounded-xl px-4 py-3"><option value="">Select year</option>@foreach($years as $year)<option value="{{ $year->id }}" @selected(old('academic_year_id')==$year->id)>{{ $year->name }}</option>@endforeach</select></div>
                <div><label class="mb-1 block text-sm font-bold">Term name</label><input name="name" required value="{{ old('name') }}" placeholder="First Term" class="w-full rounded-xl px-4 py-3"></div>
                <div><label class="mb-1 block text-sm font-bold">Sequence</label><input type="number" name="sequence" min="1" max="10" required value="{{ old('sequence', 1) }}" class="w-full rounded-xl px-4 py-3"></div>
                <div class="grid grid-cols-2 gap-3"><div><label class="mb-1 block text-sm font-bold">Starts</label><input type="date" name="starts_on" required value="{{ old('starts_on') }}" class="w-full rounded-xl px-3 py-3"></div><div><label class="mb-1 block text-sm font-bold">Ends</label><input type="date" name="ends_on" required value="{{ old('ends_on') }}" class="w-full rounded-xl px-3 py-3"></div></div>
                <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="is_current" value="1" class="h-4 w-4"> Make this the current term</label>
                <button class="w-full rounded-xl bg-[#06322C] py-3 font-extrabold text-white" @disabled($years->isEmpty())>Create academic term</button>
            </form>
        </section>

        <section class="space-y-4">
            @forelse($years as $year)
                <article class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3"><div><div class="flex items-center gap-2"><h2 class="text-xl font-extrabold">{{ $year->name }}</h2>@if($year->is_current)<span class="rounded-full bg-[#e7f5f1] px-3 py-1 text-xs font-bold text-[#06322C]">CURRENT YEAR</span>@endif</div><p class="mt-1 text-sm text-slate-500">{{ $year->starts_on->format('M j, Y') }} – {{ $year->ends_on->format('M j, Y') }}</p></div><span class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-500">{{ $year->terms->count() }} terms</span></div>
                    <div class="mt-5 grid gap-3 md:grid-cols-3">@forelse($year->terms as $term)<div class="rounded-2xl border {{ $term->is_current ? 'border-[#7ED3C4] bg-[#e7f5f1]/50':'border-slate-200' }} p-4"><div class="flex items-center justify-between"><p class="font-extrabold">{{ $term->name }}</p><span class="text-xs font-bold text-slate-400">#{{ $term->sequence }}</span></div><p class="mt-2 text-xs text-slate-500">{{ $term->starts_on->format('M j') }} – {{ $term->ends_on->format('M j, Y') }}</p>@if($term->is_current)<p class="mt-2 text-[10px] font-extrabold text-[#3A7B72]">CURRENT TERM</p>@endif</div>@empty<div class="rounded-2xl border border-dashed border-slate-300 p-5 text-sm text-slate-400 md:col-span-3">No terms added to this academic year yet.</div>@endforelse</div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center"><h2 class="font-extrabold">No academic calendar configured</h2><p class="mt-1 text-sm text-slate-500">Create your first academic year, then add its terms.</p></div>
            @endforelse
        </section>
    </div>
</div>
@endsection
