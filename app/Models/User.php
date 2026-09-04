<?php

// app/Models/User.php

namespace App\Models;

use App\Support\SchoolRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Add 'phone' to fillable array
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'school_id',
        'student_id',
        'campus_id',
        'phone',  // Add this line
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id')
            ->withPivot(['relationship', 'is_primary', 'can_pick_up', 'receives_billing'])->withTimestamps();
    }

    public function taughtSubjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject', 'teacher_id', 'subject_id');
    }

    public function results()
    {
        return $this->hasMany(Result::class, 'teacher_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'parent_id');
    }

    public function isAdmin()
    {
        return in_array($this->role, SchoolRole::SCHOOL_LEADERS, true);
    }

    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    public function isParent()
    {
        return $this->role === 'parent';
    }

    public function hasAnyRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isAcademicManager(): bool
    {
        return in_array($this->role, SchoolRole::ACADEMIC_MANAGERS, true);
    }

    public function isFinanceStaff(): bool
    {
        return in_array($this->role, SchoolRole::FINANCE_STAFF, true);
    }

    public function setPhoneAttribute(?string $value): void
    {
        $normalized = $value === null ? null : preg_replace('/[^0-9+]/', '', $value);
        $this->attributes['phone'] = $normalized && str_starts_with($normalized, '00') ? '+'.substr($normalized, 2) : $normalized;
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class)->withPivot('last_read_at')->withTimestamps();
    }
}
