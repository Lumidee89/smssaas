<?php

namespace App\Services\Notifications;

use App\Models\Assessment;
use App\Models\Student;
use App\Models\User;
use App\Notifications\SchoolAlert;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class ParentNotificationService
{
    public function schoolAudience(int $schoolId, string $audience, array $payload): void
    {
        $roles = match ($audience) {
            'parents' => ['parent'],
            'teachers' => ['teacher'],
            'students' => ['student'],
            default => ['parent', 'teacher', 'student', 'school_admin'],
        };

        $this->send(User::where('school_id', $schoolId)->whereIn('role', $roles)->get(), $payload);
    }

    public function assessmentApproved(Assessment $assessment): void
    {
        $studentIds = Student::where('school_id', $assessment->school_id)
            ->where('class_id', $assessment->class_id)->pluck('id');
        $parents = User::where('school_id', $assessment->school_id)
            ->where('role', 'parent')->whereHas('children', fn ($query) => $query->whereIn('students.id', $studentIds))->get();

        $this->send($parents, [
            'kind' => 'result',
            'title' => 'New result published',
            'body' => $assessment->title.' results are now available.',
            'assessment_id' => $assessment->id,
        ]);
    }

    public function paymentReceived(User $parent, string $reference, string $amount): void
    {
        $this->send(collect([$parent]), [
            'kind' => 'payment',
            'title' => 'Payment received',
            'body' => 'Your payment of '.$amount.' has been confirmed.',
            'payment_reference' => $reference,
        ], true);
    }

    private function send(Collection $recipients, array $payload, bool $email = false): void
    {
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new SchoolAlert($payload, $email));
        }
    }
}
