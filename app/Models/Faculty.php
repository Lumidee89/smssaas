<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $fillable = ['school_id', 'campus_id', 'name', 'code', 'dean_id'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function dean()
    {
        return $this->belongsTo(User::class, 'dean_id');
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}
