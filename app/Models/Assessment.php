<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = ['school_id', 'academic_term_id', 'class_id', 'subject_id', 'teacher_id', 'title', 'type', 'maximum_score', 'weight_percentage', 'assessment_date', 'status', 'approved_by', 'approved_at'];

    protected $casts = ['maximum_score' => 'decimal:2', 'weight_percentage' => 'decimal:2', 'assessment_date' => 'date', 'approved_at' => 'datetime'];

    public function scores()
    {
        return $this->hasMany(AssessmentScore::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function term()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }
}
