<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transcript extends Model
{
    protected $fillable = ['public_id', 'school_id', 'student_id', 'grading_policy_id', 'cgpa', 'snapshot', 'status', 'issued_by', 'issued_at', 'revoked_at', 'revocation_reason'];

    protected $casts = ['cgpa' => 'decimal:2', 'snapshot' => 'array', 'issued_at' => 'datetime', 'revoked_at' => 'datetime'];

    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function policy()
    {
        return $this->belongsTo(GradingPolicy::class, 'grading_policy_id');
    }
}
