<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_admin_can_view_payment_details_and_receipt(): void
    {
        [$admin, $payment] = $this->paymentFixture();

        $this->actingAs($admin)
            ->get(route('payments.show', $payment))
            ->assertOk()
            ->assertSee($payment->invoice_number)
            ->assertSee('Ada Student')
            ->assertSee('Payment details');

        $this->actingAs($admin)
            ->get(route('payments.receipt', $payment))
            ->assertOk()
            ->assertSee('Official payment receipt')
            ->assertSee($payment->provider_reference);
    }

    public function test_payment_index_and_create_form_are_not_blank(): void
    {
        [$admin, $payment] = $this->paymentFixture();

        $this->actingAs($admin)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('Payment ledger')
            ->assertSee($payment->invoice_number);

        $this->actingAs($admin)
            ->get(route('payments.create'))
            ->assertOk()
            ->assertSee('Create a payment record')
            ->assertSee('Ada Student');
    }

    public function test_admin_cannot_view_another_schools_payment(): void
    {
        [$admin] = $this->paymentFixture();
        [, $foreignPayment] = $this->paymentFixture();

        $this->actingAs($admin)
            ->get(route('payments.show', $foreignPayment))
            ->assertForbidden();
    }

    public function test_parent_only_sees_and_opens_their_own_payments(): void
    {
        [$admin, $ownPayment] = $this->paymentFixture();
        $parent = $ownPayment->parent;
        $otherParent = User::factory()->create([
            'school_id' => $admin->school_id,
            'role' => 'parent',
        ]);
        $otherPayment = $this->pendingPayment($admin, $otherParent);

        $this->actingAs($parent)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee($ownPayment->invoice_number)
            ->assertDontSee($otherPayment->invoice_number);

        $this->actingAs($parent)
            ->get(route('payments.show', $otherPayment))
            ->assertForbidden();
    }

    public function test_parent_and_teacher_cannot_create_or_manually_settle_payments(): void
    {
        [$admin, $paidPayment] = $this->paymentFixture();
        $parent = $paidPayment->parent;
        $teacher = User::factory()->create([
            'school_id' => $admin->school_id,
            'role' => 'teacher',
        ]);
        $pending = $this->pendingPayment($admin, $parent);

        foreach ([$parent, $teacher] as $user) {
            $this->actingAs($user)->get(route('payments.create'))->assertForbidden();
            $this->actingAs($user)->post(route('payments.process', $pending), [
                'payment_method' => 'cash',
            ])->assertForbidden();
        }

        $this->assertSame('pending', $pending->fresh()->status);
    }

    public function test_online_payment_cannot_be_manually_settled_but_offline_payment_can(): void
    {
        [$admin, $onlinePayment] = $this->paymentFixture();
        $onlinePayment->update(['status' => 'pending', 'paid_at' => null]);

        $this->actingAs($admin)->post(route('payments.process', $onlinePayment), [
            'payment_method' => 'cash',
        ])->assertUnprocessable();
        $this->assertSame('pending', $onlinePayment->fresh()->status);

        $offlinePayment = $this->pendingPayment($admin, $onlinePayment->parent);
        $this->actingAs($admin)->post(route('payments.process', $offlinePayment), [
            'payment_method' => 'bank_transfer',
            'transaction_id' => 'BANK-VERIFIED-001',
        ])->assertRedirect(route('payments.show', $offlinePayment));

        $offlinePayment->refresh();
        $this->assertSame('paid', $offlinePayment->status);
        $this->assertSame('bank_transfer', $offlinePayment->payment_method);
    }

    private function pendingPayment(User $admin, User $parent): Payment
    {
        $student = Student::factory()->create([
            'school_id' => $admin->school_id,
            'parent_id' => $parent->id,
        ]);

        return Payment::create([
            'school_id' => $admin->school_id,
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'invoice_number' => 'PAY-'.fake()->unique()->numerify('########'),
            'amount' => 2500,
            'payment_type' => 'other',
            'term' => 'First Term',
            'status' => 'pending',
        ]);
    }

    private function paymentFixture(): array
    {
        $school = School::factory()->create();
        $admin = User::factory()->create([
            'school_id' => $school->id,
            'role' => 'school_admin',
        ]);
        $parent = User::factory()->create([
            'school_id' => $school->id,
            'role' => 'parent',
        ]);
        $student = Student::factory()->create([
            'school_id' => $school->id,
            'parent_id' => $parent->id,
            'first_name' => 'Ada',
            'last_name' => 'Student',
        ]);
        $payment = Payment::create([
            'school_id' => $school->id,
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'invoice_number' => 'PAY-'.fake()->unique()->numerify('########'),
            'amount' => 4000,
            'payment_type' => 'tuition',
            'term' => 'First Term',
            'status' => 'paid',
            'payment_method' => 'card',
            'provider' => 'paystack',
            'provider_reference' => 'PSK-'.fake()->unique()->numerify('########'),
            'transaction_id' => fake()->unique()->numerify('##########'),
            'paid_at' => now(),
            'description' => 'School fee payment',
        ]);

        return [$admin, $payment];
    }
}
