<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolSubscription;
use App\Models\Student;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_provisions_and_manages_tenant_subscription(): void
    {
        $super = User::factory()->create(['role' => 'super_admin', 'school_id' => null]);
        $plan = SubscriptionPlan::where('code', 'growth')->firstOrFail();
        $this->actingAs($super)->post(route('platform.schools.store'), ['name' => 'Future Academy', 'subdomain' => 'future-academy', 'email' => 'school@example.test', 'phone' => '08010000000', 'address' => 'Lagos', 'institution_type' => 'secondary', 'plan_id' => $plan->id, 'admin_name' => 'School Owner', 'admin_email' => 'owner@example.test', 'temporary_password' => 'temporary-pass-123'])->assertSessionHasNoErrors();
        $school = School::where('subdomain', 'future-academy')->firstOrFail();
        $this->assertDatabaseHas('users', ['school_id' => $school->id, 'email' => 'owner@example.test', 'role' => 'school_admin']);
        $this->assertDatabaseHas('school_subscriptions', ['school_id' => $school->id, 'subscription_plan_id' => $plan->id, 'status' => 'trialing']);
        $this->actingAs($super)->put(route('platform.schools.subscription', $school), ['plan_id' => $plan->id, 'status' => 'suspended'])->assertSessionHasNoErrors();
        $this->assertFalse($school->fresh()->is_active);
        $this->assertSame('suspended', SchoolSubscription::where('school_id', $school->id)->latest('id')->value('status'));
    }

    public function test_school_admin_cannot_access_platform_operations(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $this->actingAs($admin)->get(route('platform.index'))->assertForbidden();
    }

    public function test_platform_tabs_render_distinct_content(): void
    {
        $super = User::factory()->create(['role' => 'super_admin', 'school_id' => null]);

        $this->actingAs($super)->get(route('platform.index'))->assertOk()->assertSee('Network overview')->assertDontSee('New institution');
        $this->actingAs($super)->get(route('platform.schools.index'))->assertOk()->assertSee('Provision school')->assertDontSee('Recent accounts');
        $this->actingAs($super)->get(route('platform.users.index'))->assertOk()->assertSee('Users and students')->assertDontSee('Payment ledger');
        $this->actingAs($super)->get(route('platform.payments.index'))->assertOk()->assertSee('Payment ledger')->assertDontSee('Provision school');
    }

    public function test_super_admin_creates_users_and_students_for_selected_school(): void
    {
        $super = User::factory()->create(['role' => 'super_admin', 'school_id' => null]);
        $school = School::factory()->create();
        $this->actingAs($super)->post(route('platform.users.store'), ['school_id' => $school->id, 'role' => 'teacher', 'name' => 'Platform Teacher', 'email' => 'teacher@school.test', 'phone' => '08012345678', 'password' => 'secure-password'])->assertSessionHasNoErrors();
        $this->actingAs($super)->post(route('platform.students.store'), ['school_id' => $school->id, 'first_name' => 'New', 'last_name' => 'Student', 'email' => 'student@school.test', 'date_of_birth' => '2014-05-10', 'gender' => 'female'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['school_id' => $school->id, 'role' => 'teacher', 'email' => 'teacher@school.test']);
        $this->assertSame($school->id, Student::where('email', 'student@school.test')->value('school_id'));
    }

    public function test_normalized_plan_entitlements_are_enforced(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $plan = SubscriptionPlan::where('code', 'starter')->firstOrFail();
        SchoolSubscription::create(['school_id' => $school->id, 'subscription_plan_id' => $plan->id, 'status' => 'active', 'starts_at' => now(), 'current_period_ends_at' => now()->addMonth()]);
        $this->actingAs($admin)->get(route('cbt.index'))->assertStatus(402);
        $this->actingAs($admin)->get(route('analytics.index'))->assertStatus(402);
        $this->actingAs($admin)->get(route('students.index'))->assertOk();
    }
}
