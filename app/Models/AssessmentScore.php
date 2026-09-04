<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentScore extends Model
{
    protected $fillable = ['school_id', 'assessment_id', 'student_id', 'score', 'teacher_comment', 'entered_by'];

    protected $casts = ['score' => 'decimal:2'];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
