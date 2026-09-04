{{-- resources/views/students/show.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Student Details')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Student Details</h1>
            <p class="text-slate-500 mt-1">{{ $student->admission_number }} - {{ $student->full_name }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('students.edit', $student) }}" class="bg-[#3A7B72] hover:bg-[#2F675F] text-white px-4 py-2 rounded-2xl transition">
                Edit Student
            </a>
            <a href="{{ route('students.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-2xl transition">
                Back to List
            </a>
        </div>
    </div>

    <!-- Student Profile Card -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-[#06322C] to-[#3A7B72] px-6 py-4">
            <div class="flex items-center">
                <div class="w-20 h-20 rounded-full bg-white flex items-center justify-center">
                    <span class="text-2xl font-bold text-[#3A7B72]">{{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}</span>
                </div>
                <div class="ml-6 text-white">
                    <h2 class="text-2xl font-bold">{{ $student->full_name }}</h2>
                    <p class="opacity-90">Admission Number: {{ $student->admission_number }}</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4">Personal Information</h3>
                    <div class="space-y-3">
                        <div class="flex">
                            <span class="w-32 text-slate-500">Full Name:</span>
                            <span class="text-[#06322C] font-medium">{{ $student->full_name }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Email:</span>
                            <span class="text-[#06322C]">{{ $student->email }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Phone:</span>
                            <span class="text-[#06322C]">{{ $student->phone ?? 'N/A' }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Date of Birth:</span>
                            <span class="text-[#06322C]">{{ $student->date_of_birth->format('d M, Y') }} ({{ $student->age }} years)</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Gender:</span>
                            <span class="text-[#06322C] capitalize">{{ $student->gender }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Blood Group:</span>
                            <span class="text-[#06322C]">{{ $student->blood_group ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4">Academic Information</h3>
                    <div class="space-y-3">
                        <div class="flex">
                            <span class="w-32 text-slate-500">Class:</span>
                            <span class="text-[#06322C]">{{ $student->class ? $student->class->name : 'Not Assigned' }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Section:</span>
                            <span class="text-[#06322C]">{{ $student->class->section ?? 'N/A' }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Parent/Guardian:</span>
                            <span class="text-[#06322C]">{{ $student->parent ? $student->parent->name : 'Not Assigned' }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Average Score:</span>
                            <span class="text-[#06322C]">{{ number_format($averageScore, 2) ?? '0' }}%</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Total Payments:</span>
                            <span class="text-[#06322C]">${{ number_format($totalPayments, 2) }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-span-2">
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4">Address</h3>
                    <div class="bg-slate-50 p-4 rounded-2xl">
                        <p class="text-[#06322C]">{{ $student->address ?? 'No address provided' }}</p>
                    </div>
                </div>
                
                <div class="col-span-2">
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4">Emergency Contact</h3>
                    <div class="bg-slate-50 p-4 rounded-2xl">
                        <p class="text-[#06322C]">{{ $student->emergency_contact ?? 'No emergency contact provided' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Results -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-[#06322C]">Recent Results</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400">Exam Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400">Grade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400">Term</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentResults as $result)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $result->subject->name }}</td>
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
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $result->term }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                            No results found for this student
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-extrabold text-[#06322C]">Student mobile access</h3>
        <p class="mb-4 text-sm text-slate-500">Create or reset this learner's private SchoolOS Student login.</p>
        <form method="POST" action="{{ route('students.account', $student) }}" class="grid gap-3 md:grid-cols-3">@csrf
            <input type="email" name="email" value="{{ old('email', $student->userAccount?->email ?? $student->email) }}" required placeholder="Student login email" class="rounded-xl">
            <input type="password" name="password" minlength="8" required placeholder="Temporary password" class="rounded-xl">
            <input type="password" name="password_confirmation" minlength="8" required placeholder="Confirm password" class="rounded-xl">
            <button class="rounded-xl bg-[#06322C] py-3 font-bold text-white md:col-span-3">{{ $student->userAccount ? 'Reset student access' : 'Create student access' }}</button>
        </form>
    </section>
</div>
@endsection
