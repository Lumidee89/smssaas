<?php

namespace App\Events;

use App\Models\AttendanceRecord;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceMarked
{
    use Dispatchable, SerializesModels;
    public function __construct(public AttendanceRecord $attendance) {}
}
