<?php

namespace Tests\Feature;

use App\Actions\Finance\CreateFeeInvoice;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class FinanceDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_totals_are_calculated_server_side_and_audited(): void
    {
        $school = School::factory()->create(['currency' => 'NGN']);
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $student = Student::factory()->create(['school_id' => $school->id]);
        $invoice = $this->actingAs($admin)->app->make(CreateFeeInvoice::class)->execute($student, $admin, [['description' => 'Tuition', 'quantity' => 1, 'unit_amount' => 50000], ['description' => 'Books', 'quantity' => 2, 'unit_amount' => 2500]], ['discount_total' => 5000]);
        $this->assertSame('55000.00', $invoice->subtotal);
        $this->assertSame('50000.00', $invoice->amount_due);
        $this->assertCount(2, $invoice->items);
        $this->assertDatabaseHas('audit_logs', ['event' => 'FeeInvoice.created', 'school_id' => $school->id]);
    }

    public function test_invoice_creation_rejects_cross_tenant_students(): void
    {
        $student = Student::factory()->create(['school_id' => School::factory()->create()->id]);
        $admin = User::factory()->create(['school_id' => School::factory()->create()->id, 'role' => 'school_admin']);
        $this->expectException(InvalidArgumentException::class);
        app(CreateFeeInvoice::class)->execute($student, $admin, [['description' => 'Tuition', 'unit_amount' => 100]]);
    }
}
