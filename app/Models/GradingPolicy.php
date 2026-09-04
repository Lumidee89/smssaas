<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradingPolicy extends Model
{
    protected $fillable = ['school_id', 'name', 'version', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function bands()
    {
        return $this->hasMany(GradingBand::class)->orderByDesc('minimum_percentage');
    }
}
