<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicTerm extends Model
{
    protected $fillable = ['school_id', 'academic_year_id', 'name', 'sequence', 'starts_on', 'ends_on', 'is_current'];

    protected $casts = ['starts_on' => 'date', 'ends_on' => 'date', 'is_current' => 'boolean'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
