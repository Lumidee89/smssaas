<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Tenancy\TenantSchemaManager;
use App\Tenancy\DomainVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_subdomain_resolves_tenant_and_rejects_cross_tenant_user(): void
    {
        config(['tenancy.base_domain' => 'schoolos.test']);
        $school = School::factory()->create(['subdomain' => 'greenfield']);
        app(TenantSchemaManager::class)->provision($school);
        $own = User::factory()->create(['school_id' => $school->id, 'role' => 'principal']);
        $foreign = User::factory()->create(['school_id' => School::factory()->create()->id, 'role' => 'principal']);

        $this->actingAs($own)->get('http://greenfield.schoolos.test/dashboard')->assertOk();
        $this->actingAs($foreign)->get('http://greenfield.schoolos.test/dashboard')->assertRedirect(route('login'));
        $this->assertNull($school->fresh()->tenant_schema);
        $this->assertNotNull($school->fresh()->provisioned_at);
    }

    public function test_verified_custom_domain_resolves_tenant(): void
    {
        config(['tenancy.base_domain' => 'schoolos.test']);
        $school = School::factory()->create(['custom_domain' => 'portal.greenfield.edu', 'domain_verified_at' => now()]);
        $principal = User::factory()->create(['school_id' => $school->id, 'role' => 'principal']);

        $this->actingAs($principal)->get('https://portal.greenfield.edu/dashboard')->assertOk();
    }

    public function test_platform_admin_configures_and_verifies_custom_domain(): void
    {
        $platform = User::factory()->create(['school_id' => null, 'role' => 'super_admin']);
        $school = School::factory()->create();
        $this->actingAs($platform)->put(route('platform.schools.domain', $school), ['custom_domain' => 'portal.example.edu'])->assertRedirect();
        $this->assertNotNull($school->fresh()->domain_verification_token);
        $this->app->instance(DomainVerifier::class, new class extends DomainVerifier {
            public function verify(string $domain, string $token): bool { return true; }
        });
        $this->actingAs($platform)->post(route('platform.schools.domain.verify', $school))->assertRedirect();
        $this->assertNotNull($school->fresh()->domain_verified_at);
    }
}
