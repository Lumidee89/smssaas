<?php

namespace Tests\Feature;

use App\Models\Classes;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\DemoAcademicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoAcademicSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_academic_seed_is_complete_and_idempotent(): void
    {
        $this->seed(DemoAcademicSeeder::class);
        $this->seed(DemoAcademicSeeder::class);

        $school = School::where('subdomain', 'demo-academy')->firstOrFail();
        $this->assertSame(2, Classes::where('school_id', $school->id)->count());
        $this->assertSame(2, Subject::where('school_id', $school->id)->count());
        $this->assertSame(2, User::where('school_id', $school->id)->where('role', 'teacher')->count());
        $this->assertSame(2, Student::where('school_id', $school->id)->count());
        $this->assertSame(2, Classes::where('school_id', $school->id)->whereNotNull('teacher_id')->count());
    }
}
