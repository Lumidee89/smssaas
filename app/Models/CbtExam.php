<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CbtExam extends Model
{
    protected $fillable = ['school_id', 'academic_term_id', 'class_id', 'subject_id', 'created_by', 'title', 'instructions', 'duration_minutes', 'opens_at', 'closes_at', 'status', 'shuffle_questions', 'shuffle_options', 'max_attempts', 'pass_percentage', 'access_code_hash'];

    protected $casts = ['opens_at' => 'datetime', 'closes_at' => 'datetime', 'shuffle_questions' => 'boolean', 'shuffle_options' => 'boolean', 'pass_percentage' => 'decimal:2'];

    protected $hidden = ['access_code_hash'];

    public function questions()
    {
        return $this->belongsToMany(CbtQuestion::class, 'cbt_exam_question')->withPivot(['points', 'position'])->orderByPivot('position');
    }

    public function attempts()
    {
        return $this->hasMany(CbtAttempt::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function term()
    {
        return $this->belongsTo(AcademicTerm::class, 'academic_term_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
