<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['school_id', 'faculty_id', 'campus_id', 'name', 'code', 'head_id'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function head()
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
