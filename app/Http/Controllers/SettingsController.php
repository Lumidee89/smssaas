<?php

// app/Http/Controllers/SettingsController.php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        $school = $user->school;

        return view('settings.index', compact('user', 'school'));
    }

    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        // Manual update instead of using update() method
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();

        return redirect()->route('settings.index')->with('success', 'Profile updated successfully.');
    }

    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        // Manual update
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();

        return redirect()->route('settings.index')->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Manual password update
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('settings.index')->with('success', 'Password updated successfully.');
    }

    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $school = Auth::user()->school;

        if ($school) {
            // Manual update for school
            $school->theme_color = $request->theme_color;
            $school->save();
        }

        return redirect()->route('settings.index')->with('success', 'Theme updated successfully.');
    }

    public function updateSchool(Request $request)
    {
        $school = Auth::user()->school;

        if (! $school) {
            return redirect()->route('settings.index')->with('error', 'School not found.');
        }

        $request->validate([
            'school_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        // Manual school update
        $school->name = $request->school_name;
        $school->email = $request->email;
        $school->phone = $request->phone;
        $school->address = $request->address;
        $school->save();

        return redirect()->route('settings.index')->with('success', 'School information updated successfully.');
    }
}
