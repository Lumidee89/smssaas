<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpChallenge extends Model
{
    protected $fillable = ['phone', 'purpose', 'code_hash', 'attempts', 'expires_at', 'consumed_at'];

    protected $hidden = ['code_hash'];

    protected $casts = ['expires_at' => 'datetime', 'consumed_at' => 'datetime'];
}
