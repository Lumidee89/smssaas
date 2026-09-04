<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeInvoice extends Model
{
    protected $fillable = ['school_id', 'student_id', 'academic_term_id', 'invoice_number', 'currency', 'subtotal', 'discount_total', 'amount_due', 'amount_paid', 'status', 'due_on', 'issued_at', 'created_by'];

    protected $casts = ['subtotal' => 'decimal:2', 'discount_total' => 'decimal:2', 'amount_due' => 'decimal:2', 'amount_paid' => 'decimal:2', 'due_on' => 'date', 'issued_at' => 'datetime'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function items()
    {
        return $this->hasMany(FeeInvoiceItem::class);
    }

    public function installments()
    {
        return $this->hasMany(InstallmentSchedule::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
