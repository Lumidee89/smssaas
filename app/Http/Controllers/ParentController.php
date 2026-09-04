<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ParentController extends Controller
{
    public function index()
    {
        $schoolId = Auth::user()->school_id;
        $parents = User::where('school_id', $schoolId)->where('role', 'parent')->with('children.class')->latest()->paginate(15);
        $students = Student::where('school_id', $schoolId)->with('class')->orderBy('first_name')->get();

        return view('parents.index', compact('parents', 'students'));
    }

    public function store(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $data = $this->validated($request, $schoolId);
        DB::transaction(function () use ($data, $schoolId) {
            $parent = User::create(['school_id' => $schoolId, 'role' => 'parent', 'name' => $data['name'], 'phone' => $data['phone'], 'email' => strtolower($data['email']), 'password' => Hash::make($data['password']), 'email_verified_at' => now()]);
            $this->link($parent, $data, $schoolId);
            Student::whereIn('id', $data['student_ids'])->where('school_id', $schoolId)->whereNull('parent_id')->update(['parent_id' => $parent->id]);
        });

        return back()->with('success', 'Parent account created and linked to the selected student(s).');
    }

    public function update(Request $request, User $parent)
    {
        $schoolId = Auth::user()->school_id;
        abort_unless($parent->role === 'parent' && $parent->school_id === $schoolId, 404);
        $data = $this->validated($request, $schoolId, $parent);
        $values = ['name' => $data['name'], 'phone' => $data['phone'], 'email' => strtolower($data['email'])];
        if (filled($data['password'] ?? null)) {
            $values['password'] = Hash::make($data['password']);
        }
        DB::transaction(function () use ($parent, $values, $data, $schoolId): void {
            $previousStudentIds = $parent->children()->pluck('students.id');
            $parent->update($values);
            $this->link($parent, $data, $schoolId);
            Student::whereIn('id', $previousStudentIds->diff($data['student_ids']))
                ->where('school_id', $schoolId)
                ->where('parent_id', $parent->id)
                ->update(['parent_id' => null]);
            Student::whereIn('id', $data['student_ids'])
                ->where('school_id', $schoolId)
                ->whereNull('parent_id')
                ->update(['parent_id' => $parent->id]);
        });

        return back()->with('success', 'Parent account updated.');
    }

    private function validated(Request $request, int $schoolId, ?User $parent = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32', Rule::unique('users')->ignore($parent)->where('school_id', $schoolId)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($parent)],
            'password' => [$parent ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => [Rule::exists('students', 'id')->where('school_id', $schoolId)],
            'relationship' => ['required', Rule::in(['mother', 'father', 'guardian', 'other'])],
        ]);
    }

    private function link(User $parent, array $data, int $schoolId): void
    {
        $links = collect($data['student_ids'])->mapWithKeys(fn ($id) => [$id => ['school_id' => $schoolId, 'relationship' => $data['relationship'], 'is_primary' => true, 'can_pick_up' => true, 'receives_billing' => true]])->all();
        $parent->children()->sync($links);
    }
}
