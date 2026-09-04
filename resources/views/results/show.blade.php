{{-- resources/views/results/show.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Result Details')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Result Details</h1>
            <p class="text-slate-500 mt-1">View complete result information</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('results.edit', $result) }}" class="bg-[#3A7B72] hover:bg-[#2F675F] text-white px-4 py-2 rounded-2xl transition">
                Edit Result
            </a>
            <a href="{{ route('results.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-2xl transition">
                Back to List
            </a>
        </div>
    </div>

    <!-- Result Card -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-[#06322C] to-[#3A7B72] px-6 py-4">
            <h2 class="text-xl font-bold text-white">Result Summary</h2>
        </div>
        
        <div class="p-6">
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Student Information -->
                <div>
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4 pb-2 border-b">Student Information</h3>
                    <div class="space-y-3">
                        <div class="flex">
                            <span class="w-32 text-slate-500">Full Name:</span>
                            <span class="text-[#06322C] font-medium">{{ $result->student->full_name }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Admission No:</span>
                            <span class="text-[#06322C]">{{ $result->student->admission_number }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Class:</span>
                            <span class="text-[#06322C]">{{ $result->student->class->name ?? 'Not Assigned' }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Parent:</span>
                            <span class="text-[#06322C]">{{ $result->student->parent->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Subject Information -->
                <div>
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4 pb-2 border-b">Subject Information</h3>
                    <div class="space-y-3">
                        <div class="flex">
                            <span class="w-32 text-slate-500">Subject Name:</span>
                            <span class="text-[#06322C] font-medium">{{ $result->subject->name }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Subject Code:</span>
                            <span class="text-[#06322C]">{{ $result->subject->code }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Credit Hours:</span>
                            <span class="text-[#06322C]">{{ $result->subject->credit_hours }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Teacher:</span>
                            <span class="text-[#06322C]">{{ $result->teacher->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Exam Information -->
                <div>
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4 pb-2 border-b">Exam Information</h3>
                    <div class="space-y-3">
                        <div class="flex">
                            <span class="w-32 text-slate-500">Exam Type:</span>
                            <span class="text-[#06322C] capitalize">{{ $result->exam_type }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Term:</span>
                            <span class="text-[#06322C]">{{ $result->term }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Academic Year:</span>
                            <span class="text-[#06322C]">{{ $result->academic_year }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Date Recorded:</span>
                            <span class="text-[#06322C]">{{ $result->created_at->format('d M, Y') }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Result Information -->
                <div>
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4 pb-2 border-b">Result Information</h3>
                    <div class="space-y-3">
                        <div class="flex">
                            <span class="w-32 text-slate-500">Score:</span>
                            <span class="text-[#06322C] font-bold">{{ $result->score }}/{{ $result->max_score }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Percentage:</span>
                            <span class="text-[#06322C]">{{ number_format($result->percentage, 2) }}%</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Grade:</span>
                            <span class="px-2 py-1 text-sm rounded-full 
                                @if($result->grade == 'A+') bg-green-100 text-green-800
                                @elseif($result->grade == 'A') bg-green-100 text-green-800
                                @elseif($result->grade == 'B') bg-emerald-50 text-blue-800
                                @elseif($result->grade == 'C') bg-amber-50 text-yellow-800
                                @elseif($result->grade == 'D') bg-orange-100 text-orange-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $result->grade }}
                            </span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-slate-500">Status:</span>
                            <span class="px-2 py-1 text-sm rounded-full {{ $result->percentage >= 50 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $result->percentage >= 50 ? 'Pass' : 'Fail' }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Remarks -->
                @if($result->remarks)
                <div class="col-span-2">
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4 pb-2 border-b">Remarks</h3>
                    <div class="bg-slate-50 p-4 rounded-2xl">
                        <p class="text-[#06322C]">{{ $result->remarks }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection