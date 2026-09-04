@extends('layouts.app')
@section('title', 'Edit CBT exam')
@section('page_title', 'Edit CBT exam')

@section('content')
@php($selectedQuestions = collect(old('question_ids', $exam->questions->pluck('id')->all()))->map(fn ($id) => (string) $id))
<div class="mx-auto max-w-6xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><a href="{{ route('cbt.show', $exam) }}" class="mb-3 inline-flex text-sm font-bold text-[#3A7B72]">← Back to exam</a><h1 class="text-3xl font-extrabold">Edit exam</h1><p class="mt-1 text-sm text-slate-500">Update scheduling, rules, instructions, and questions before students begin.</p></div>
        <span class="w-fit rounded-full bg-[#e7f5f1] px-4 py-2 text-xs font-extrabold text-[#06322C]">{{ strtoupper($exam->status) }}</span>
    </div>

    @if($errors->any())<div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><strong>Could not update the exam:</strong> {{ $errors->first() }}</div>@endif

    <section class="mb-6 rounded-3xl border border-[#7ED3C4] bg-white p-6 shadow-sm sm:p-7" x-data="{ type: '{{ old('type', 'single_choice') }}' }">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"><div><p class="text-xs font-bold uppercase tracking-[.18em] text-[#3A7B72]">Step 2 · {{ $exam->subject?->name }}</p><h2 class="mt-2 text-xl font-extrabold">Enter a question for this exam</h2><p class="text-sm text-slate-500">The question is automatically assigned to {{ $exam->subject?->name }} and attached to {{ $exam->title }}.</p></div><span class="w-fit rounded-full bg-[#e7f5f1] px-3 py-2 text-xs font-bold text-[#06322C]">{{ $exam->questions->count() }} added</span></div>
        <form method="POST" action="{{ route('cbt.questions.store') }}" class="mt-6 grid gap-4 md:grid-cols-2">
            @csrf<input type="hidden" name="exam_id" value="{{ $exam->id }}">
            <div><label class="mb-2 block text-sm font-bold">Question type</label><select name="type" x-model="type" class="w-full rounded-xl px-4 py-3"><option value="single_choice">Single choice</option><option value="multiple_choice">Multiple choice</option><option value="true_false">True / false</option><option value="short_text">Written answer</option></select></div>
            <div class="grid grid-cols-2 gap-3"><div><label class="mb-2 block text-sm font-bold">Points</label><input type="number" name="default_points" value="{{ old('default_points',1) }}" step="0.01" min="0.01" max="1000" required class="w-full rounded-xl px-4 py-3"></div><div><label class="mb-2 block text-sm font-bold">Difficulty</label><select name="difficulty" class="w-full rounded-xl px-4 py-3"><option value="easy">Easy</option><option value="medium" selected>Medium</option><option value="hard">Hard</option></select></div></div>
            <div class="md:col-span-2"><label class="mb-2 block text-sm font-bold">Question</label><textarea name="prompt" required rows="3" maxlength="10000" class="w-full rounded-xl p-4" placeholder="Enter the question students will answer">{{ old('prompt') }}</textarea></div>
            <div x-show="type === 'single_choice' || type === 'multiple_choice'" class="space-y-3 md:col-span-2"><p class="text-sm font-bold">Answer options</p><div class="grid gap-3 md:grid-cols-2">@for($i=0;$i<4;$i++)<input name="options[]" value="{{ old('options.'.$i) }}" :required="type === 'single_choice' || type === 'multiple_choice'" placeholder="Option {{ $i+1 }}" class="w-full rounded-xl px-4 py-3">@endfor</div><p class="text-xs text-slate-400">Enter the correct answer exactly as it appears in the options.</p></div>
            <div><label class="mb-2 block text-sm font-bold">Correct answer</label><input name="correct_answers[]" required value="{{ old('correct_answers.0') }}" :placeholder="type === 'true_false' ? 'true or false' : type === 'short_text' ? 'Teacher reference answer' : 'Exact option text'" class="w-full rounded-xl px-4 py-3"></div>
            <div x-show="type === 'multiple_choice'"><label class="mb-2 block text-sm font-bold">Second correct answer <small class="font-normal text-slate-400">optional</small></label><input name="correct_answers[]" value="{{ old('correct_answers.1') }}" placeholder="Exact option text" class="w-full rounded-xl px-4 py-3"></div>
            <div class="md:col-span-2"><label class="mb-2 block text-sm font-bold">Explanation <small class="font-normal text-slate-400">optional</small></label><textarea name="explanation" rows="2" maxlength="5000" class="w-full rounded-xl p-4"></textarea></div>
            <button class="rounded-xl bg-[#06322C] px-6 py-3 font-extrabold text-white md:col-span-2">Add question to this exam</button>
        </form>
    </section>

    <form method="POST" action="{{ route('cbt.update', $exam) }}" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        @csrf @method('PUT')
        <div class="space-y-6">
            <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm sm:p-7">
                <h2 class="text-lg font-extrabold">Exam details</h2><p class="mb-6 text-sm text-slate-500">The name and academic placement students will see.</p>
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2"><label class="mb-2 block text-sm font-bold">Title</label><input name="title" value="{{ old('title', $exam->title) }}" required maxlength="255" class="w-full rounded-xl px-4 py-3"></div>
                    <div><label class="mb-2 block text-sm font-bold">Academic term</label><select name="academic_term_id" required class="w-full rounded-xl px-4 py-3">@foreach($terms as $term)<option value="{{ $term->id }}" @selected((string)old('academic_term_id',$exam->academic_term_id)===(string)$term->id)>{{ $term->academicYear->name }} · {{ $term->name }}</option>@endforeach</select></div>
                    <div><label class="mb-2 block text-sm font-bold">Class</label><select name="class_id" required class="w-full rounded-xl px-4 py-3">@foreach($classes as $class)<option value="{{ $class->id }}" @selected((string)old('class_id',$exam->class_id)===(string)$class->id)>{{ $class->full_name }}</option>@endforeach</select></div>
                    <div><label class="mb-2 block text-sm font-bold">Subject</label><select name="subject_id" required class="w-full rounded-xl px-4 py-3">@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected((string)old('subject_id',$exam->subject_id)===(string)$subject->id)>{{ $subject->name }}</option>@endforeach</select><p class="mt-1 text-xs text-slate-400">Selected questions must belong to this subject.</p></div>
                    <div><label class="mb-2 block text-sm font-bold">Pass mark (%)</label><input type="number" name="pass_percentage" value="{{ old('pass_percentage', $exam->pass_percentage) }}" min="0" max="100" step="0.01" required class="w-full rounded-xl px-4 py-3"></div>
                    <div class="md:col-span-2"><label class="mb-2 block text-sm font-bold">Instructions</label><textarea name="instructions" rows="4" maxlength="5000" class="w-full rounded-xl p-4" placeholder="Instructions shown before the exam starts">{{ old('instructions', $exam->instructions) }}</textarea></div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm sm:p-7">
                <h2 class="text-lg font-extrabold">Question set</h2><p class="mb-5 text-sm text-slate-500">Choose at least one active question. Existing selections are checked.</p>
                <div class="max-h-[520px] space-y-2 overflow-y-auto rounded-2xl border border-slate-200 p-3">
                    @forelse($questions as $question)
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl p-3 transition hover:bg-slate-50"><input type="checkbox" name="question_ids[]" value="{{ $question->id }}" @checked($selectedQuestions->contains((string)$question->id)) class="mt-1 h-4 w-4 accent-[#06322C]"><span><strong class="text-sm">{{ $question->subject->name }} · {{ ucfirst(str_replace('_',' ',$question->type)) }}</strong><small class="mt-1 block text-slate-500">{{ $question->prompt }}</small><span class="mt-2 inline-block rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-500">{{ $question->default_points }} points · {{ ucfirst($question->difficulty) }}</span></span></label>
                    @empty<p class="p-8 text-center text-sm text-slate-400">There are no active questions in the question bank.</p>@endforelse
                </div>
            </section>
        </div>

        <aside class="h-fit space-y-6 xl:sticky xl:top-6">
            <section class="rounded-3xl bg-[#06322C] p-6 text-white shadow-xl shadow-[#06322C]/10">
                <h2 class="text-lg font-extrabold">Schedule and rules</h2>
                <div class="mt-5 space-y-4">
                    <div><label class="mb-2 block text-sm font-bold text-white/80">Duration (minutes)</label><input type="number" name="duration_minutes" value="{{ old('duration_minutes',$exam->duration_minutes) }}" min="1" max="480" required class="w-full rounded-xl px-4 py-3 text-slate-900"></div>
                    <div><label class="mb-2 block text-sm font-bold text-white/80">Opens at</label><input type="datetime-local" name="opens_at" value="{{ old('opens_at',$exam->opens_at->format('Y-m-d\TH:i')) }}" required class="w-full rounded-xl px-4 py-3 text-slate-900"></div>
                    <div><label class="mb-2 block text-sm font-bold text-white/80">Closes at</label><input type="datetime-local" name="closes_at" value="{{ old('closes_at',$exam->closes_at->format('Y-m-d\TH:i')) }}" required class="w-full rounded-xl px-4 py-3 text-slate-900"></div>
                    <div><label class="mb-2 block text-sm font-bold text-white/80">Maximum attempts</label><input type="number" name="max_attempts" value="{{ old('max_attempts',$exam->max_attempts) }}" min="1" max="5" required class="w-full rounded-xl px-4 py-3 text-slate-900"></div>
                    <div><label class="mb-2 block text-sm font-bold text-white/80">New access code <small class="font-normal text-white/50">optional</small></label><input name="access_code" minlength="4" maxlength="50" class="w-full rounded-xl px-4 py-3 text-slate-900" placeholder="Leave blank to keep current code"><label class="mt-2 flex items-center gap-2 text-xs text-white/70"><input type="checkbox" name="clear_access_code" value="1"> Remove the current access code</label></div>
                    <label class="flex items-center gap-3 rounded-xl bg-white/10 p-3 text-sm font-semibold"><input type="checkbox" name="shuffle_questions" value="1" @checked(old('shuffle_questions',$exam->shuffle_questions)) class="h-4 w-4"> Shuffle questions</label>
                    <label class="flex items-center gap-3 rounded-xl bg-white/10 p-3 text-sm font-semibold"><input type="checkbox" name="shuffle_options" value="1" @checked(old('shuffle_options',$exam->shuffle_options)) class="h-4 w-4"> Shuffle answer options</label>
                </div>
            </section>
            <button class="w-full rounded-2xl bg-[#7ED3C4] px-5 py-4 font-extrabold text-[#06322C] shadow-sm">Save exam changes</button>
            <p class="text-center text-xs leading-5 text-slate-400">The exam becomes locked after the first student starts an attempt.</p>
        </aside>
    </form>
</div>
@endsection
