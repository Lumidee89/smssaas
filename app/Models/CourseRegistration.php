<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseRegistration extends Model
{
    protected $fillable = ['school_id', 'student_id', 'course_id', 'academic_term_id', 'status', 'registered_by', 'registered_at'];

    protected $casts = ['registered_at' => 'datetime'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function registrar()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
