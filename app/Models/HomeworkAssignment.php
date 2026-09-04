<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeworkAssignment extends Model
{
    protected $fillable = ['school_id', 'class_id', 'subject_id', 'teacher_id', 'academic_term_id', 'title', 'instructions', 'due_at', 'published_at'];
    protected $casts = ['due_at' => 'datetime', 'published_at' => 'datetime'];
    public function subject() { return $this->belongsTo(Subject::class); }
    public function class() { return $this->belongsTo(Classes::class); }
}
