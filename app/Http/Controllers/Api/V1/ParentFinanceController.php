<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Finance\HandlePaystackWebhook;
use App\Contracts\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\FeeInvoice;
use App\Models\Payment;
use App\Services\Payments\PaystackGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentFinanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $parent = $request->user();
        $studentIds = $parent->children()->pluck('students.id');
        if ($studentIds->isEmpty()) {
            $studentIds = $parent->students()->pluck('id');
        }$payments = Payment::where('school_id', $parent->school_id)->whereIn('student_id', $studentIds)->with('student:id,first_name,last_name')->latest()->paginate(20);

        return response()->json(['data' => collect($payments->items())->map(fn ($p) => ['id' => $p->id, 'invoice_number' => $p->invoice_number, 'student' => ['id' => $p->student->id, 'name' => $p->student->full_name], 'amount' => (float) $p->amount, 'type' => $p->payment_type, 'term' => $p->term, 'status' => $p->status, 'paid_at' => $p->paid_at?->toISOString()]), 'meta' => ['current_page' => $payments->currentPage(), 'last_page' => $payments->lastPage(), 'total' => $payments->total()]]);
    }

    public function show(Request $request, Payment $payment): JsonResponse
    {
        $parent = $request->user();
        $studentIds = $parent->children()->pluck('students.id');
        if ($studentIds->isEmpty()) {
            $studentIds = $parent->students()->pluck('id');
        }abort_unless($payment->school_id === $parent->school_id && $studentIds->contains($payment->student_id), 403);
        $payment->load('student:id,first_name,last_name,admission_number');

        return response()->json(['data' => ['id' => $payment->id, 'invoice_number' => $payment->invoice_number, 'student' => ['id' => $payment->student->id, 'name' => $payment->student->full_name, 'admission_number' => $payment->student->admission_number], 'amount' => (float) $payment->amount, 'description' => $payment->description, 'type' => $payment->payment_type, 'term' => $payment->term, 'status' => $payment->status, 'payment_method' => $payment->payment_method, 'transaction_id' => $payment->transaction_id, 'paid_at' => $payment->paid_at?->toISOString()]]);
    }

    public function invoices(Request $request, PaystackGateway $gateway, HandlePaystackWebhook $handler): JsonResponse
    {
        $ids = $this->studentIds($request);
        $this->reconcilePendingPayments($request, $ids, $gateway, $handler);
        $items = FeeInvoice::where('school_id', $request->user()->school_id)->whereIn('student_id', $ids)->whereNot('status', 'draft')->with(['student:id,first_name,last_name', 'items', 'installments' => fn ($query) => $query->orderBy('sequence')])->latest()->paginate(20);

        return response()->json(['data' => collect($items->items())->map(fn ($i) => ['id' => $i->id, 'invoice_number' => $i->invoice_number, 'student' => ['id' => $i->student->id, 'name' => $i->student->full_name], 'currency' => $i->currency, 'amount_due' => (float) $i->amount_due, 'amount_paid' => (float) $i->amount_paid, 'balance' => max(0, (float) $i->amount_due - (float) $i->amount_paid), 'status' => $i->status, 'due_on' => $i->due_on?->toDateString(), 'items' => $i->items->map(fn ($line) => ['description' => $line->description, 'quantity' => $line->quantity, 'amount' => (float) $line->total_amount]), 'installments' => $i->installments->map(fn ($part) => ['id' => $part->id, 'sequence' => $part->sequence, 'amount' => (float) $part->amount, 'due_on' => $part->due_on->toDateString(), 'status' => $part->status])]), 'meta' => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'total' => $items->total()]]);
    }

    public function initialize(Request $request, FeeInvoice $invoice, PaymentGateway $gateway): JsonResponse
    {
        $data = $request->validate(['idempotency_key' => ['required', 'string', 'min:16', 'max:100'], 'installment_id' => ['nullable', 'integer']]);
        $parent = $request->user();
        abort_unless($invoice->school_id === $parent->school_id && $this->studentIds($request)->contains($invoice->student_id), 403);
        $balance = round((float) $invoice->amount_due - (float) $invoice->amount_paid, 2);
        abort_if($balance <= 0, 422, 'Invoice has no outstanding balance.');
        $installment = null;
        if (! empty($data['installment_id'])) {
            $installment = $invoice->installments()->whereKey($data['installment_id'])->where('status', 'pending')->firstOrFail();
            $earlierUnpaid = $invoice->installments()->where('sequence', '<', $installment->sequence)->where('status', 'pending')->exists();
            abort_if($earlierUnpaid, 422, 'Earlier installments must be paid first.');
        }
        $chargeAmount = $installment ? min((float) $installment->amount, $balance) : $balance;
        $reference = 'PSK-'.strtoupper(substr(hash('sha256', $invoice->id.'|'.$parent->id.'|'.$data['idempotency_key']), 0, 24));
        $payment = Payment::firstOrCreate(['provider' => 'paystack', 'provider_reference' => $reference], ['school_id' => $parent->school_id, 'student_id' => $invoice->student_id, 'fee_invoice_id' => $invoice->id, 'installment_schedule_id' => $installment?->id, 'parent_id' => $parent->id, 'invoice_number' => 'PAY-'.$reference, 'amount' => $chargeAmount, 'payment_type' => 'tuition', 'term' => $invoice->academicTerm?->name ?? 'General', 'status' => 'pending', 'description' => $installment ? 'Installment '.$installment->sequence.' for '.$invoice->invoice_number : 'Payment for '.$invoice->invoice_number]);
        if ($payment->provider_payload) {
            return response()->json(['data' => $payment->provider_payload]);
        }$checkout = $gateway->initialize($parent->email, $chargeAmount, $invoice->currency, $reference, ['invoice_id' => $invoice->id, 'installment_id' => $installment?->id, 'school_id' => $parent->school_id, 'student_id' => $invoice->student_id]);
        $payment->update(['provider_payload' => $checkout]);

        return response()->json(['data' => $checkout]);
    }

    public function verify(Request $request, FeeInvoice $invoice, PaystackGateway $gateway, HandlePaystackWebhook $handler): JsonResponse
    {
        $parent = $request->user();
        abort_unless($invoice->school_id === $parent->school_id && $this->studentIds($request)->contains($invoice->student_id), 403);

        $payment = $invoice->payments()
            ->where('parent_id', $parent->id)
            ->where('provider', 'paystack')
            ->latest()
            ->first();

        abort_unless($payment, 404, 'No payment attempt was found for this invoice.');

        $this->reconcilePendingPayments($request, collect([$invoice->student_id]), $gateway, $handler, $invoice->id);

        $invoice->refresh();
        $balance = max(0, (float) $invoice->amount_due - (float) $invoice->amount_paid);

        return response()->json(['data' => [
            'status' => $invoice->status,
            'amount_paid' => (float) $invoice->amount_paid,
            'balance' => $balance,
            'payment_status' => $balance <= 0 ? 'paid' : 'pending',
        ]]);
    }

    private function reconcilePendingPayments(Request $request, $studentIds, PaystackGateway $gateway, HandlePaystackWebhook $handler, ?int $invoiceId = null): void
    {
        $query = Payment::where('school_id', $request->user()->school_id)
            ->where('parent_id', $request->user()->id)
            ->whereIn('student_id', $studentIds)
            ->where('provider', 'paystack')
            ->where('status', 'pending')
            ->whereNotNull('provider_reference');

        if ($invoiceId !== null) {
            $query->where('fee_invoice_id', $invoiceId);
        }

        foreach ($query->latest()->limit(10)->get() as $pending) {
            try {
                $verified = $gateway->verify($pending->provider_reference);
                if (($verified['status'] ?? null) === 'success') {
                    $handler->execute(['event' => 'charge.success', 'data' => $verified]);
                } elseif (in_array($verified['status'] ?? null, ['abandoned', 'failed', 'reversed'], true)) {
                    $pending->update(['status' => 'failed', 'provider_payload' => $verified]);
                }
            } catch (\Throwable $error) {
                report($error);
            }
        }
    }

    private function studentIds(Request $request)
    {
        $parent = $request->user();
        $ids = $parent->children()->pluck('students.id');

        return $ids->isEmpty() ? $parent->students()->pluck('id') : $ids;
    }
}
