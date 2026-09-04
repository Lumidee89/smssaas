{{-- resources/views/results/create.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Add New Result')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Add New Result</h1>
            <p class="text-slate-500 mt-1">Upload student result</p>
        </div>
        <a href="{{ route('results.index') }}" class="text-slate-500 hover:text-[#06322C] transition">
            ← Back to Results
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <form action="{{ route('results.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Student Selection -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Select Student *</label>
                    <select name="student_id" required
                            class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                        <option value="">Select Student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id', $selectedStudent->id ?? '') == $student->id ? 'selected' : '' }}>
                                {{ $student->full_name }} ({{ $student->admission_number }}) - {{ $student->class->name ?? 'No Class' }}
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
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
                                {{ $subject->name }} ({{ $subject->code }}) - {{ $subject->credit_hours }} Credit Hours
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
                
                <!-- Max Score -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Maximum Score *</label>
                    <input type="number" name="max_score" value="{{ old('max_score', 100) }}" required step="1" min="1"
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                    @error('max_score')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Score -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Score Obtained *</label>
                    <input type="number" name="score" value="{{ old('score') }}" required step="0.01" min="0"
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                    @error('score')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Remarks -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Remarks (Optional)</label>
                    <textarea name="remarks" rows="3" 
                              class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]"
                              placeholder="Additional comments about the result">{{ old('remarks') }}</textarea>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <a href="{{ route('results.index') }}" class="px-6 py-2 border border-slate-200 text-slate-700 rounded-2xl hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-[#06322C] text-white rounded-2xl hover:bg-[#0B4A41] transition shadow-sm">
                    Save Result
                </button>
            </div>
        </form>
    </div>
</div>
@endsection