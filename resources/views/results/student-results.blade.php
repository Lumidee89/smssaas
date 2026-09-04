{{-- resources/views/results/student-results.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Student Results')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Student Results</h1>
            <p class="text-slate-500 mt-1">{{ $student->full_name }} ({{ $student->admission_number }})</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('results.create') }}?student_id={{ $student->id }}" 
               class="bg-[#06322C] hover:bg-[#0B4A41] text-white px-4 py-2 rounded-2xl transition">
                Add Result
            </a>
            <a href="{{ route('students.show', $student) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-2xl transition">
                Back to Student
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Overall Average</p>
                    <h3 class="text-2xl font-bold text-[#06322C]">{{ number_format($averageScore, 2) }}%</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Total Subjects</p>
                    <h3 class="text-2xl font-bold text-[#06322C]">{{ $results->unique('subject_id')->count() }}</h3>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Total Results</p>
                    <h3 class="text-2xl font-bold text-[#06322C]">{{ $results->count() }}</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#3A7B72]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Exam Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Percentage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Grade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Term</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Year</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($results as $result)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ $result->subject->name }}</span>
                                <br>
                                <span class="text-xs text-slate-400">{{ $result->subject->code }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full {{ $result->exam_type == 'exam' ? 'bg-emerald-50 text-[#3A7B72]' : 'bg-emerald-50 text-blue-800' }}">
                                {{ ucfirst($result->exam_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold">{{ $result->score }}/{{ $result->max_score }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm">{{ number_format($result->percentage, 1) }}%</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($result->grade == 'A+') bg-green-100 text-green-800
                                @elseif($result->grade == 'A') bg-green-100 text-green-800
                                @elseif($result->grade == 'B') bg-emerald-50 text-blue-800
                                @elseif($result->grade == 'C') bg-amber-50 text-yellow-800
                                @elseif($result->grade == 'D') bg-orange-100 text-orange-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $result->grade }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $result->term }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $result->academic_year }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <p class="text-lg">No results found for this student</p>
                            <p class="text-sm mt-1">Click "Add Result" to upload results</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection