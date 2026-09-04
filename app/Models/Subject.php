<?php

// app/Models/Subject.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id', 'name', 'code', 'credit_hours', 'description',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_subject', 'subject_id', 'teacher_id');
    }

    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'class_subject', 'subject_id', 'class_id');
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function getTeacherCountAttribute()
    {
        return $this->teachers()->count();
    }

    public function getClassCountAttribute()
    {
        return $this->classes()->count();
    }
}
