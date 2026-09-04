<?php

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\Classes;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_academic_guardian_and_attendance_relations_are_tenant_linked(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $parent = User::factory()->create(['school_id' => $school->id, 'role' => 'parent']);
        $class = Classes::create(['school_id' => $school->id, 'name' => 'JSS 1', 'section' => 'A', 'capacity' => 30]);
        $student = Student::factory()->create(['school_id' => $school->id, 'class_id' => $class->id, 'parent_id' => $parent->id]);
        $year = AcademicYear::create(['school_id' => $school->id, 'name' => '2026/2027', 'starts_on' => '2026-09-01', 'ends_on' => '2027-07-31', 'is_current' => true]);
        $term = AcademicTerm::create(['school_id' => $school->id, 'academic_year_id' => $year->id, 'name' => 'First Term', 'sequence' => 1, 'starts_on' => '2026-09-01', 'ends_on' => '2026-12-18', 'is_current' => true]);
        $parent->children()->attach($student, ['school_id' => $school->id, 'relationship' => 'mother', 'is_primary' => true]);
        AttendanceRecord::create(['school_id' => $school->id, 'student_id' => $student->id, 'class_id' => $class->id, 'academic_term_id' => $term->id, 'attendance_date' => '2026-09-02', 'status' => 'present', 'recorded_by' => $admin->id]);
        Announcement::create(['school_id' => $school->id, 'author_id' => $admin->id, 'title' => 'Welcome', 'body' => 'Term begins today.', 'audience' => 'parents', 'published_at' => now()]);

        $this->assertTrue($parent->children->contains($student));
        $this->assertTrue($student->guardians->contains($parent));
        $this->assertSame('present', $student->attendanceRecords->first()->status);
        $this->assertCount(1, $school->academicYears);
        $this->assertCount(1, $school->announcements()->published()->get());
        $this->assertDatabaseHas('audit_logs', ['event' => 'AttendanceRecord.created', 'school_id' => $school->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'Announcement.created', 'school_id' => $school->id]);
    }

    public function test_cross_tenant_relationship_ids_are_rejected(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $schoolA->id, 'role' => 'school_admin']);
        $foreignClass = Classes::create(['school_id' => $schoolB->id, 'name' => 'Foreign', 'capacity' => 30]);
        $foreignParent = User::factory()->create(['school_id' => $schoolB->id, 'role' => 'parent']);

        $this->actingAs($admin)->post(route('students.store'), [
            'first_name' => 'Tenant', 'last_name' => 'Escape', 'email' => 'escape@example.com', 'date_of_birth' => '2012-01-01',
            'gender' => 'male', 'class_id' => $foreignClass->id, 'parent_id' => $foreignParent->id,
        ])->assertSessionHasErrors(['class_id', 'parent_id']);

        $this->assertDatabaseMissing('students', ['email' => 'escape@example.com']);
    }
}
