@extends('layouts.app')

@section('title', 'Record payment')
@section('page_title', 'Record payment')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <a href="{{ route('payments.index') }}" class="text-sm font-bold text-[#3A7B72]">← Back to payments</a>
        <h2 class="mt-3 text-3xl font-extrabold text-slate-900">Create a payment record</h2>
        <p class="mt-1 text-slate-500">Issue a payment request for a student and record its purpose.</p>
    </div>

    <form method="POST" action="{{ route('payments.store') }}" class="space-y-5 rounded-2xl bg-white p-6 shadow-sm">
        @csrf
        <div>
            <label for="student_id" class="mb-2 block text-sm font-bold text-slate-700">Student</label>
            <select id="student_id" name="student_id" required class="w-full rounded-xl px-4 py-3">
                <option value="">Select a student</option>
                @foreach($students as $student)
                    <option value="{{ $student->id }}" @selected(old('student_id', $selectedStudent?->id) == $student->id)>{{ $student->full_name }} · {{ $student->admission_number }}{{ $student->class ? ' · '.$student->class->name : '' }}</option>
                @endforeach
            </select>
            @error('student_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div><label for="amount" class="mb-2 block text-sm font-bold text-slate-700">Amount (₦)</label><input id="amount" name="amount" type="number" min="1" step="0.01" required value="{{ old('amount') }}" class="w-full rounded-xl px-4 py-3">@error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
            <div><label for="term" class="mb-2 block text-sm font-bold text-slate-700">Term</label><input id="term" name="term" required value="{{ old('term') }}" placeholder="e.g. First term 2026/2027" class="w-full rounded-xl px-4 py-3">@error('term')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        </div>
        <div><label for="payment_type" class="mb-2 block text-sm font-bold text-slate-700">Payment type</label><select id="payment_type" name="payment_type" required class="w-full rounded-xl px-4 py-3"><option value="">Select payment type</option>@foreach(['tuition' => 'Tuition', 'exam_fee' => 'Examination fee', 'library_fee' => 'Library fee', 'sports_fee' => 'Sports fee', 'other' => 'Other'] as $value => $label)<option value="{{ $value }}" @selected(old('payment_type') === $value)>{{ $label }}</option>@endforeach</select>@error('payment_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <div><label for="description" class="mb-2 block text-sm font-bold text-slate-700">Description <span class="font-normal text-slate-400">(optional)</span></label><textarea id="description" name="description" rows="4" class="w-full rounded-xl px-4 py-3" placeholder="Add a note about this payment">{{ old('description') }}</textarea>@error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <div class="flex justify-end gap-3 border-t border-slate-100 pt-5"><a href="{{ route('payments.index') }}" class="rounded-xl px-5 py-3 text-sm font-bold text-slate-500">Cancel</a><button class="rounded-xl bg-[#06322C] px-6 py-3 text-sm font-bold text-white">Create payment</button></div>
    </form>
</div>
@endsection
