<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommunicationAndAcademicSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_admin_reads_and_replies_to_parent_conversation(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $parent = User::factory()->create(['school_id' => $school->id, 'role' => 'parent']);
        Student::factory()->create(['school_id' => $school->id, 'parent_id' => $parent->id]);
        $token = $parent->createToken('phone')->plainTextToken;

        $created = $this->withToken($token)->postJson('/api/v1/parent/conversations', [
            'subject' => 'School transport',
            'body' => 'Please confirm the pickup time.',
        ])->assertCreated();
        $conversationId = $created->json('data.id');

        $this->actingAs($admin)->get(route('messages.index'))->assertOk()->assertSee('School transport');
        $this->actingAs($admin)->get(route('messages.show', $conversationId))->assertOk()->assertSee('Please confirm the pickup time.');
        $this->actingAs($admin)->post(route('messages.reply', $conversationId), ['body' => 'Pickup is at 3 PM.'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('messages', ['conversation_id' => $conversationId, 'sender_id' => $admin->id, 'body' => 'Pickup is at 3 PM.']);
    }

    public function test_school_admin_creates_academic_year_and_terms_for_assessments(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);

        $this->actingAs($admin)->post(route('academic-setup.years.store'), [
            'name' => '2026/2027', 'starts_on' => '2026-09-01', 'ends_on' => '2027-07-31', 'is_current' => 1,
        ])->assertSessionHasNoErrors();
        $year = AcademicYear::where('school_id', $school->id)->firstOrFail();

        $this->actingAs($admin)->post(route('academic-setup.terms.store'), [
            'academic_year_id' => $year->id, 'name' => 'First Term', 'sequence' => 1,
            'starts_on' => '2026-09-01', 'ends_on' => '2026-12-18', 'is_current' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('academic_terms', ['school_id' => $school->id, 'academic_year_id' => $year->id, 'name' => 'First Term', 'is_current' => true]);
        $this->actingAs($admin)->get(route('academic-setup.index'))->assertOk()->assertSee('2026/2027')->assertSee('First Term');
        $this->actingAs($admin)->get(route('assessments.create'))->assertOk()->assertSee('2026/2027');
        $this->actingAs($admin)->get(route('cbt.create'))->assertOk()->assertSee('2026/2027');
    }
}
