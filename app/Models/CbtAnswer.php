<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CbtAnswer extends Model
{
    protected $fillable = ['cbt_attempt_id', 'cbt_question_id', 'answer', 'awarded_points', 'is_correct', 'requires_review', 'review_comment'];

    protected $casts = ['answer' => 'array', 'awarded_points' => 'decimal:2', 'is_correct' => 'boolean', 'requires_review' => 'boolean'];

    public function question()
    {
        return $this->belongsTo(CbtQuestion::class, 'cbt_question_id');
    }
}
