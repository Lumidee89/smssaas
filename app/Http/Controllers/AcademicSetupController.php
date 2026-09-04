<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicSetupController extends Controller
{
    public function index(): View
    {
        $years = AcademicYear::where('school_id', Auth::user()->school_id)
            ->with(['terms' => fn ($query) => $query->orderBy('sequence')])
            ->latest('starts_on')
            ->get();

        return view('academic-setup.index', compact('years'));
    }

    public function storeYear(Request $request): RedirectResponse
    {
        $schoolId = Auth::user()->school_id;
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('academic_years')->where('school_id', $schoolId)],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
            'is_current' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($data, $schoolId): void {
            if ($data['is_current'] ?? false) {
                AcademicYear::where('school_id', $schoolId)->update(['is_current' => false]);
            }
            AcademicYear::create([...$data, 'school_id' => $schoolId, 'is_current' => (bool) ($data['is_current'] ?? false)]);
        });

        return back()->with('success', 'Academic year created. You can now add its terms.');
    }

    public function storeTerm(Request $request): RedirectResponse
    {
        $schoolId = Auth::user()->school_id;
        $data = $request->validate([
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'name' => ['required', 'string', 'max:60'],
            'sequence' => ['required', 'integer', 'min:1', 'max:10'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
            'is_current' => ['nullable', 'boolean'],
        ]);
        $year = AcademicYear::where('school_id', $schoolId)->findOrFail($data['academic_year_id']);
        if ($data['starts_on'] < $year->starts_on->toDateString() || $data['ends_on'] > $year->ends_on->toDateString()) {
            return back()->withInput()->withErrors(['starts_on' => 'Term dates must fall within the selected academic year.']);
        }
        if ($year->terms()->where('sequence', $data['sequence'])->exists()) {
            return back()->withInput()->withErrors(['sequence' => 'That term sequence already exists for this academic year.']);
        }

        DB::transaction(function () use ($data, $schoolId): void {
            if ($data['is_current'] ?? false) {
                AcademicTerm::where('school_id', $schoolId)->update(['is_current' => false]);
            }
            AcademicTerm::create([...$data, 'school_id' => $schoolId, 'is_current' => (bool) ($data['is_current'] ?? false)]);
        });

        return back()->with('success', 'Academic term created successfully.');
    }
}
