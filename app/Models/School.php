<?php

// app/Models/School.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'institution_type',
        'subdomain',
        'custom_domain', 'tenant_schema', 'domain_verified_at', 'domain_verification_token', 'provisioned_at',
        'email',
        'phone',
        'address',
        'logo',
        'theme_color', 'currency', 'timezone',
        'is_active',
        'subscription_end_date', 'subscription_plan', 'monthly_price', 'yearly_price', 'max_students', 'max_teachers', 'has_api_access', 'has_advanced_analytics', 'has_priority_support',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscription_end_date' => 'datetime',
        'domain_verified_at' => 'datetime',
        'provisioned_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function classes()
    {
        return $this->hasMany(Classes::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function teachers()
    {
        return $this->users()->where('role', 'teacher');
    }

    public function parents()
    {
        return $this->users()->where('role', 'parent');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function academicTerms()
    {
        return $this->hasMany(AcademicTerm::class);
    }

    public function campuses()
    {
        return $this->hasMany(Campus::class);
    }

    public function faculties()
    {
        return $this->hasMany(Faculty::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(SchoolSubscription::class);
    }

    public function isSubscriptionActive()
    {
        return $this->is_active && ($this->subscription_end_date === null || $this->subscription_end_date->isFuture());
    }
}
