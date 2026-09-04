<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\OtpSender;
use App\Http\Controllers\Controller;
use App\Models\OtpChallenge;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class ParentAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        if ($request->filled(['phone', 'code'])) {
            return $this->verifyOtp($request);
        }
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string'], 'device_name' => ['required', 'string', 'max:100']]);
        $parent = User::where('role', 'parent')->whereRaw('LOWER(email) = ?', [strtolower($data['email'])])->with('school')->first();
        if (! $parent || ! Hash::check($data['password'], $parent->password)) {
            return response()->json(['message' => 'The email or password is incorrect.', 'code' => 'INVALID_CREDENTIALS'], 422);
        }
        if (! $parent->school?->isSubscriptionActive()) {
            return response()->json(['message' => 'This parent account is unavailable.', 'code' => 'ACCOUNT_UNAVAILABLE'], 403);
        }
        $parent->tokens()->where('name', $data['device_name'])->delete();
        $token = $parent->createToken($data['device_name'], ['parent:read', 'parent:pay', 'parent:message']);

        return response()->json(['data' => ['token' => $token->plainTextToken, 'token_type' => 'Bearer', 'user' => ['id' => $parent->id, 'name' => $parent->name, 'email' => $parent->email, 'phone' => $parent->phone, 'school' => ['id' => $parent->school->id, 'name' => $parent->school->name, 'currency' => $parent->school->currency]]]]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $data = $request->validate(['current_password' => ['required', 'string'], 'password' => ['required', 'string', 'min:8', 'confirmed']]);
        if (! Hash::check($data['current_password'], $request->user()->password)) {
            return response()->json(['message' => 'The current password is incorrect.', 'code' => 'INVALID_CURRENT_PASSWORD'], 422);
        }
        $request->user()->update(['password' => Hash::make($data['password'])]);
        $request->user()->tokens()->whereKeyNot($request->user()->currentAccessToken()->id)->delete();

        return response()->json(['message' => 'Password changed successfully.']);
    }

    public function requestOtp(Request $request, OtpSender $sender): JsonResponse
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:32']]);
        $phone = $this->normalize($data['phone']);
        $key = 'parent-otp:'.$phone;
        abort_if(RateLimiter::tooManyAttempts($key, 3), 429, 'Too many requests. Try again later.');
        RateLimiter::hit($key, 600);
        $parent = User::where('role', 'parent')->where('phone', $phone)->whereHas('school', fn ($q) => $q->where('is_active', true))->first();
        if ($parent) {
            $code = (string) random_int(100000, 999999);
            OtpChallenge::where('phone', $phone)->where('purpose', 'parent_login')->whereNull('consumed_at')->delete();
            OtpChallenge::create(['phone' => $phone, 'purpose' => 'parent_login', 'code_hash' => Hash::make($code), 'expires_at' => now()->addMinutes(10)]);
            $sender->send($phone, $code);
        }

        return response()->json(['message' => 'If the phone number is linked to an active parent account, a code has been sent.']);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:32'], 'code' => ['required', 'digits:6'], 'device_name' => ['required', 'string', 'max:100']]);
        $phone = $this->normalize($data['phone']);
        $challenge = OtpChallenge::where('phone', $phone)->where('purpose', 'parent_login')->whereNull('consumed_at')->latest()->first();
        if (! $challenge || $challenge->expires_at->isPast() || $challenge->attempts >= 5 || ! Hash::check($data['code'], $challenge->code_hash)) {
            if ($challenge) {
                $challenge->increment('attempts');
            }

            return response()->json(['message' => 'The verification code is invalid or expired.', 'code' => 'INVALID_OTP'], 422);
        }
        $parent = User::where('role', 'parent')->where('phone', $phone)->with('school')->first();
        if (! $parent || ! $parent->school?->isSubscriptionActive()) {
            return response()->json(['message' => 'This parent account is unavailable.', 'code' => 'ACCOUNT_UNAVAILABLE'], 403);
        }
        $challenge->update(['consumed_at' => now()]);
        $token = $parent->createToken($data['device_name'], ['parent:read', 'parent:pay', 'parent:message']);

        return response()->json(['data' => ['token' => $token->plainTextToken, 'token_type' => 'Bearer', 'user' => ['id' => $parent->id, 'name' => $parent->name, 'phone' => $parent->phone, 'school' => ['id' => $parent->school->id, 'name' => $parent->school->name, 'currency' => $parent->school->currency]]]]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Signed out successfully.']);
    }

    private function normalize(string $phone): string
    {
        $value = preg_replace('/[^0-9+]/', '', $phone);

        return str_starts_with($value, '00') ? '+'.substr($value, 2) : $value;
    }
}
