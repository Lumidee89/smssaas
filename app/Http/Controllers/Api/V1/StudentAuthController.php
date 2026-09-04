<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentAuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string'], 'device_name' => ['required', 'string', 'max:100']]);
        $user = User::where('role', 'student')->whereRaw('LOWER(email) = ?', [strtolower($data['email'])])->with(['school', 'student'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password) || ! $user->student || ! $user->school?->isSubscriptionActive()) return response()->json(['message' => 'The student credentials are invalid.'], 422);
        $user->tokens()->where('name', $data['device_name'])->delete();
        $token = $user->createToken($data['device_name'], ['student:read', 'student:message', 'student:cbt']);
        return response()->json(['data' => ['token' => $token->plainTextToken, 'user' => ['id' => $user->id, 'name' => $user->name, 'student_id' => $user->student_id, 'school' => ['id' => $user->school_id, 'name' => $user->school->name]]]]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'Logged out.']);
    }
}
