<?php

// app/Models/Classes.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'school_id', 'campus_id', 'name', 'section', 'capacity', 'teacher_id',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function students()
    {
        // Fix: The foreign key is 'class_id' not 'classes_id'
        return $this->hasMany(Student::class, 'class_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'class_id', 'subject_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function getFullNameAttribute()
    {
        return $this->name.($this->section ? ' - '.$this->section : '');
    }

    public function getStudentCountAttribute()
    {
        return $this->students()->count();
    }
}
