<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    protected $fillable = ['school_id', 'reference', 'period_start', 'period_end', 'status', 'gross_total', 'deduction_total', 'net_total', 'created_by', 'processed_at'];

    protected $casts = ['period_start' => 'date', 'period_end' => 'date', 'gross_total' => 'decimal:2', 'deduction_total' => 'decimal:2', 'net_total' => 'decimal:2', 'processed_at' => 'datetime'];

    public function items()
    {
        return $this->hasMany(PayrollItem::class);
    }
}
