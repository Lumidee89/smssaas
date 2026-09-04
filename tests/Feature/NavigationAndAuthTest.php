<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationAndAuthTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $school = School::create([
            'name' => 'Test Academy', 'subdomain' => 'test-academy', 'email' => 'school@example.com',
            'phone' => '123456789', 'address' => 'Test address', 'is_active' => true,
            'subscription_end_date' => now()->addMonth(),
        ]);

        return User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
    }

    public function test_guest_can_open_redesigned_auth_screens(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Sign in to your workspace');
        $this->get(route('register'))->assertOk()->assertSee('Tell us about your school');
    }

    public function test_admin_can_open_dashboard_and_each_sidebar_destination(): void
    {
        $admin = $this->admin();
        foreach (['dashboard', 'students.index', 'teachers.index', 'classes.index', 'subjects.index', 'results.index', 'payments.index', 'reports.index', 'settings.index'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
    }

    public function test_logout_invalidates_authenticated_session(): void
    {
        $this->actingAs($this->admin())->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_seeded_super_admin_uses_the_new_dashboard_not_the_legacy_placeholder(): void
    {
        $admin = User::factory()->create(['school_id' => null, 'role' => 'super_admin']);

        foreach (['dashboard', 'students.index', 'teachers.index', 'classes.index', 'subjects.index', 'results.index', 'payments.index', 'reports.index', 'settings.index'] as $route) {
            $response = $this->actingAs($admin)->get(route($route))->assertOk();
            if ($route === 'dashboard') {
                $response->assertSee('Here is what is happening across your school today.')->assertDontSee('1,234');
            }
        }
    }
}
