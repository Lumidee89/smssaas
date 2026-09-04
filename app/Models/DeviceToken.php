<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $fillable = ['school_id', 'user_id', 'token', 'platform', 'last_seen_at'];

    protected $casts = ['last_seen_at' => 'datetime'];
}
