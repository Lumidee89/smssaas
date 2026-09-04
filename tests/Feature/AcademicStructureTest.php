<?php

namespace Tests\Feature;

use App\Models\Campus;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_builds_tenant_scoped_campus_faculty_department_and_course(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);

        $this->actingAs($admin)->post(route('academic-structure.campuses.store'), [
            'name' => 'City Campus', 'code' => 'CITY', 'is_main' => 1,
        ])->assertRedirect();
        $campus = Campus::where('school_id', $school->id)->firstOrFail();

        $this->actingAs($admin)->post(route('academic-structure.faculties.store'), [
            'name' => 'Faculty of Science', 'code' => 'SCI', 'campus_id' => $campus->id,
        ])->assertRedirect();
        $faculty = Faculty::where('school_id', $school->id)->firstOrFail();

        $this->actingAs($admin)->post(route('academic-structure.departments.store'), [
            'name' => 'Computer Science', 'code' => 'CSC', 'campus_id' => $campus->id, 'faculty_id' => $faculty->id,
        ])->assertRedirect();
        $department = Department::where('school_id', $school->id)->firstOrFail();

        $this->actingAs($admin)->post(route('academic-structure.courses.store'), [
            'name' => 'Introduction to Computing', 'code' => 'CSC101', 'department_id' => $department->id, 'credit_units' => 3,
        ])->assertRedirect();

        $this->assertDatabaseHas('courses', ['school_id' => $school->id, 'code' => 'CSC101']);
        $this->actingAs($admin)->get(route('academic-structure.index', ['section' => 'courses']))
            ->assertOk()->assertSee('Introduction to Computing');
    }

    public function test_cross_tenant_structure_relationships_are_rejected(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $foreignCampus = Campus::create(['school_id' => School::factory()->create()->id, 'name' => 'Foreign', 'code' => 'FOREIGN']);

        $this->actingAs($admin)->post(route('academic-structure.faculties.store'), [
            'name' => 'Escaped Faculty', 'code' => 'ESC', 'campus_id' => $foreignCampus->id,
        ])->assertSessionHasErrors('campus_id');

        $this->assertDatabaseMissing('faculties', ['school_id' => $school->id, 'code' => 'ESC']);
    }

    public function test_teacher_cannot_manage_institution_structure(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->create(['school_id' => $school->id, 'role' => 'teacher']);

        $this->actingAs($teacher)->get(route('academic-structure.index'))->assertForbidden();
    }
}
