<?php

namespace App\Support;

final class SchoolRole
{
    public const PLATFORM_ADMIN = 'super_admin';
    public const SCHOOL_ADMIN = 'school_admin';
    public const PRINCIPAL = 'principal';
    public const VICE_PRINCIPAL = 'vice_principal';
    public const ACADEMIC_ADMIN = 'academic_admin';
    public const TEACHER = 'teacher';
    public const BURSAR = 'bursar';
    public const PARENT = 'parent';
    public const STUDENT = 'student';

    public const ALL = [
        self::PLATFORM_ADMIN, self::SCHOOL_ADMIN, self::PRINCIPAL,
        self::VICE_PRINCIPAL, self::ACADEMIC_ADMIN, self::TEACHER,
        self::BURSAR, self::PARENT, self::STUDENT,
    ];

    public const SCHOOL_LEADERS = [self::SCHOOL_ADMIN, self::PRINCIPAL, self::VICE_PRINCIPAL];
    public const ACADEMIC_MANAGERS = [self::SCHOOL_ADMIN, self::PRINCIPAL, self::VICE_PRINCIPAL, self::ACADEMIC_ADMIN];
    public const ACADEMIC_STAFF = [self::SCHOOL_ADMIN, self::PRINCIPAL, self::VICE_PRINCIPAL, self::ACADEMIC_ADMIN, self::TEACHER];
    public const FINANCE_STAFF = [self::SCHOOL_ADMIN, self::PRINCIPAL, self::BURSAR];
    public const MESSAGE_CONTACTS = [self::SCHOOL_ADMIN, self::PRINCIPAL, self::VICE_PRINCIPAL, self::ACADEMIC_ADMIN, self::TEACHER, self::BURSAR];

    public static function label(string $role): string
    {
        return match ($role) {
            self::PLATFORM_ADMIN => 'Platform Admin',
            self::SCHOOL_ADMIN => 'School Super Admin',
            self::PRINCIPAL => 'Principal / VC',
            self::VICE_PRINCIPAL => 'Vice Principal',
            self::ACADEMIC_ADMIN => 'Academic Admin',
            self::BURSAR => 'Bursar / Finance Officer',
            self::TEACHER => 'Teacher / Lecturer',
            self::PARENT => 'Parent / Guardian',
            self::STUDENT => 'Student',
            default => str($role)->replace('_', ' ')->title()->toString(),
        };
    }
}
