<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    protected $fillable = ['school_id', 'name', 'code', 'email', 'phone', 'address', 'is_main', 'is_active'];

    protected $casts = ['is_main' => 'boolean', 'is_active' => 'boolean'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function faculties()
    {
        return $this->hasMany(Faculty::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}
