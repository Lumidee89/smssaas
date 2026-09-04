<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSuccessScore extends Model
{
    protected $fillable = ['school_id', 'student_id', 'calculated_on', 'score', 'academic_score', 'attendance_score', 'assignment_score', 'behaviour_score', 'leadership_score', 'risk_level', 'trend', 'risk_factors', 'recommendations', 'data_quality'];

    protected $casts = ['calculated_on' => 'date', 'score' => 'decimal:2', 'academic_score' => 'decimal:2', 'attendance_score' => 'decimal:2', 'assignment_score' => 'decimal:2', 'behaviour_score' => 'decimal:2', 'leadership_score' => 'decimal:2', 'trend' => 'decimal:2', 'risk_factors' => 'array', 'recommendations' => 'array', 'data_quality' => 'array'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
