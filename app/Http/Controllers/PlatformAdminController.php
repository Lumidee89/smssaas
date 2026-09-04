<?php

namespace App\Http\Controllers;

use App\Support\SchoolRole;
use App\Tenancy\TenantSchemaManager;
use App\Tenancy\DomainVerifier;
use App\Models\School;
use App\Models\SchoolSubscription;
use App\Models\Student;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlatformAdminController extends Controller
{
    public function index()
    {
        $schools = School::withCount(['students', 'users', 'teachers', 'parents'])->with(['subscriptions' => fn ($q) => $q->with('plan')->latest()])->latest()->paginate(25);
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        $stats = ['tenants' => School::count(), 'active' => School::where('is_active', true)->count(), 'students' => Student::count(), 'teachers' => User::where('role', 'teacher')->count(), 'parents' => User::where('role', 'parent')->count(), 'revenue' => (float) SubscriptionPayment::where('status', 'paid')->sum('amount'), 'monthly_recurring_revenue' => (float) SchoolSubscription::where('status', 'active')->with('plan')->get()->sum(fn ($subscription) => (float) ($subscription->plan->monthly_price ?? 0))];
        $payments = SubscriptionPayment::with('school')->latest()->limit(20)->get();
        $allSchools = School::orderBy('name')->get();
        $users = User::whereNot('role', 'super_admin')->with('school')->latest()->limit(30)->get();
        $students = Student::with(['school', 'class'])->latest()->limit(30)->get();

        $view = match (request()->route()->getName()) {
            'platform.schools.index' => 'platform.schools',
            'platform.users.index' => 'platform.users',
            'platform.payments.index' => 'platform.payments',
            default => 'platform.index',
        };

        return view($view, compact('schools', 'plans', 'stats', 'payments', 'allSchools', 'users', 'students'));
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate(['school_id' => ['required', 'exists:schools,id'], 'role' => ['required', Rule::in(array_values(array_diff(SchoolRole::ALL, [SchoolRole::PLATFORM_ADMIN])))], 'name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'unique:users,email'], 'phone' => ['nullable', 'string', 'max:32'], 'password' => ['required', 'string', 'min:8'], 'student_ids' => ['nullable', 'array'], 'student_ids.*' => ['integer']]);
        $user = User::create([...collect($data)->only(['school_id', 'role', 'name', 'email', 'phone'])->all(), 'password' => Hash::make($data['password']), 'email_verified_at' => now()]);
        if ($user->role === 'parent' && ! empty($data['student_ids'])) {
            $ids = Student::where('school_id', $user->school_id)->whereIn('id', $data['student_ids'])->pluck('id');
            $user->children()->attach($ids->mapWithKeys(fn ($id) => [$id => ['school_id' => $user->school_id, 'relationship' => 'guardian', 'is_primary' => true, 'can_pick_up' => true, 'receives_billing' => true]])->all());
        }

        return back()->with('success', ucfirst($user->role).' account created for the selected school.');
    }

    public function storeStudent(Request $request)
    {
        $data = $request->validate(['school_id' => ['required', 'exists:schools,id'], 'first_name' => ['required', 'string'], 'last_name' => ['required', 'string'], 'email' => ['required', 'email', 'unique:students,email'], 'date_of_birth' => ['required', 'date'], 'gender' => ['required', Rule::in(['male', 'female', 'other'])]]);
        $sequence = Student::where('school_id', $data['school_id'])->count() + 1;
        Student::create([...$data, 'admission_number' => 'ADM'.$data['school_id'].date('Y').str_pad($sequence, 5, '0', STR_PAD_LEFT)]);

        return back()->with('success', 'Student added to the selected school.');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'subdomain' => ['required', 'alpha_dash', 'min:3', 'max:50', 'unique:schools,subdomain'], 'email' => ['required', 'email', 'max:255', 'unique:schools,email'], 'phone' => ['required', 'string', 'max:30'], 'address' => ['required', 'string', 'max:2000'], 'institution_type' => ['required', Rule::in(['primary', 'secondary', 'college', 'polytechnic', 'university'])], 'plan_id' => ['required', Rule::exists('subscription_plans', 'id')->where('is_active', true)], 'admin_name' => ['required', 'string', 'max:255'], 'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'], 'temporary_password' => ['required', 'string', 'min:12', 'max:100']]);
        DB::transaction(function () use ($data) {
            $plan = SubscriptionPlan::findOrFail($data['plan_id']);
            $school = School::create(['name' => $data['name'], 'subdomain' => $data['subdomain'], 'email' => $data['email'], 'phone' => $data['phone'], 'address' => $data['address'], 'institution_type' => $data['institution_type'], 'theme_color' => '#06322C', 'currency' => 'NGN', 'timezone' => 'Africa/Lagos', 'is_active' => true, 'subscription_end_date' => now()->addDays(30)]);
            app(TenantSchemaManager::class)->provision($school);
            $admin = User::create(['name' => $data['admin_name'], 'email' => $data['admin_email'], 'password' => Hash::make($data['temporary_password']), 'role' => 'school_admin', 'school_id' => $school->id]);
            SchoolSubscription::create(['school_id' => $school->id, 'subscription_plan_id' => $plan->id, 'status' => 'trialing', 'starts_at' => now(), 'trial_ends_at' => now()->addDays(30), 'current_period_ends_at' => now()->addDays(30), 'changed_by' => Auth::id()]);
        });

        return back()->with('success', 'Tenant provisioned with a 30-day trial.');
    }

    public function updateSubscription(Request $request, School $school)
    {
        $data = $request->validate(['plan_id' => ['required', Rule::exists('subscription_plans', 'id')->where('is_active', true)], 'status' => ['required', Rule::in(['trialing', 'active', 'past_due', 'suspended', 'cancelled', 'expired'])], 'period_ends_at' => ['nullable', 'date', 'after:today']]);
        DB::transaction(function () use ($data, $school) {
            $school->subscriptions()->whereIn('status', ['trialing', 'active', 'past_due'])->update(['status' => 'cancelled', 'cancelled_at' => now(), 'changed_by' => Auth::id()]);
            $end = filled($data['period_ends_at'] ?? null) ? $data['period_ends_at'] : now()->addMonth();
            SchoolSubscription::create(['school_id' => $school->id, 'subscription_plan_id' => $data['plan_id'], 'status' => $data['status'], 'starts_at' => now(), 'current_period_ends_at' => $end, 'changed_by' => Auth::id()]);
            $school->update(['is_active' => ! in_array($data['status'], ['suspended', 'cancelled', 'expired'], true), 'subscription_end_date' => $end]);
        });

        return back()->with('success', 'Subscription updated.');
    }

    public function updateBrand(Request $request, School $school)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'theme_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'currency' => ['required', 'string', 'size:3'], 'timezone' => ['required', 'timezone']]);
        $school->update($data);

        return back()->with('success', 'Tenant branding updated.');
    }

    public function updateDomain(Request $request, School $school)
    {
        $data = $request->validate(['custom_domain' => ['nullable', 'string', 'max:255', 'regex:/^(?!https?:\/\/)[a-z0-9.-]+\.[a-z]{2,}$/i', Rule::unique('schools')->ignore($school->id)]]);
        $domain = filled($data['custom_domain'] ?? null) ? strtolower(trim($data['custom_domain'], '.')) : null;
        $school->update(['custom_domain' => $domain, 'domain_verified_at' => null, 'domain_verification_token' => $domain ? 'schoolos-'.Str::lower(Str::random(40)) : null]);
        return back()->with('success', $domain ? 'Domain saved. Add the displayed TXT record, then verify it.' : 'Custom domain removed.');
    }

    public function verifyDomain(School $school, DomainVerifier $verifier)
    {
        abort_unless($school->custom_domain && $school->domain_verification_token, 422, 'Configure a custom domain first.');
        abort_unless($verifier->verify($school->custom_domain, $school->domain_verification_token), 422, 'Verification TXT record was not found yet.');
        $school->update(['domain_verified_at' => now()]);
        return back()->with('success', 'Custom domain verified and activated.');
    }

    public function recordPayment(Request $request, School $school)
    {
        $subscription = $school->subscriptions()->latest()->firstOrFail();
        $data = $request->validate(['amount' => ['required', 'numeric', 'min:1'], 'currency' => ['required', 'string', 'size:3'], 'reference' => ['required', 'string', 'max:100', 'unique:subscription_payments,reference'], 'paid_at' => ['required', 'date', 'before_or_equal:now']]);
        SubscriptionPayment::create([...$data, 'school_id' => $school->id, 'school_subscription_id' => $subscription->id, 'status' => 'paid', 'provider' => 'manual']);

        return back()->with('success', 'Subscription payment recorded.');
    }
}
