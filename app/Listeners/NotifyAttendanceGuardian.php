<?php

namespace App\Listeners;

use App\Events\AttendanceMarked;
use App\Notifications\SchoolAlert;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAttendanceGuardian implements ShouldQueue
{
    public string $queue = 'notifications';

    public function handle(AttendanceMarked $event): void
    {
        $record = $event->attendance->loadMissing('student');
        $parents = $record->student->guardians()->get();
        foreach ($parents as $parent) {
            $parent->notify(new SchoolAlert([
                'kind' => 'attendance', 'title' => 'Attendance update',
                'body' => $record->student->full_name.' was marked '.$record->status.'.',
                'student_id' => $record->student_id, 'attendance_id' => $record->id,
            ]));
        }
    }
}
