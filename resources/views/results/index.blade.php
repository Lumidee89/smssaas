{{-- resources/views/results/index.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Result Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Result Management</h1>
            <p class="text-slate-500 mt-1">Manage student results and academic performance</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('results.create') }}" 
               class="bg-[#06322C] hover:bg-[#0B4A41] text-white font-semibold px-6 py-2 rounded-2xl transition shadow-sm flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Result
            </a>
            <a href="{{ route('results.bulk-upload.form') }}" 
               class="bg-[#3A7B72] hover:bg-[#2F675F] text-white font-semibold px-6 py-2 rounded-2xl transition shadow-sm flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Bulk Upload
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Total Results</p>
                    <h3 class="text-2xl font-bold text-[#06322C]">{{ $totalResults }}</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
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
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Total Students</p>
                    <h3 class="text-2xl font-bold text-[#06322C]">{{ $totalStudents }}</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#3A7B72]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Subjects</p>
                    <h3 class="text-2xl font-bold text-[#06322C]">{{ $totalSubjects }}</h3>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
        <form method="GET" action="{{ route('results.index') }}" class="grid md:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Class</label>
                <select name="class_id" class="w-full px-3 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} {{ $class->section ? '- ' . $class->section : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Subject</label>
                <select name="subject_id" class="w-full px-3 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Exam Type</label>
                <select name="exam_type" class="w-full px-3 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                    <option value="">All Types</option>
                    @foreach($examTypes as $type)
                        <option value="{{ $type }}" {{ request('exam_type') == $type ? 'selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Term</label>
                <select name="term" class="w-full px-3 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                    <option value="">All Terms</option>
                    @foreach($terms as $term)
                        <option value="{{ $term }}" {{ request('term') == $term ? 'selected' : '' }}>
                            {{ $term }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Academic Year</label>
                <select name="academic_year" class="w-full px-3 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                    <option value="">All Years</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year }}" {{ request('academic_year') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="flex-1 bg-[#06322C] text-white px-4 py-2 rounded-2xl hover:bg-[#0B4A41] transition">
                    Filter
                </button>
                <a href="{{ route('results.index') }}" class="flex-1 bg-gray-200 text-slate-700 px-4 py-2 rounded-2xl hover:bg-gray-300 transition text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Exam Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Grade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Term</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Year</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($results as $result)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center mr-3">
                                    <span class="text-[#3A7B72] font-semibold text-xs">{{ substr($result->student->first_name, 0, 1) }}{{ substr($result->student->last_name, 0, 1) }}</span>
                                </div>
                                <span class="text-sm text-gray-900">{{ $result->student->full_name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $result->subject->name }}</span>
                            <br>
                            <span class="text-xs text-slate-400">{{ $result->subject->code }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full {{ $result->exam_type == 'exam' ? 'bg-emerald-50 text-[#3A7B72]' : 'bg-emerald-50 text-blue-800' }}">
                                {{ ucfirst($result->exam_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold">{{ $result->score }}/{{ $result->max_score }}</span>
                            <br>
                            <span class="text-xs text-slate-400">{{ number_format($result->percentage, 1) }}%</span>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('results.show', $result) }}" 
                                   class="text-[#3A7B72] hover:text-[#06322C] transition" title="View">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('results.edit', $result) }}" 
                                   class="text-green-600 hover:text-green-900 transition" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <button type="button" onclick="confirmDelete({{ $result->id }})" 
                                        class="text-red-600 hover:text-red-900 transition" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                                <form id="delete-form-{{ $result->id }}" action="{{ route('results.destroy', $result) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <p class="text-lg">No results found</p>
                            <p class="text-sm mt-1">Click "Add Result" to upload results</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $results->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this result? This action cannot be undone.')) {
            document.getElementById(`delete-form-${id}`).submit();
        }
    }
</script>
@endpush
@endsection