<?php

namespace App\Actions\Finance;

use App\Models\FeeInvoice;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CreateFeeInvoice
{
    public function execute(Student $student, User $actor, array $items, array $attributes = []): FeeInvoice
    {
        if ($student->school_id !== $actor->school_id) {
            throw new InvalidArgumentException('Student and actor must belong to the same school.');
        }if (empty($items)) {
            throw new InvalidArgumentException('An invoice requires at least one item.');
        }

        return DB::transaction(function () use ($student, $actor, $items, $attributes) {
            $normalized = collect($items)->map(function ($item) {
                $quantity = (int) ($item['quantity'] ?? 1);
                $unit = round((float) $item['unit_amount'], 2);
                if ($quantity < 1 || $unit < 0) {
                    throw new InvalidArgumentException('Invoice item values are invalid.');
                }

return ['description' => $item['description'], 'quantity' => $quantity, 'unit_amount' => $unit, 'total_amount' => round($quantity * $unit, 2)];
            });
            $subtotal = round($normalized->sum('total_amount'), 2);
            $discount = min(round((float) ($attributes['discount_total'] ?? 0), 2), $subtotal);
            $invoice = FeeInvoice::create(['school_id' => $student->school_id, 'student_id' => $student->id, 'academic_term_id' => $attributes['academic_term_id'] ?? null, 'invoice_number' => $attributes['invoice_number'] ?? 'FIN-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)), 'currency' => $student->school->currency, 'subtotal' => $subtotal, 'discount_total' => $discount, 'amount_due' => $subtotal - $discount, 'amount_paid' => 0, 'status' => $attributes['status'] ?? 'issued', 'due_on' => $attributes['due_on'] ?? null, 'issued_at' => now(), 'created_by' => $actor->id]);
            $invoice->items()->createMany($normalized->all());

            return $invoice->load('items');
        });
    }
}
