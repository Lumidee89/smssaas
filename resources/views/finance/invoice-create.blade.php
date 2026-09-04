@extends('layouts.app')

@section('title', 'Issue invoice')
@section('page_title', 'Issue fee invoice')

@section('content')
@php
    $invoiceItems = collect(old('items', [
        ['description' => '', 'quantity' => 1, 'unit_amount' => ''],
    ]))->values()->map(function ($item, $index) {
        return [
            'key' => $index,
            'description' => $item['description'] ?? '',
            'quantity' => (int) ($item['quantity'] ?? 1),
            'unitAmount' => $item['unit_amount'] ?? '',
        ];
    })->all();
@endphp

<div class="mx-auto max-w-7xl" x-data="invoiceForm()">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('finance.index', ['section' => 'invoices']) }}" class="mb-3 inline-flex items-center gap-2 text-sm font-semibold text-[#3A7B72] hover:text-[#06322C]">
                <span aria-hidden="true">←</span> Back to invoices
            </a>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Create fee invoice</h1>
            <p class="mt-1 text-sm text-slate-500">Add a student, due date and one or more billable items.</p>
        </div>
        <div class="inline-flex w-fit items-center gap-2 rounded-full bg-[#e7f5f1] px-4 py-2 text-xs font-bold text-[#06322C]">
            <span class="h-2 w-2 rounded-full bg-[#3A7B72]"></span>
            Draft invoice
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
            <p class="font-bold">Please correct the following:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('finance.invoices.store') }}" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        @csrf

        <div class="min-w-0 space-y-6">
            <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7">
                <div class="mb-6 flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#e7f5f1] text-[#06322C]">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                    </div>
                    <div>
                        <h2 class="font-extrabold text-slate-900">Invoice details</h2>
                        <p class="text-sm text-slate-500">Choose who this invoice belongs to and when it is due.</p>
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="student_id" class="mb-2 block text-sm font-bold text-slate-700">Student <span class="text-red-500">*</span></label>
                        <select id="student_id" name="student_id" required class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm text-slate-800">
                            <option value="" disabled @selected(!old('student_id'))>Select a student</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}" @selected((string) old('student_id') === (string) $student->id)>
                                    {{ $student->full_name }} · {{ $student->admission_number }}
                                </option>
                            @endforeach
                        </select>
                        @if ($students->isEmpty())
                            <p class="mt-2 text-sm text-amber-700">No students are available. Add a student before issuing an invoice.</p>
                        @endif
                    </div>

                    <div>
                        <label for="due_on" class="mb-2 block text-sm font-bold text-slate-700">Due date</label>
                        <input id="due_on" type="date" name="due_on" min="{{ now()->toDateString() }}" value="{{ old('due_on') }}" class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm">
                    </div>
                    <div>
                        <label for="discount_total" class="mb-2 block text-sm font-bold text-slate-700">Discount (₦)</label>
                        <input id="discount_total" x-model.number="discount" type="number" step="0.01" min="0" name="discount_total" value="{{ old('discount_total', 0) }}" class="h-12 w-full rounded-xl border border-slate-300 px-4 text-sm" placeholder="0.00">
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7">
                <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-extrabold text-slate-900">Invoice items</h2>
                        <p class="text-sm text-slate-500">Enter the fees included in this invoice.</p>
                    </div>
                    <button type="button" @click="addLine()" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[#3A7B72] px-4 text-sm font-bold text-[#06322C] transition hover:bg-[#e7f5f1]">
                        <span class="text-lg leading-none">+</span> Add item
                    </button>
                </div>

                <div class="hidden grid-cols-[minmax(180px,1fr)_90px_150px_110px_38px] gap-3 px-1 pb-2 text-xs font-bold uppercase tracking-wide text-slate-400 md:grid">
                    <span>Description</span><span>Qty</span><span>Unit amount</span><span class="text-right">Total</span><span></span>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="item.key">
                        <div class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50/60 p-4 md:grid-cols-[minmax(180px,1fr)_90px_150px_110px_38px] md:items-center md:border-0 md:bg-transparent md:p-0">
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-500 md:hidden">Description</label>
                                <input x-model="item.description" :name="`items[${index}][description]`" required maxlength="255" placeholder="e.g. Tuition fee" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-500 md:hidden">Quantity</label>
                                <input x-model.number="item.quantity" :name="`items[${index}][quantity]`" type="number" min="1" max="1000" required class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-500 md:hidden">Unit amount (₦)</label>
                                <input x-model.number="item.unitAmount" :name="`items[${index}][unit_amount]`" type="number" step="0.01" min="0" required placeholder="0.00" class="h-11 w-full rounded-xl border border-slate-300 px-3 text-sm">
                            </div>
                            <div class="flex items-center justify-between md:block md:text-right">
                                <span class="text-xs font-bold text-slate-500 md:hidden">Line total</span>
                                <span class="font-extrabold text-slate-800" x-text="money(lineTotal(item))"></span>
                            </div>
                            <button type="button" @click="removeLine(index)" :disabled="items.length === 1" class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 transition hover:bg-red-50 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-30" aria-label="Remove invoice item">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
            </section>
        </div>

        <aside class="xl:sticky xl:top-6 xl:self-start">
            <div class="overflow-hidden rounded-3xl bg-[#06322C] text-white shadow-xl shadow-[#06322C]/10">
                <div class="border-b border-white/10 p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7ED3C4]">Invoice summary</p>
                    <h2 class="mt-2 text-xl font-extrabold">Amount due</h2>
                </div>
                <dl class="space-y-4 p-6 text-sm">
                    <div class="flex items-center justify-between text-white/70"><dt>Subtotal</dt><dd class="font-bold text-white" x-text="money(subtotal)"></dd></div>
                    <div class="flex items-center justify-between text-white/70"><dt>Discount</dt><dd class="font-bold text-[#7ED3C4]" x-text="`− ${money(validDiscount)}`"></dd></div>
                    <div class="h-px bg-white/10"></div>
                    <div class="flex items-end justify-between gap-3"><dt class="font-bold">Total</dt><dd class="text-2xl font-black" x-text="money(total)"></dd></div>
                </dl>
                <div class="bg-white/5 p-5">
                    <button type="submit" @disabled($students->isEmpty()) class="flex w-full items-center justify-center gap-2 rounded-2xl bg-[#7ED3C4] px-5 py-4 font-extrabold text-[#06322C] transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-50">
                        Issue invoice
                        <span aria-hidden="true">→</span>
                    </button>
                    <p class="mt-3 text-center text-xs leading-5 text-white/55">Totals are verified by the server before the invoice is created.</p>
                </div>
            </div>
        </aside>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function invoiceForm() {
        return {
            discount: Number(@json((float) old('discount_total', 0))),
            nextKey: 1,
            items: @json($invoiceItems),
            init() {
                this.nextKey = this.items.length;
            },
            addLine() {
                this.items.push({ key: this.nextKey++, description: '', quantity: 1, unitAmount: '' });
            },
            removeLine(index) {
                if (this.items.length > 1) this.items.splice(index, 1);
            },
            lineTotal(item) {
                return Math.max(0, Number(item.quantity) || 0) * Math.max(0, Number(item.unitAmount) || 0);
            },
            get subtotal() {
                return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0);
            },
            get validDiscount() {
                return Math.min(Math.max(0, Number(this.discount) || 0), this.subtotal);
            },
            get total() {
                return Math.max(0, this.subtotal - this.validDiscount);
            },
            money(value) {
                return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(value || 0);
            },
        };
    }
</script>
@endpush
