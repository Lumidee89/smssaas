<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\FeeInvoice;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_issues_invoice_with_balanced_installments_and_manages_operations(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $teacher = User::factory()->create(['school_id' => $school->id, 'role' => 'teacher']);
        $student = Student::factory()->create(['school_id' => $school->id]);
        $this->actingAs($admin)->get(route('finance.index'))->assertOk();
        $this->post(route('finance.invoices.store'), ['student_id' => $student->id, 'due_on' => now()->addMonth()->toDateString(), 'discount_total' => 1000, 'items' => [['description' => 'Tuition', 'quantity' => 1, 'unit_amount' => 11000]], 'installments' => [['amount' => 5000, 'due_on' => now()->addWeek()->toDateString()], ['amount' => 5000, 'due_on' => now()->addMonth()->toDateString()]]])->assertRedirect();
        $invoice = FeeInvoice::first();
        $this->assertSame('10000.00', $invoice->amount_due);
        $this->assertCount(2, $invoice->installments);
        $this->post(route('finance.expenses.store'), ['category' => 'Utilities', 'description' => 'Electricity', 'amount' => 2500, 'expense_date' => now()->toDateString()])->assertSessionHasNoErrors();
        $expense = Expense::first();
        $this->post(route('finance.expenses.approve', $expense))->assertSessionHasNoErrors();
        $this->assertSame('approved', $expense->fresh()->status);
        $this->post(route('finance.payroll.store'), ['period_start' => now()->startOfMonth()->toDateString(), 'period_end' => now()->endOfMonth()->toDateString(), 'staff' => [$teacher->id => ['gross' => 100000, 'deductions' => 5000]]])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('payroll_items', ['user_id' => $teacher->id, 'net_amount' => 95000]);
    }

    public function test_unbalanced_installments_roll_back_entire_invoice(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'school_admin']);
        $student = Student::factory()->create(['school_id' => $school->id]);
        $this->actingAs($admin)->post(route('finance.invoices.store'), ['student_id' => $student->id, 'items' => [['description' => 'Tuition', 'quantity' => 1, 'unit_amount' => 10000]], 'installments' => [['amount' => 100, 'due_on' => now()->addDay()->toDateString()]]])->assertStatus(422);
        $this->assertDatabaseCount('fee_invoices', 0);
    }
}
