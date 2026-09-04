<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['school_id', 'reference', 'category', 'description', 'amount', 'currency', 'expense_date', 'status', 'submitted_by', 'approved_by', 'approved_at'];

    protected $casts = ['amount' => 'decimal:2', 'expense_date' => 'date', 'approved_at' => 'datetime'];
}
