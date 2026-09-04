{{-- resources/views/classes/show.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Class Details')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Class Details</h1>
            <p class="text-slate-500 mt-1">{{ $class->name }} {{ $class->section ? '- ' . $class->section : '' }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('classes.edit', $class) }}" class="bg-[#3A7B72] hover:bg-[#2F675F] text-white px-4 py-2 rounded-2xl transition">
                Edit Class
            </a>
            <a href="{{ route('classes.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-2xl transition">
                Back to List
            </a>
        </div>
    </div>

    <!-- Class Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Total Students</p>
                    <h3 class="text-2xl font-bold text-[#06322C]">{{ $totalStudents }}</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Available Seats</p>
                    <h3 class="text-2xl font-bold text-[#06322C]">{{ $availableSeats }}</h3>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Subjects</p>
                    <h3 class="text-2xl font-bold text-[#06322C]">{{ $subjectsCount }}</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#3A7B72]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Class Teacher</p>
                    <h3 class="text-xl font-bold text-[#06322C]">{{ $class->teacher ? $class->teacher->name : 'Not Assigned' }}</h3>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Subjects List -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-[#06322C]">Subjects Offered</h3>
        </div>
        <div class="p-6">
            <div class="flex flex-wrap gap-2">
                @forelse($class->subjects as $subject)
                    <span class="px-3 py-2 bg-emerald-50 text-blue-800 rounded-2xl">
                        {{ $subject->name }} ({{ $subject->code }})
                    </span>
                @empty
                    <p class="text-slate-400">No subjects assigned to this class</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Students List -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-[#06322C]">Students Enrolled</h3>
            <a href="{{ route('students.create') }}?class_id={{ $class->id }}" 
               class="text-[#3A7B72] hover:text-[#06322C] text-sm font-medium">
                + Add Student
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400">Admission No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400">Student Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400">Gender</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400">Parent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($class->students as $student)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $student->admission_number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $student->full_name }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500 capitalize">{{ $student->gender }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $student->parent ? $student->parent->name : 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $student->phone ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm font-medium">
                            <a href="{{ route('students.show', $student) }}" class="text-[#3A7B72] hover:text-[#06322C]">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                            No students enrolled in this class
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection