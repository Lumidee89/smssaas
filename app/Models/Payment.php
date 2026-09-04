<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id', 'student_id', 'fee_invoice_id', 'installment_schedule_id', 'parent_id', 'invoice_number',
        'amount', 'payment_type', 'term', 'status', 'payment_method',
        'transaction_id', 'provider', 'provider_reference', 'provider_payload', 'paid_at', 'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'provider_payload' => 'array',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feeInvoice()
    {
        return $this->belongsTo(FeeInvoice::class);
    }

    public function installmentSchedule()
    {
        return $this->belongsTo(InstallmentSchedule::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function markAsPaid($transactionId = null, $paymentMethod = null)
    {
        $this->status = 'paid';
        $this->transaction_id = $transactionId;
        $this->payment_method = $paymentMethod;
        $this->paid_at = now();
        $this->save();
    }
}
