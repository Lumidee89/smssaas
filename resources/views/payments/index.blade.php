@extends('layouts.app')

@section('title', 'Payments')
@section('page_title', 'Payments')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-wider text-[#3A7B72]">Finance</p>
            <h2 class="mt-1 text-3xl font-extrabold text-slate-900">Payment ledger</h2>
            <p class="mt-1 text-slate-500">Track collections, pending payments, and transaction receipts.</p>
        </div>
        @if(auth()->user()->role === 'school_admin')
            <a href="{{ route('payments.create') }}" class="rounded-xl bg-[#06322C] px-5 py-3 text-sm font-bold text-white hover:bg-[#0a463d]">+ Record payment</a>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        @foreach([
            ['Collected', $stats['total_collected'], 'bg-[#06322C] text-white', 'text-[#7ED3C4]'],
            ['Pending', $stats['pending_amount'], 'bg-white text-slate-900', 'text-amber-600'],
            ['Transactions', $stats['total_transactions'], 'bg-white text-slate-900', 'text-[#3A7B72]'],
        ] as [$label, $value, $cardClass, $accentClass])
            <section class="rounded-2xl p-5 shadow-sm {{ $cardClass }}">
                <p class="text-sm opacity-70">{{ $label }}</p>
                <strong class="mt-2 block text-2xl {{ $accentClass }}">
                    {{ $label === 'Transactions' ? number_format($value) : '₦'.number_format($value, 2) }}
                </strong>
            </section>
        @endforeach
    </div>

    <form method="GET" action="{{ route('payments.index') }}" class="grid gap-3 rounded-2xl bg-white p-4 shadow-sm sm:grid-cols-[1fr_1fr_auto]">
        <select name="status" class="rounded-xl px-4 py-3 text-sm">
            <option value="">All statuses</option>
            @foreach(['pending', 'paid', 'failed', 'refunded'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <select name="payment_type" class="rounded-xl px-4 py-3 text-sm">
            <option value="">All payment types</option>
            @foreach(['tuition', 'exam_fee', 'library_fee', 'sports_fee', 'other'] as $type)
                <option value="{{ $type }}" @selected(request('payment_type') === $type)>{{ str($type)->replace('_', ' ')->title() }}</option>
            @endforeach
        </select>
        <button class="rounded-xl border border-[#06322C] px-5 py-3 text-sm font-bold text-[#06322C]">Filter</button>
    </form>

    <section class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-400">
                    <tr><th class="p-4">Reference</th><th>Student</th><th>Type</th><th>Amount</th><th>Status</th><th>Date</th><th></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-slate-50/70">
                            <td class="p-4"><span class="font-bold text-[#06322C]">{{ $payment->invoice_number }}</span><small class="mt-1 block text-slate-400">{{ $payment->provider_reference ?: ($payment->transaction_id ?: 'No gateway reference') }}</small></td>
                            <td><strong class="text-slate-800">{{ $payment->student?->full_name ?? 'Student unavailable' }}</strong><small class="block text-slate-400">{{ $payment->student?->admission_number }}</small></td>
                            <td>{{ str($payment->payment_type)->replace('_', ' ')->title() }}</td>
                            <td class="font-bold">₦{{ number_format($payment->amount, 2) }}</td>
                            <td><span class="rounded-full px-3 py-1 text-xs font-bold {{ $payment->status === 'paid' ? 'bg-emerald-50 text-emerald-700' : ($payment->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">{{ ucfirst($payment->status) }}</span></td>
                            <td class="text-slate-500">{{ $payment->created_at->format('M j, Y') }}</td>
                            <td class="pr-4 text-right"><a href="{{ route('payments.show', $payment) }}" class="font-bold text-[#3A7B72]">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-12 text-center text-slate-400">No payment transactions match the selected filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    {{ $payments->withQueryString()->links() }}
</div>
@endsection
