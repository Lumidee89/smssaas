{{-- resources/views/subjects/show.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Subject Details')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Subject Details</h1>
            <p class="text-slate-500 mt-1">{{ $subject->name }} ({{ $subject->code }})</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('subjects.edit', $subject) }}" class="bg-[#3A7B72] hover:bg-[#2F675F] text-white px-4 py-2 rounded-2xl transition">
                Edit Subject
            </a>
            <a href="{{ route('subjects.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-2xl transition">
                Back to List
            </a>
        </div>
    </div>

    <!-- Subject Info Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Credit Hours</p>
                    <h3 class="text-2xl font-bold text-[#06322C]">{{ $subject->credit_hours }}</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Teachers</p>
                    <h3 class="text-2xl font-bold text-[#06322C]">{{ $totalTeachers }}</h3>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Classes</p>
                    <h3 class="text-2xl font-bold text-[#06322C]">{{ $totalClasses }}</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#3A7B72]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Average Score</p>
                    <h3 class="text-2xl font-bold text-[#06322C]">{{ number_format($averageScore, 2) }}%</h3>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Description -->
    @if($subject->description)
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-[#06322C]">Description</h3>
        </div>
        <div class="p-6">
            <p class="text-slate-700">{{ $subject->description }}</p>
        </div>
    </div>
    @endif

    <!-- Assigned Teachers -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-[#06322C]">Assigned Teachers</h3>
            <a href="{{ route('subjects.edit', $subject) }}#teachers" class="text-[#3A7B72] hover:text-[#06322C] text-sm font-medium">
                Manage Teachers
            </a>
        </div>
        <div class="p-6">
            <div class="flex flex-wrap gap-3">
                @forelse($subject->teachers as $teacher)
                    <div class="flex items-center space-x-2 bg-slate-50 rounded-2xl p-2">
                        <div class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center">
                            <span class="text-[#3A7B72] font-semibold text-xs">{{ substr($teacher->name, 0, 2) }}</span>
                        </div>
                        <span class="text-slate-700">{{ $teacher->name }}</span>
                    </div>
                @empty
                    <p class="text-slate-400">No teachers assigned to this subject</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Offering Classes -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-[#06322C]">Offering Classes</h3>
            <a href="{{ route('subjects.edit', $subject) }}#classes" class="text-[#3A7B72] hover:text-[#06322C] text-sm font-medium">
                Manage Classes
            </a>
        </div>
        <div class="p-6">
            <div class="flex flex-wrap gap-3">
                @forelse($subject->classes as $class)
                    <span class="px-3 py-2 bg-emerald-50 text-blue-800 rounded-2xl">
                        {{ $class->name }} {{ $class->section ? '- ' . $class->section : '' }}
                    </span>
                @empty
                    <p class="text-slate-400">No classes offering this subject</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection