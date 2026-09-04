<?php

// app/Models/Student.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';

    protected $fillable = [
        'school_id',
        'campus_id',
        'faculty_id',
        'department_id',
        'class_id',
        'parent_id',
        'admission_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'blood_group',
        'emergency_contact',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function courseRegistrations()
    {
        return $this->hasMany(CourseRegistration::class);
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function userAccount()
    {
        return $this->hasOne(User::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id')
            ->withPivot(['relationship', 'is_primary', 'can_pick_up', 'receives_billing'])->withTimestamps();
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function cbtAttempts()
    {
        return $this->hasMany(CbtAttempt::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getAverageScoreAttribute()
    {
        return $this->results()->avg('score');
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth->age;
    }
}
