<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CbtQuestion extends Model
{
    protected $fillable = ['school_id', 'subject_id', 'author_id', 'type', 'prompt', 'options', 'correct_answers', 'explanation', 'default_points', 'difficulty', 'is_active'];

    protected $casts = ['options' => 'array', 'correct_answers' => 'encrypted:array', 'default_points' => 'decimal:2', 'is_active' => 'boolean'];

    protected $hidden = ['correct_answers'];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
