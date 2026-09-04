<?php

namespace App\Http\Controllers;

use App\Actions\Finance\CreateFeeInvoice;
use App\Models\Expense;
use App\Models\FeeInvoice;
use App\Models\PayrollRun;
use App\Models\Scholarship;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FinanceAdminController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $section = in_array($request->section, ['invoices', 'expenses', 'scholarships', 'payroll'], true) ? $request->section : 'invoices';
        $invoices = FeeInvoice::where('school_id', $schoolId)->with('student')->latest()->paginate(15, ['*'], 'invoice_page');
        $expenses = Expense::where('school_id', $schoolId)->latest('expense_date')->paginate(15, ['*'], 'expense_page');
        $scholarships = Scholarship::where('school_id', $schoolId)->withCount('students')->latest()->get();
        $payrollRuns = PayrollRun::where('school_id', $schoolId)->withCount('items')->latest('period_start')->get();
        $students = Student::where('school_id', $schoolId)->orderBy('first_name')->get();
        $staff = User::where('school_id', $schoolId)->whereIn('role', ['teacher', 'school_admin'])->orderBy('name')->get();
        $stats = ['invoiced' => FeeInvoice::where('school_id', $schoolId)->sum('amount_due'), 'collected' => FeeInvoice::where('school_id', $schoolId)->sum('amount_paid'), 'expenses' => Expense::where('school_id', $schoolId)->where('status', 'approved')->sum('amount'), 'outstanding' => FeeInvoice::where('school_id', $schoolId)->whereIn('status', ['issued', 'partial', 'overdue'])->selectRaw('coalesce(sum(amount_due-amount_paid),0) total')->value('total')];

        return view('finance.index', compact('section', 'invoices', 'expenses', 'scholarships', 'payrollRuns', 'students', 'staff', 'stats'));
    }

    public function createInvoice()
    {
        return view('finance.invoice-create', ['students' => Student::where('school_id', Auth::user()->school_id)->orderBy('first_name')->get()]);
    }

    public function storeInvoice(Request $request, CreateFeeInvoice $creator)
    {
        $schoolId = Auth::user()->school_id;
        $data = $request->validate(['student_id' => ['required', Rule::exists('students', 'id')->where('school_id', $schoolId)], 'due_on' => ['nullable', 'date', 'after_or_equal:today'], 'discount_total' => ['nullable', 'numeric', 'min:0'], 'items' => ['required', 'array', 'min:1'], 'items.*.description' => ['required', 'string', 'max:255'], 'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'], 'items.*.unit_amount' => ['required', 'numeric', 'min:0'], 'installments' => ['nullable', 'array'], 'installments.*.amount' => ['required_with:installments', 'numeric', 'min:0.01'], 'installments.*.due_on' => ['required_with:installments', 'date', 'after_or_equal:today']]);
        $student = Student::where('school_id', $schoolId)->findOrFail($data['student_id']);
        $invoice = DB::transaction(function () use ($creator, $student, $data, $schoolId) {
            $invoice = $creator->execute($student, Auth::user(), $data['items'], ['due_on' => $data['due_on'] ?? null, 'discount_total' => $data['discount_total'] ?? 0]);
            if (! empty($data['installments'])) {
                abort_if(round((float) collect($data['installments'])->sum('amount'), 2) !== round((float) $invoice->amount_due, 2), 422, 'Installments must equal the invoice balance.');
                foreach ($data['installments'] as $index => $installment) {
                    $invoice->installments()->create(['school_id' => $schoolId, 'sequence' => $index + 1, 'amount' => $installment['amount'], 'due_on' => $installment['due_on'], 'status' => 'pending']);
                }
            }

            return $invoice;
        });

        return redirect()->route('finance.invoices.show', $invoice)->with('success', 'Invoice issued successfully.');
    }

    public function showInvoice(FeeInvoice $invoice)
    {
        $this->tenant($invoice);
        $invoice->load(['student', 'items', 'installments', 'payments']);

        return view('finance.invoice-show', compact('invoice'));
    }

    public function storeExpense(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $data = $request->validate(['category' => ['required', 'string', 'max:60'], 'description' => ['required', 'string', 'max:255'], 'amount' => ['required', 'numeric', 'min:0.01'], 'expense_date' => ['required', 'date', 'before_or_equal:today']]);
        Expense::create([...$data, 'school_id' => $schoolId, 'reference' => 'EXP-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)), 'currency' => Auth::user()->school->currency, 'status' => 'pending', 'submitted_by' => Auth::id()]);

        return back()->with('success', 'Expense submitted for approval.');
    }

    public function approveExpense(Expense $expense)
    {
        $this->tenant($expense);
        abort_unless($expense->status === 'pending', 422);
        $expense->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);

        return back()->with('success', 'Expense approved.');
    }

    public function storeScholarship(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'discount_type' => ['required', Rule::in(['percentage', 'fixed'])], 'discount_value' => ['required', 'numeric', 'min:0.01'], 'starts_on' => ['nullable', 'date'], 'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on']]);
        Scholarship::create([...$data, 'school_id' => Auth::user()->school_id, 'is_active' => true]);

        return back()->with('success', 'Scholarship created.');
    }

    public function assignScholarship(Request $request, Scholarship $scholarship)
    {
        $this->tenant($scholarship);
        $data = $request->validate(['student_id' => ['required', Rule::exists('students', 'id')->where('school_id', Auth::user()->school_id)]]);
        $scholarship->students()->syncWithoutDetaching([$data['student_id'] => ['approved_by' => Auth::id()]]);

        return back()->with('success', 'Scholarship assigned.');
    }

    public function storePayroll(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $data = $request->validate(['period_start' => ['required', 'date'], 'period_end' => ['required', 'date', 'after_or_equal:period_start'], 'staff' => ['required', 'array', 'min:1'], 'staff.*.gross' => ['required', 'numeric', 'min:0'], 'staff.*.deductions' => ['nullable', 'numeric', 'min:0']]);
        $staff = User::where('school_id', $schoolId)->whereIn('role', ['teacher', 'school_admin'])->whereIn('id', array_keys($data['staff']))->pluck('id');
        abort_unless($staff->count() === count($data['staff']), 422, 'Payroll contains staff outside this school.');
        DB::transaction(function () use ($data, $staff, $schoolId) {
            $run = PayrollRun::create(['school_id' => $schoolId, 'reference' => 'PAYROLL-'.now()->format('Ym').'-'.Str::upper(Str::random(6)), 'period_start' => $data['period_start'], 'period_end' => $data['period_end'], 'status' => 'draft', 'created_by' => Auth::id()]);
            foreach ($staff as $id) {
                $gross = round((float) $data['staff'][$id]['gross'], 2);
                $deductions = round((float) ($data['staff'][$id]['deductions'] ?? 0), 2);
                abort_if($deductions > $gross, 422, 'Deductions cannot exceed gross pay.');
                $run->items()->create(['user_id' => $id, 'gross_amount' => $gross, 'deduction_amount' => $deductions, 'net_amount' => $gross - $deductions, 'status' => 'pending']);
            }$run->update(['gross_total' => $run->items()->sum('gross_amount'), 'deduction_total' => $run->items()->sum('deduction_amount'), 'net_total' => $run->items()->sum('net_amount')]);
        });

        return back()->with('success', 'Draft payroll run created.');
    }

    private function tenant($model): void
    {
        abort_unless($model->school_id === Auth::user()->school_id, 403);
    }
}
