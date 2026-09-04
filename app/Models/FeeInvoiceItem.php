<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeInvoiceItem extends Model
{
    protected $fillable = ['fee_invoice_id', 'description', 'quantity', 'unit_amount', 'total_amount'];

    protected $casts = ['unit_amount' => 'decimal:2', 'total_amount' => 'decimal:2'];

    public function invoice()
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }
}
