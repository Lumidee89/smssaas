<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['school_id', 'department_id', 'name', 'code', 'credit_units', 'level', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function registrations()
    {
        return $this->hasMany(CourseRegistration::class);
    }
}
