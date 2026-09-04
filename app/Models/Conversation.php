<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['school_id', 'subject', 'created_by', 'last_message_at'];

    protected $casts = ['last_message_at' => 'datetime'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class)->withPivot('last_read_at')->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
