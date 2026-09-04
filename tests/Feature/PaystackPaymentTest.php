<?php

namespace Tests\Feature;

use App\Actions\Finance\CreateFeeInvoice;
use App\Models\Payment;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaystackPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_initializes_checkout_and_signed_webhook_settles_invoice_once(): void
    {
        config(['payment.paystack.secret_key' => 'test-secret', 'payment.paystack.base_url' => 'https://api.paystack.co']);
        Http::fake(['api.paystack.co/*' => Http::response(['status' => true, 'data' => ['authorization_url' => 'https://checkout.test/abc', 'access_code' => 'abc', 'reference' => 'provider-ref']], 200)]);
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $parent = User::factory()->create(['school_id' => $school->id, 'role' => 'parent']);
        $student = Student::factory()->create(['school_id' => $school->id, 'parent_id' => $parent->id]);
        $parent->children()->attach($student, ['school_id' => $school->id, 'relationship' => 'mother']);
        $invoice = app(CreateFeeInvoice::class)->execute($student, $admin, [['description' => 'Tuition', 'unit_amount' => 25000]]);
        $token = $parent->createToken('phone')->plainTextToken;
        $response = $this->withToken($token)->postJson("/api/v1/parent/fee-invoices/{$invoice->id}/initialize-payment", ['idempotency_key' => 'mobile-request-0001'])->assertOk()->assertJsonPath('data.authorization_url', 'https://checkout.test/abc');
        $payment = $invoice->payments()->first();
        $payload = ['event' => 'charge.success', 'data' => ['reference' => $payment->provider_reference, 'status' => 'success', 'amount' => 2500000, 'id' => 12345, 'channel' => 'card']];
        $body = json_encode($payload);
        $signature = hash_hmac('sha512', $body, 'test-secret');
        $this->call('POST', '/api/v1/webhooks/paystack', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_PAYSTACK_SIGNATURE' => $signature], $body)->assertOk();
        $this->call('POST', '/api/v1/webhooks/paystack', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_X_PAYSTACK_SIGNATURE' => $signature], $body)->assertOk();
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame('25000.00', $invoice->fresh()->amount_paid);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        config(['payment.paystack.secret_key' => 'test-secret']);
        $this->postJson('/api/v1/webhooks/paystack', ['event' => 'charge.success'], ['X-Paystack-Signature' => 'invalid'])->assertUnauthorized();
    }

    public function test_parent_can_verify_successful_payment_when_webhook_is_delayed(): void
    {
        config(['payment.paystack.secret_key' => 'test-secret', 'payment.paystack.base_url' => 'https://api.paystack.co']);
        Http::fake(function ($request) {
            if (str_contains($request->url(), '/transaction/verify/')) {
                if (str_contains($request->url(), 'PSK-ABANDONED-REFERENCE')) {
                    return Http::response(['status' => true, 'data' => ['reference' => 'PSK-ABANDONED-REFERENCE', 'status' => 'abandoned', 'amount' => 1250000, 'id' => 9877, 'gateway_response' => 'The transaction was not completed']]);
                }

                return Http::response(['status' => true, 'data' => ['reference' => 'PSK-VERIFY-REFERENCE-0001', 'status' => 'success', 'amount' => 1250000, 'id' => 9876, 'channel' => 'card']]);
            }

            return Http::response(['status' => true, 'data' => ['authorization_url' => 'https://checkout.test/verify', 'reference' => 'PSK-VERIFY-REFERENCE-0001']]);
        });
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $parent = User::factory()->create(['school_id' => $school->id, 'role' => 'parent']);
        $student = Student::factory()->create(['school_id' => $school->id, 'parent_id' => $parent->id]);
        $parent->children()->attach($student, ['school_id' => $school->id, 'relationship' => 'father']);
        $invoice = app(CreateFeeInvoice::class)->execute($student, $admin, [['description' => 'Tuition', 'unit_amount' => 12500]]);
        $token = $parent->createToken('phone')->plainTextToken;

        $this->withToken($token)->postJson("/api/v1/parent/fee-invoices/{$invoice->id}/initialize-payment", ['idempotency_key' => 'verify-request-00000001'])->assertOk();
        $payment = $invoice->payments()->firstOrFail();
        $payment->update(['provider_reference' => 'PSK-VERIFY-REFERENCE-0001']);
        $abandoned = Payment::create([
            'school_id' => $school->id, 'student_id' => $student->id, 'fee_invoice_id' => $invoice->id,
            'parent_id' => $parent->id, 'invoice_number' => 'PAY-ABANDONED-REFERENCE', 'amount' => 12500,
            'payment_type' => 'tuition', 'term' => 'General', 'status' => 'pending', 'provider' => 'paystack',
            'provider_reference' => 'PSK-ABANDONED-REFERENCE',
        ]);

        $this->withToken($token)->postJson("/api/v1/parent/fee-invoices/{$invoice->id}/verify-payment")
            ->assertOk()
            ->assertJsonPath('data.payment_status', 'paid')
            ->assertJsonPath('data.balance', 0);

        $this->assertSame('paid', $invoice->fresh()->status);
        $this->assertSame('failed', $abandoned->fresh()->status);
    }
}
