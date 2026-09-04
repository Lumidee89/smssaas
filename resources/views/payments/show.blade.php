@extends('layouts.app')

@section('title', $payment->invoice_number)
@section('page_title', 'Payment details')

@section('content')
@php
    $isPaid = $payment->status === 'paid';
    $statusClasses = $isPaid ? 'bg-emerald-50 text-emerald-700' : ($payment->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600');
@endphp
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('payments.index') }}" class="text-sm font-bold text-[#3A7B72]">← Back to payments</a>
            <div class="mt-3 flex flex-wrap items-center gap-3"><h2 class="text-3xl font-extrabold text-slate-900">{{ $payment->invoice_number }}</h2><span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses }}">{{ strtoupper($payment->status) }}</span></div>
            <p class="mt-1 text-slate-500">Created {{ $payment->created_at->format('F j, Y \a\t g:i A') }}</p>
        </div>
        @if($isPaid)<a href="{{ route('payments.receipt', $payment) }}" class="rounded-xl bg-[#06322C] px-5 py-3 text-sm font-bold text-white">View receipt</a>@endif
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.35fr_.65fr]">
        <section class="rounded-2xl bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-6">
                <div><p class="text-sm text-slate-400">Amount</p><strong class="mt-1 block text-4xl text-[#06322C]">₦{{ number_format($payment->amount, 2) }}</strong></div>
                <div class="grid h-12 w-12 place-items-center rounded-2xl bg-[#E7F5F1] text-xl font-extrabold text-[#06322C]">₦</div>
            </div>
            <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Student</dt><dd class="mt-1 font-bold text-slate-800">{{ $payment->student?->full_name ?? 'Student unavailable' }}</dd><dd class="text-sm text-slate-500">{{ $payment->student?->admission_number }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Parent / payer</dt><dd class="mt-1 font-bold text-slate-800">{{ $payment->parent?->name ?? 'Not assigned' }}</dd><dd class="text-sm text-slate-500">{{ $payment->parent?->email }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Payment type</dt><dd class="mt-1 font-bold text-slate-800">{{ str($payment->payment_type)->replace('_', ' ')->title() }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Term</dt><dd class="mt-1 font-bold text-slate-800">{{ $payment->term }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Method</dt><dd class="mt-1 font-bold text-slate-800">{{ $payment->payment_method ? str($payment->payment_method)->replace('_', ' ')->title() : 'Not paid yet' }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Paid on</dt><dd class="mt-1 font-bold text-slate-800">{{ $payment->paid_at?->format('M j, Y · g:i A') ?? '—' }}</dd></div>
            </dl>
            @if($payment->description)<div class="mt-6 rounded-xl bg-slate-50 p-4"><p class="text-xs font-bold uppercase tracking-wide text-slate-400">Description</p><p class="mt-1 text-sm text-slate-700">{{ $payment->description }}</p></div>@endif
        </section>

        <aside class="space-y-4">
            <section class="rounded-2xl bg-[#06322C] p-5 text-white shadow-sm"><p class="text-sm text-[#7ED3C4]">Transaction reference</p><p class="mt-2 break-all font-bold">{{ $payment->provider_reference ?: ($payment->transaction_id ?: 'Awaiting payment') }}</p>@if($payment->provider)<p class="mt-4 text-xs uppercase tracking-wider text-white/60">Processed by {{ $payment->provider }}</p>@endif</section>
            @if(!$isPaid && auth()->user()->role === 'school_admin')
                <form method="POST" action="{{ route('payments.process', $payment) }}" class="space-y-4 rounded-2xl bg-white p-5 shadow-sm">
                    @csrf
                    <div><h3 class="font-bold text-slate-900">Record payment</h3><p class="mt-1 text-sm text-slate-500">Use this for an externally completed or offline payment.</p></div>
                    <div><label for="payment_method" class="mb-2 block text-sm font-bold text-slate-700">Payment method</label><select id="payment_method" name="payment_method" required class="w-full rounded-xl px-3 py-3 text-sm"><option value="bank_transfer">Bank transfer</option><option value="cash">Cash</option></select></div>
                    <div><label for="transaction_id" class="mb-2 block text-sm font-bold text-slate-700">Transaction ID <span class="font-normal text-slate-400">(required for transfer)</span></label><input id="transaction_id" name="transaction_id" class="w-full rounded-xl px-3 py-3 text-sm"></div>
                    @if($errors->any())<div class="rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
                    <button class="w-full rounded-xl bg-[#06322C] py-3 text-sm font-bold text-white">Mark as paid</button>
                </form>
            @endif
        </aside>
    </div>
</div>
@endsection
