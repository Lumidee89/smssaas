<?php

// app/Models/Result.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'subject_id', 'teacher_id', 'exam_type',
        'term', 'academic_year', 'score', 'max_score', 'remarks',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function getPercentageAttribute()
    {
        return ($this->score / $this->max_score) * 100;
    }

    public function getGradeAttribute()
    {
        $percentage = $this->percentage;

        if ($percentage >= 90) {
            return 'A+';
        }
        if ($percentage >= 80) {
            return 'A';
        }
        if ($percentage >= 70) {
            return 'B';
        }
        if ($percentage >= 60) {
            return 'C';
        }
        if ($percentage >= 50) {
            return 'D';
        }

        return 'F';
    }

    public function getRemarkAttribute()
    {
        $percentage = $this->percentage;

        if ($percentage >= 90) {
            return 'Excellent';
        }
        if ($percentage >= 80) {
            return 'Very Good';
        }
        if ($percentage >= 70) {
            return 'Good';
        }
        if ($percentage >= 60) {
            return 'Satisfactory';
        }
        if ($percentage >= 50) {
            return 'Pass';
        }

        return 'Needs Improvement';
    }
}
