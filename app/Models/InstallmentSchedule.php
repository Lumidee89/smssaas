<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallmentSchedule extends Model
{
    protected $fillable = ['school_id', 'fee_invoice_id', 'sequence', 'amount', 'due_on', 'status'];

    protected $casts = ['amount' => 'decimal:2', 'due_on' => 'date'];

    public function invoice()
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
