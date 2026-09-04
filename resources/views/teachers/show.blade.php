{{-- resources/views/teachers/show.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Teacher Details')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Teacher Details</h1>
            <p class="text-slate-500 mt-1">{{ $teacher->name }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('teachers.edit', $teacher) }}" class="bg-[#3A7B72] hover:bg-[#2F675F] text-white px-4 py-2 rounded-2xl transition">
                Edit Teacher
            </a>
            <a href="{{ route('teachers.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-2xl transition">
                Back to List
            </a>
        </div>
    </div>

    <!-- Teacher Profile Card -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-[#06322C] to-[#3A7B72] px-6 py-4">
            <div class="flex items-center">
                <div class="w-20 h-20 rounded-full bg-white flex items-center justify-center">
                    <span class="text-2xl font-bold text-[#3A7B72]">{{ substr($teacher->name, 0, 2) }}</span>
                </div>
                <div class="ml-6 text-white">
                    <h2 class="text-2xl font-bold">{{ $teacher->name }}</h2>
                    <p class="opacity-90">Teacher</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4">Contact Information</h3>
                    <div class="space-y-3">
                        <div class="flex">
                            <span class="w-32 text-slate-500">Full Name:</span>
                            <span class="text-[#06322C] font-medium">{{ $teacher->name }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Email:</span>
                            <span class="text-[#06322C]">{{ $teacher->email }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Phone:</span>
                            <span class="text-[#06322C]">{{ $teacher->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Member Since:</span>
                            <span class="text-[#06322C]">{{ $teacher->created_at->format('d M, Y') }}</span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4">Teaching Statistics</h3>
                    <div class="space-y-3">
                        <div class="flex">
                            <span class="w-32 text-slate-500">Subjects Taught:</span>
                            <span class="text-[#06322C]">{{ $teacher->taughtSubjects->count() }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Total Students:</span>
                            <span class="text-[#06322C]">{{ $totalStudentsTaught }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Results Uploaded:</span>
                            <span class="text-[#06322C]">{{ $totalResultsUploaded }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Average Score:</span>
                            <span class="text-[#06322C]">{{ number_format($averageScoreGiven, 2) }}%</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-span-2">
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4">Subjects Taught</h3>
                    <div class="flex flex-wrap gap-2">
                        @forelse($teacher->taughtSubjects as $subject)
                            <span class="px-3 py-2 bg-emerald-50 text-blue-800 rounded-2xl">
                                {{ $subject->name }} ({{ $subject->code }})
                            </span>
                        @empty
                            <p class="text-slate-400">No subjects assigned yet</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Results Uploaded -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-[#06322C]">Recent Results Uploaded</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400">Exam Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400">Grade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentResults as $result)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $result->student->full_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $result->subject->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500 capitalize">{{ $result->exam_type }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $result->score }}/{{ $result->max_score }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($result->grade == 'A+') bg-green-100 text-green-800
                                @elseif($result->grade == 'A') bg-emerald-50 text-blue-800
                                @elseif($result->grade == 'B') bg-emerald-50 text-[#06322C]
                                @elseif($result->grade == 'C') bg-amber-50 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $result->grade }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $result->created_at->format('d M, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                            No results uploaded yet
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection