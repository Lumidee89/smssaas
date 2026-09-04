@extends('layouts.app')
@section('title', 'Create CBT exam')
@section('page_title', 'Create CBT exam')

@section('content')
<form method="POST" action="{{ route('cbt.exams.store') }}" class="mx-auto max-w-5xl space-y-6">
    @csrf
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"><div><h1 class="text-3xl font-extrabold text-[#06322C]">Create CBT exam</h1><p class="mt-1 text-sm text-slate-500">Set up the exam first, then enter questions for its selected subject.</p></div><span class="w-fit rounded-full bg-[#e7f5f1] px-4 py-2 text-xs font-bold text-[#06322C]">STEP 1 OF 2</span></div>
    @if($errors->any())<div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm sm:p-8">
        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2"><label class="mb-2 block text-sm font-bold">Exam title</label><input name="title" value="{{ old('title') }}" required maxlength="255" placeholder="e.g. First Term Mathematics CBT" class="w-full rounded-xl px-4 py-3"></div>
            <div><label class="mb-2 block text-sm font-bold">Academic term</label><select name="academic_term_id" required class="w-full rounded-xl px-4 py-3"><option value="">Select term</option>@foreach($terms as $term)<option value="{{ $term->id }}" @selected(old('academic_term_id')==$term->id)>{{ $term->academicYear->name }} · {{ $term->name }}</option>@endforeach</select></div>
            <div><label class="mb-2 block text-sm font-bold">Class</label><select name="class_id" required class="w-full rounded-xl px-4 py-3"><option value="">Select class</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected(old('class_id')==$class->id)>{{ $class->full_name }}</option>@endforeach</select></div>
            <div><label class="mb-2 block text-sm font-bold">Exam subject</label><select name="subject_id" required class="w-full rounded-xl px-4 py-3"><option value="">Select subject</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected(old('subject_id')==$subject->id)>{{ $subject->name }}</option>@endforeach</select><p class="mt-1 text-xs text-slate-400">Questions entered in step 2 will automatically use this subject.</p></div>
            <div><label class="mb-2 block text-sm font-bold">Duration (minutes)</label><input type="number" name="duration_minutes" value="{{ old('duration_minutes',60) }}" min="1" max="480" required class="w-full rounded-xl px-4 py-3"></div>
            <div><label class="mb-2 block text-sm font-bold">Opens at</label><input type="datetime-local" name="opens_at" value="{{ old('opens_at') }}" required class="w-full rounded-xl px-4 py-3"></div>
            <div><label class="mb-2 block text-sm font-bold">Closes at</label><input type="datetime-local" name="closes_at" value="{{ old('closes_at') }}" required class="w-full rounded-xl px-4 py-3"></div>
            <div><label class="mb-2 block text-sm font-bold">Maximum attempts</label><input type="number" name="max_attempts" value="{{ old('max_attempts',1) }}" min="1" max="5" required class="w-full rounded-xl px-4 py-3"></div>
            <div><label class="mb-2 block text-sm font-bold">Pass mark (%)</label><input type="number" name="pass_percentage" value="{{ old('pass_percentage',50) }}" min="0" max="100" step="0.01" required class="w-full rounded-xl px-4 py-3"></div>
            <div><label class="mb-2 block text-sm font-bold">Access code <small class="font-normal text-slate-400">optional</small></label><input name="access_code" minlength="4" maxlength="50" value="{{ old('access_code') }}" class="w-full rounded-xl px-4 py-3"></div>
            <div class="md:col-span-2"><label class="mb-2 block text-sm font-bold">Instructions</label><textarea name="instructions" rows="4" maxlength="5000" class="w-full rounded-xl p-4">{{ old('instructions') }}</textarea></div>
            <label class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4 text-sm font-semibold"><input type="checkbox" name="shuffle_questions" value="1" @checked(old('shuffle_questions',true))> Shuffle questions</label>
            <label class="flex items-center gap-3 rounded-2xl bg-slate-50 p-4 text-sm font-semibold"><input type="checkbox" name="shuffle_options" value="1" @checked(old('shuffle_options',true))> Shuffle answer options</label>
        </div>
    </section>
    <button class="w-full rounded-2xl bg-[#06322C] py-4 font-extrabold text-white">Create draft and add questions →</button>
</form>
@endsection
