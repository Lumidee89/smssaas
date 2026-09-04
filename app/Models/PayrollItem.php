<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    protected $fillable = ['payroll_run_id', 'user_id', 'gross_amount', 'deduction_amount', 'net_amount', 'breakdown', 'status'];

    protected $casts = ['gross_amount' => 'decimal:2', 'deduction_amount' => 'decimal:2', 'net_amount' => 'decimal:2', 'breakdown' => 'array'];
}
