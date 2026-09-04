<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentIntervention extends Model
{
    protected $fillable = ['school_id', 'student_id', 'assigned_to', 'created_by', 'title', 'action_plan', 'due_on', 'status', 'completed_at'];

    protected $casts = ['due_on' => 'date', 'completed_at' => 'datetime'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
