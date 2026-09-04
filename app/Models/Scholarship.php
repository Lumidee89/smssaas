<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    protected $fillable = ['school_id', 'name', 'discount_type', 'discount_value', 'starts_on', 'ends_on', 'is_active'];

    protected $casts = ['discount_value' => 'decimal:2', 'starts_on' => 'date', 'ends_on' => 'date', 'is_active' => 'boolean'];

    public function students()
    {
        return $this->belongsToMany(Student::class)->withPivot('approved_by')->withTimestamps();
    }
}
