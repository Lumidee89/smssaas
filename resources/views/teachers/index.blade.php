{{-- resources/views/teachers/index.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Teacher Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Teacher Management</h1>
            <p class="text-slate-500 mt-1">Manage all teachers in your school</p>
        </div>
        <a href="{{ route('teachers.create') }}" 
           class="bg-[#06322C] hover:bg-[#0B4A41] text-white font-semibold px-6 py-2 rounded-2xl transition shadow-sm flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add New Teacher
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Total Teachers</p>
                    <h3 class="text-2xl font-bold text-[#06322C]">{{ $totalTeachers }}</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Total Subjects</p>
                    <h3 class="text-2xl font-bold text-[#06322C]">{{ $totalSubjects }}</h3>
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
                    <p class="text-slate-400 text-sm">Active Teachers</p>
                    <h3 class="text-2xl font-bold text-[#06322C]">{{ $activeTeachers }}</h3>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#3A7B72]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Teacher-Student Ratio</p>
                    <h3 class="text-2xl font-bold text-[#06322C]">
                        @php
                            $totalStudents = \App\Models\Student::where('school_id', Auth::user()->school_id)->count();
                            $ratio = $totalTeachers > 0 ? round($totalStudents / $totalTeachers, 1) : 0;
                        @endphp
                        {{ $ratio }}:1
                    </h3>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-2xl shadow-sm p-4 mb-6">
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <input type="text" id="search" placeholder="Search by name or email..." 
                       class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
            </div>
            <div>
                <select id="subject_filter" class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                    <option value="">All Subjects</option>
                    @php
                        // Get unique subjects from all teachers
                        $allSubjects = collect();
                        foreach($teachers as $teacher) {
                            foreach($teacher->taughtSubjects as $subject) {
                                $allSubjects->push($subject);
                            }
                        }
                        $uniqueSubjects = $allSubjects->unique('id');
                    @endphp
                    @foreach($uniqueSubjects as $subject)
                        <option value="{{ $subject->name }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button id="reset_filters" class="w-full px-4 py-2 bg-gray-200 text-slate-700 rounded-2xl hover:bg-gray-300 transition">
                    Reset Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Teachers Table -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Teacher</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Subjects</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($teachers as $teacher)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-[#06322C] to-[#3A7B72] flex items-center justify-center mr-3">
                                    <span class="text-white font-semibold text-sm">{{ substr($teacher->name, 0, 2) }}</span>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-900">{{ $teacher->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $teacher->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $teacher->phone ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @forelse($teacher->taughtSubjects as $subject)
                                    <span class="px-2 py-1 text-xs rounded-full bg-emerald-50 text-blue-800">
                                        {{ $subject->name }}
                                    </span>
                                @empty
                                    <span class="text-sm text-slate-400">No subjects assigned</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                Active
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                            {{ $teacher->created_at->format('d M, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('teachers.show', $teacher) }}" 
                                   class="text-[#3A7B72] hover:text-[#06322C] transition" title="View">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('teachers.edit', $teacher) }}" 
                                   class="text-green-600 hover:text-green-900 transition" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <button type="button" onclick="resetPassword({{ $teacher->id }}, '{{ $teacher->name }}')" 
                                        class="text-yellow-600 hover:text-yellow-900 transition" title="Reset Password">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                    </svg>
                                </button>
                                <button type="button" onclick="confirmDelete({{ $teacher->id }})" 
                                        class="text-red-600 hover:text-red-900 transition" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                                <form id="delete-form-{{ $teacher->id }}" action="{{ route('teachers.destroy', $teacher) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                <form id="reset-password-form-{{ $teacher->id }}" action="{{ route('teachers.reset-password', $teacher) }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <p class="text-lg">No teachers found</p>
                            <p class="text-sm mt-1">Click "Add New Teacher" to get started</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $teachers->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Search functionality
    document.getElementById('search').addEventListener('keyup', function() {
        filterTable();
    });
    
    document.getElementById('subject_filter').addEventListener('change', function() {
        filterTable();
    });
    
    document.getElementById('reset_filters').addEventListener('click', function() {
        document.getElementById('search').value = '';
        document.getElementById('subject_filter').value = '';
        filterTable();
    });
    
    function filterTable() {
        const searchTerm = document.getElementById('search').value.toLowerCase();
        const subjectFilter = document.getElementById('subject_filter').value.toLowerCase();
        
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            if (row.cells.length === 1) return; // Skip "no results" row
            
            const teacherName = row.cells[0]?.textContent.toLowerCase() || '';
            const email = row.cells[1]?.textContent.toLowerCase() || '';
            const subjects = row.cells[3]?.textContent.toLowerCase() || '';
            
            let show = true;
            
            if (searchTerm && !teacherName.includes(searchTerm) && !email.includes(searchTerm)) {
                show = false;
            }
            
            if (subjectFilter && !subjects.includes(subjectFilter)) {
                show = false;
            }
            
            row.style.display = show ? '' : 'none';
        });
    }
    
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this teacher? This action cannot be undone.')) {
            document.getElementById(`delete-form-${id}`).submit();
        }
    }
    
    function resetPassword(id, name) {
        if (confirm(`Reset password for ${name}? A new temporary password will be generated.`)) {
            document.getElementById(`reset-password-form-${id}`).submit();
        }
    }
</script>
@endpush
@endsection