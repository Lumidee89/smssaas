<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAttendanceRecord extends Model
{
    protected $fillable = ['school_id', 'user_id', 'attendance_date', 'status', 'scheduled_at', 'checked_in_at', 'recorded_by', 'note'];

    protected $casts = ['attendance_date' => 'date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
