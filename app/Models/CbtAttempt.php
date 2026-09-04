<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CbtAttempt extends Model
{
    protected $fillable = ['school_id', 'cbt_exam_id', 'student_id', 'attempt_number', 'token_hash', 'question_order', 'started_at', 'expires_at', 'submitted_at', 'status', 'score', 'maximum_score', 'percentage'];

    protected $casts = ['question_order' => 'array', 'started_at' => 'datetime', 'expires_at' => 'datetime', 'submitted_at' => 'datetime', 'score' => 'decimal:2', 'maximum_score' => 'decimal:2', 'percentage' => 'decimal:2'];

    protected $hidden = ['token_hash'];

    public function exam()
    {
        return $this->belongsTo(CbtExam::class, 'cbt_exam_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function answers()
    {
        return $this->hasMany(CbtAnswer::class);
    }
}
