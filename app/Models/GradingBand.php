<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradingBand extends Model
{
    protected $fillable = ['grading_policy_id', 'minimum_percentage', 'maximum_percentage', 'letter_grade', 'grade_point', 'remark'];

    protected $casts = ['minimum_percentage' => 'decimal:2', 'maximum_percentage' => 'decimal:2', 'grade_point' => 'decimal:2'];

    public function policy()
    {
        return $this->belongsTo(GradingPolicy::class, 'grading_policy_id');
    }
}
