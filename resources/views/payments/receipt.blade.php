@extends('layouts.app')

@section('title', 'Receipt '.$payment->invoice_number)
@section('page_title', 'Payment receipt')

@push('styles')
<style>@media print {.sidebar, nav, .print-hidden { display:none!important } body,.bg-white { background:#fff!important } .flex-1 { overflow:visible!important } }</style>
@endpush

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="print-hidden mb-4 flex justify-between"><a href="{{ route('payments.show', $payment) }}" class="text-sm font-bold text-[#3A7B72]">← Payment details</a><button onclick="window.print()" class="rounded-xl bg-[#06322C] px-5 py-2 text-sm font-bold text-white">Print receipt</button></div>
    <article class="rounded-2xl bg-white p-8 shadow-sm">
        <header class="flex items-start justify-between gap-4 border-b border-slate-200 pb-6"><div><img src="{{ asset('images/logo.png') }}" alt="SchoolOS" class="h-12 max-w-[180px] object-contain object-left"><p class="mt-4 text-sm font-bold uppercase tracking-wider text-[#3A7B72]">Official payment receipt</p></div><div class="text-right"><span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">PAID</span><p class="mt-3 text-sm text-slate-500">{{ $payment->paid_at?->format('F j, Y') }}</p></div></header>
        <div class="py-8 text-center"><p class="text-sm text-slate-400">Amount paid</p><strong class="mt-2 block text-4xl text-[#06322C]">₦{{ number_format($payment->amount, 2) }}</strong></div>
        <dl class="grid gap-x-8 gap-y-5 border-y border-slate-200 py-6 sm:grid-cols-2">
            <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Receipt number</dt><dd class="mt-1 font-bold">{{ $payment->invoice_number }}</dd></div>
            <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Gateway reference</dt><dd class="mt-1 break-all font-bold">{{ $payment->provider_reference ?: ($payment->transaction_id ?: '—') }}</dd></div>
            <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Student</dt><dd class="mt-1 font-bold">{{ $payment->student?->full_name ?? 'Student unavailable' }}</dd><dd class="text-sm text-slate-500">{{ $payment->student?->admission_number }}</dd></div>
            <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Parent / payer</dt><dd class="mt-1 font-bold">{{ $payment->parent?->name ?? 'Not assigned' }}</dd></div>
            <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Purpose</dt><dd class="mt-1 font-bold">{{ str($payment->payment_type)->replace('_', ' ')->title() }} · {{ $payment->term }}</dd></div>
            <div><dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Payment method</dt><dd class="mt-1 font-bold">{{ str($payment->payment_method ?? 'Not specified')->replace('_', ' ')->title() }}</dd></div>
        </dl>
        <footer class="pt-6 text-center text-xs text-slate-400">This receipt was generated electronically by Plus36 SchoolOS.</footer>
    </article>
</div>
@endsection
