<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDevelopmentSignal extends Model
{
    protected $fillable = ['school_id', 'student_id', 'recorded_by', 'category', 'score', 'note', 'occurred_on'];

    protected $casts = ['occurred_on' => 'date', 'score' => 'integer'];
}
