<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SchoolRegistrationPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_is_locked_until_selected_plan_payment_is_verified(): void
    {
        config(['payment.paystack.secret_key' => 'sk_test_example', 'payment.paystack.base_url' => 'https://api.paystack.co']);
        Http::fake(function (ClientRequest $request) {
            if (str_contains($request->url(), '/transaction/initialize')) {
                return Http::response(['status' => true, 'data' => ['authorization_url' => 'https://checkout.paystack.test/session', 'reference' => $request['reference']]], 200);
            }

            $payment = SubscriptionPayment::firstOrFail();

            return Http::response(['status' => true, 'data' => ['reference' => $payment->reference, 'status' => 'success', 'amount' => (int) round((float) $payment->amount * 100)]], 200);
        });
        $plan = SubscriptionPlan::where('code', 'growth')->firstOrFail();

        $response = $this->post(route('register'), [
            'school_name' => 'Paid Academy', 'subdomain' => 'paid-academy', 'admin_name' => 'School Admin',
            'email' => 'admin@paid.test', 'phone' => '08012345678', 'address' => 'Lagos',
            'password' => 'secure-password', 'password_confirmation' => 'secure-password', 'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
        ]);

        $response->assertRedirect('https://checkout.paystack.test/session');
        $school = School::where('subdomain', 'paid-academy')->firstOrFail();
        $payment = SubscriptionPayment::where('school_id', $school->id)->firstOrFail();
        $this->assertFalse($school->is_active);
        $this->assertSame('pending', $payment->status);

        $this->post(route('login'), ['email' => 'admin@paid.test', 'password' => 'secure-password'])->assertSessionHasErrors('email');
        $this->get(route('registration.payment.callback', ['reference' => $payment->reference]))->assertRedirect(route('dashboard'));
        $this->assertTrue($school->fresh()->is_active);
        $this->assertSame('active', $payment->subscription->fresh()->status);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertAuthenticated();
    }

    public function test_yearly_registration_charges_twenty_percent_less_and_activates_a_year(): void
    {
        config(['payment.paystack.secret_key' => 'sk_test_example', 'payment.paystack.base_url' => 'https://api.paystack.co']);
        Http::fake(function (ClientRequest $request) {
            if (str_contains($request->url(), '/transaction/initialize')) {
                return Http::response(['status' => true, 'data' => ['authorization_url' => 'https://checkout.paystack.test/annual', 'reference' => $request['reference']]], 200);
            }

            $payment = SubscriptionPayment::firstOrFail();

            return Http::response(['status' => true, 'data' => ['reference' => $payment->reference, 'status' => 'success', 'amount' => (int) round((float) $payment->amount * 100)]], 200);
        });
        $plan = SubscriptionPlan::where('code', 'growth')->firstOrFail();

        $this->post(route('register'), [
            'school_name' => 'Annual Academy', 'subdomain' => 'annual-academy', 'admin_name' => 'Annual Admin',
            'email' => 'admin@annual.test', 'phone' => '08012345678', 'address' => 'Abuja',
            'password' => 'secure-password', 'password_confirmation' => 'secure-password',
            'plan_id' => $plan->id, 'billing_cycle' => 'yearly',
        ])->assertRedirect('https://checkout.paystack.test/annual');

        $payment = SubscriptionPayment::where('school_id', School::where('subdomain', 'annual-academy')->value('id'))->firstOrFail();
        $this->assertSame(576000.0, (float) $payment->amount);
        $this->assertSame('yearly', $payment->metadata['billing_cycle']);
        $this->assertSame(20, $payment->metadata['discount_percentage']);

        $this->get(route('registration.payment.callback', ['reference' => $payment->reference]))->assertRedirect(route('dashboard'));
        $subscription = $payment->subscription->fresh();
        $this->assertTrue($subscription->current_period_ends_at->isAfter(now()->addMonths(11)));
        $this->assertTrue($subscription->current_period_ends_at->isBefore(now()->addYear()->addMinute()));
    }

    public function test_registration_requires_a_payable_active_plan(): void
    {
        $this->post(route('register'), [
            'school_name' => 'No Plan Academy', 'subdomain' => 'no-plan', 'admin_name' => 'Admin',
            'email' => 'admin@noplans.test', 'phone' => '08012345678', 'address' => 'Lagos',
            'password' => 'secure-password', 'password_confirmation' => 'secure-password',
            'billing_cycle' => 'monthly',
        ])->assertSessionHasErrors('plan_id');
    }

    public function test_registration_rejects_an_invalid_billing_cycle(): void
    {
        $plan = SubscriptionPlan::where('code', 'starter')->firstOrFail();

        $this->post(route('register'), [
            'school_name' => 'Invalid Cycle School', 'subdomain' => 'invalid-cycle', 'admin_name' => 'Admin',
            'email' => 'admin@invalid-cycle.test', 'phone' => '08012345678', 'address' => 'Lagos',
            'password' => 'secure-password', 'password_confirmation' => 'secure-password',
            'plan_id' => $plan->id, 'billing_cycle' => 'lifetime',
        ])->assertSessionHasErrors('billing_cycle');
    }
}
