<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolEvent extends Model
{
    protected $fillable = ['school_id', 'academic_term_id', 'created_by', 'title', 'description', 'audience', 'starts_at', 'ends_at', 'location'];
    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
}
