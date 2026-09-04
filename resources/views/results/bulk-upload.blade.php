{{-- resources/views/results/bulk-upload.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Bulk Upload Results')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Bulk Upload Results</h1>
            <p class="text-slate-500 mt-1">Upload multiple results at once using CSV/Excel file</p>
        </div>
        <a href="{{ route('results.index') }}" class="text-slate-500 hover:text-[#06322C] transition">
            ← Back to Results
        </a>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <!-- Upload Form -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-[#06322C]">Upload Results File</h3>
                </div>
                
                <form action="{{ route('results.bulk-upload') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    
                    <div class="space-y-4">
                        <!-- Class Selection -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Select Class *</label>
                            <select name="class_id" required
                                    class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }} {{ $class->section ? '- ' . $class->section : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Subject Selection -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Select Subject *</label>
                            <select name="subject_id" required
                                    class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }} ({{ $subject->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Exam Type -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Exam Type *</label>
                            <select name="exam_type" required
                                    class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                                <option value="">Select Type</option>
                                @foreach($examTypes as $type)
                                    <option value="{{ $type }}" {{ old('exam_type') == $type ? 'selected' : '' }}>
                                        {{ ucfirst($type) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('exam_type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Term -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Term *</label>
                            <select name="term" required
                                    class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                                <option value="">Select Term</option>
                                @foreach($terms as $term)
                                    <option value="{{ $term }}" {{ old('term') == $term ? 'selected' : '' }}>
                                        {{ $term }}
                                    </option>
                                @endforeach
                            </select>
                            @error('term')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Academic Year -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Academic Year *</label>
                            <select name="academic_year" required
                                    class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                                <option value="">Select Year</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year }}" {{ old('academic_year', date('Y')) == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                            @error('academic_year')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- File Upload -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Upload File *</label>
                            <input type="file" name="results_file" required accept=".csv,.xlsx,.xls"
                                   class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-[#06322C] hover:file:bg-emerald-50">
                            @error('results_file')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-slate-400 mt-2">Accepted formats: CSV, Excel (.xlsx, .xls)</p>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <a href="{{ route('results.index') }}" class="px-6 py-2 border border-slate-200 text-slate-700 rounded-2xl hover:bg-slate-50 transition">
                                Cancel
                            </a>
                            <button type="submit" class="px-6 py-2 bg-[#06322C] text-white rounded-2xl hover:bg-[#0B4A41] transition shadow-sm">
                                Upload Results
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Instructions Panel -->
        <div class="md:col-span-1">
            <div class="bg-emerald-50 rounded-2xl shadow-sm overflow-hidden sticky top-4">
                <div class="px-6 py-4 border-b border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-800">Instructions</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-medium text-blue-800 mb-2">File Format Requirements:</h4>
                            <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                                <li>CSV or Excel format (.csv, .xlsx, .xls)</li>
                                <li>Maximum file size: 5MB</li>
                                <li>First row should be headers</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-blue-800 mb-2">Required Columns:</h4>
                            <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                                <li><strong>admission_number</strong> - Student's admission number</li>
                                <li><strong>score</strong> - Score obtained (0-100)</li>
                                <li><strong>remarks</strong> - Optional remarks</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-blue-800 mb-2">Sample CSV Format:</h4>
                            <pre class="bg-white p-3 rounded-2xl text-xs overflow-x-auto">
admission_number,score,remarks
ADM202400001,85,Good performance
ADM202400002,72,Satisfactory
ADM202400003,91,Excellent
ADM202400004,68,Needs improvement</pre>
                        </div>
                        
                        <div>
                            <a href="#" class="text-[#3A7B72] hover:text-[#06322C] text-sm flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Download Sample CSV Template
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Important Notes -->
            <div class="bg-amber-50 rounded-2xl shadow-sm overflow-hidden mt-4">
                <div class="px-6 py-4 border-b border-yellow-200">
                    <h3 class="text-lg font-semibold text-yellow-800">Important Notes</h3>
                </div>
                <div class="p-6">
                    <ul class="text-sm text-yellow-700 space-y-2 list-disc list-inside">
                        <li>Make sure admission numbers exist in the system</li>
                        <li>Duplicate results will be skipped</li>
                        <li>Scores should be between 0 and the maximum score (default 100)</li>
                        <li>You can upload results for multiple students at once</li>
                        <li>Check the preview before confirming upload</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection