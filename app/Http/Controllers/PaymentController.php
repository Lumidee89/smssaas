<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $schoolId = $user->school_id;

        $query = Payment::query()->with(['student', 'parent']);

        if ($user->role !== 'super_admin') {
            $query->where('school_id', $schoolId);
        }

        if ($user->role === 'parent') {
            $query->where('parent_id', $user->id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_type') && $request->payment_type) {
            $query->where('payment_type', $request->payment_type);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);

        $statsQuery = Payment::query()
            ->when($user->role !== 'super_admin', fn ($query) => $query->where('school_id', $schoolId))
            ->when($user->role === 'parent', fn ($query) => $query->where('parent_id', $user->id));
        $stats = [
            'total_collected' => (clone $statsQuery)->where('status', 'paid')->sum('amount'),
            'pending_amount' => (clone $statsQuery)->where('status', 'pending')->sum('amount'),
            'total_transactions' => (clone $statsQuery)->count(),
        ];

        return view('payments.index', compact('payments', 'stats'));
    }

    public function create(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $students = Student::where('school_id', $schoolId)->with('class')->get();

        $selectedStudent = null;
        if ($request->has('student_id')) {
            $selectedStudent = Student::where('school_id', $schoolId)->find($request->student_id);
        }

        return view('payments.create', compact('students', 'selectedStudent'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => ['required', Rule::exists('students', 'id')->where('school_id', Auth::user()->school_id)],
            'amount' => 'required|numeric|min:1',
            'payment_type' => 'required|in:tuition,exam_fee,library_fee,sports_fee,other',
            'term' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $invoiceNumber = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(6));

        $payment = Payment::create([
            'school_id' => Auth::user()->school_id,
            'student_id' => $request->student_id,
            'parent_id' => Student::findOrFail($request->student_id)->parent_id ?? Auth::id(),
            'invoice_number' => $invoiceNumber,
            'amount' => $request->amount,
            'payment_type' => $request->payment_type,
            'term' => $request->term,
            'status' => 'pending',
            'description' => $request->description,
        ]);

        // Redirect to payment gateway or show payment instructions
        return redirect()->route('payments.show', $payment)->with('success', 'Payment invoice created. Please complete the payment.');
    }

    public function show(Payment $payment)
    {
        $this->authorizePayment($payment);

        $payment->load(['student', 'parent']);

        return view('payments.show', compact('payment'));
    }

    public function processPayment(Request $request, Payment $payment)
    {
        $this->authorizePayment($payment);

        abort_if($payment->status !== 'pending', 409, 'Only pending payments can be settled.');
        abort_if(
            filled($payment->provider) || filled($payment->provider_reference),
            422,
            'Online payment attempts can only be settled by verified gateway confirmation.'
        );

        $request->validate([
            'payment_method' => ['required', Rule::in(['bank_transfer', 'cash'])],
            'transaction_id' => ['required_if:payment_method,bank_transfer', 'nullable', 'string', 'max:255'],
        ]);

        $payment->markAsPaid($request->transaction_id, $request->payment_method);

        return redirect()->route('payments.show', $payment)->with('success', 'Offline payment recorded successfully.');
    }

    public function receipt(Payment $payment)
    {
        $this->authorizePayment($payment);
        abort_unless($payment->status === 'paid', 404);

        $payment->load(['student', 'student.class', 'parent']);

        return view('payments.receipt', compact('payment'));
    }

    private function authorizePayment($payment)
    {
        $user = Auth::user();

        if ($user->role === 'super_admin') {
            return;
        }

        if ($user->role === 'school_admin') {
            if ($payment->school_id !== $user->school_id) {
                abort(403);
            }
        } elseif ($user->role === 'parent') {
            if ($payment->school_id !== $user->school_id || $payment->parent_id !== $user->id) {
                abort(403);
            }
        } else {
            abort(403);
        }
    }
}
