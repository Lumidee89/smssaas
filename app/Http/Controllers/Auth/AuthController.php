<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Saas\ActivateSubscriptionPayment;
use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolSubscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Payments\PaystackGateway;
use App\Tenancy\TenantSchemaManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        $plans = SubscriptionPlan::where('is_active', true)->whereNotNull('monthly_price')->orderBy('sort_order')->get();

        return view('auth.register', compact('plans'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'subdomain' => 'required|string|unique:schools,subdomain|alpha_dash|min:3|max:50',
            'admin_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'billing_cycle' => ['required', Rule::in(['monthly', 'yearly'])],
            'plan_id' => ['required', Rule::exists('subscription_plans', 'id')->where(fn ($query) => $query->where('is_active', true)->whereNotNull('monthly_price'))],
        ]);

        try {
            [$school, $user, $payment] = DB::transaction(function () use ($request) {
                $plan = SubscriptionPlan::findOrFail($request->plan_id);
                $school = School::create([
                    'name' => $request->school_name,
                    'subdomain' => strtolower($request->subdomain),
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'is_active' => false,
                    'subscription_end_date' => null,
                ]);
                app(TenantSchemaManager::class)->provision($school);

                $user = User::create([
                    'name' => $request->admin_name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'school_admin',
                    'school_id' => $school->id,
                ]);

                $subscription = SchoolSubscription::create(['school_id' => $school->id, 'subscription_plan_id' => $plan->id, 'status' => 'past_due', 'starts_at' => now()]);
                $reference = 'SUB-'.$school->id.'-'.Str::upper(Str::random(16));
                $billingCycle = $request->string('billing_cycle')->toString();
                $amount = $billingCycle === 'yearly'
                    ? round((float) $plan->monthly_price * 12 * 0.80, 2)
                    : (float) $plan->monthly_price;
                $payment = SubscriptionPayment::create([
                    'school_id' => $school->id,
                    'school_subscription_id' => $subscription->id,
                    'reference' => $reference,
                    'amount' => $amount,
                    'currency' => $plan->currency,
                    'status' => 'pending',
                    'provider' => 'paystack',
                    'metadata' => [
                        'registration' => true,
                        'plan_id' => $plan->id,
                        'billing_cycle' => $billingCycle,
                        'discount_percentage' => $billingCycle === 'yearly' ? 20 : 0,
                    ],
                ]);

                return [$school, $user, $payment];
            });

            $checkout = app(PaystackGateway::class)->initialize($user->email, (float) $payment->amount, $payment->currency, $payment->reference, ['type' => 'school_subscription', 'school_id' => $school->id, 'subscription_payment_id' => $payment->id, 'callback_url' => route('registration.payment.callback')]);

            return redirect()->away($checkout['authorization_url']);
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput($request->except(['password', 'password_confirmation']))
                ->with('error', 'Registration failed. Please try again.');
        }
    }

    public function paymentCallback(Request $request, PaystackGateway $gateway, ActivateSubscriptionPayment $activate)
    {
        $reference = (string) $request->query('reference');
        $payment = SubscriptionPayment::where('reference', $reference)->with('school')->first();
        if (! $payment) {
            return redirect()->route('login')->with('error', 'We could not find that registration payment.');
        }

        try {
            if ($payment->status !== 'paid' && ! $activate->execute($payment, $gateway->verify($reference))) {
                return redirect()->route('login')->with('error', 'Payment was not completed. Your workspace is still locked.');
            }
        } catch (\Throwable $error) {
            report($error);

            return redirect()->route('login')->with('error', 'We could not verify the payment yet. Please contact support with reference '.$reference.'.');
        }

        $user = User::where('school_id', $payment->school_id)->where('role', 'school_admin')->oldest()->firstOrFail();
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Payment confirmed. Your SchoolOS workspace is now active.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            if (Auth::user()->school && ! Auth::user()->school->isSubscriptionActive()) {
                Auth::logout();

                return back()->withErrors(['email' => 'Subscription expired.']);
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
