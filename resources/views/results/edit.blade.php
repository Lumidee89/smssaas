{{-- resources/views/results/edit.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Edit Result')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Edit Result</h1>
            <p class="text-slate-500 mt-1">Update student result</p>
        </div>
        <a href="{{ route('results.index') }}" class="text-slate-500 hover:text-[#06322C] transition">
            ← Back to Results
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <form action="{{ route('results.update', $result) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <!-- Read-only Information -->
            <div class="bg-slate-50 rounded-2xl p-4 mb-6">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Result Information (Read-only)</h3>
                <div class="grid md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-slate-400">Student</p>
                        <p class="text-sm font-medium text-[#06322C]">{{ $result->student->full_name }}</p>
                        <p class="text-xs text-slate-400">{{ $result->student->admission_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Subject</p>
                        <p class="text-sm font-medium text-[#06322C]">{{ $result->subject->name }}</p>
                        <p class="text-xs text-slate-400">{{ $result->subject->code }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Exam Type & Term</p>
                        <p class="text-sm font-medium text-[#06322C]">{{ ucfirst($result->exam_type) }} - {{ $result->term }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Academic Year</p>
                        <p class="text-sm font-medium text-[#06322C]">{{ $result->academic_year }}</p>
                    </div>
                </div>
            </div>
            
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Max Score (Read-only) -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Maximum Score</label>
                    <input type="number" value="{{ $result->max_score }}" disabled
                           class="w-full px-4 py-2 bg-slate-100 border border-slate-200 rounded-2xl">
                    <p class="text-xs text-slate-400 mt-1">Maximum score cannot be changed</p>
                </div>
                
                <!-- Score Obtained -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Score Obtained *</label>
                    <input type="number" name="score" value="{{ old('score', $result->score) }}" required 
                           step="0.01" min="0" max="{{ $result->max_score }}"
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                    @error('score')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-slate-400 mt-1">Percentage: {{ number_format(($result->score / $result->max_score) * 100, 1) }}%</p>
                </div>
                
                <!-- Grade Display -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Grade</label>
                    <div class="px-4 py-2 bg-slate-100 border border-slate-200 rounded-2xl">
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
                </div>
                
                <!-- Remark -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Remark</label>
                    <div class="px-4 py-2 bg-slate-100 border border-slate-200 rounded-2xl">
                        <span class="text-sm">{{ $result->remark }}</span>
                    </div>
                </div>
                
                <!-- Remarks (Optional) -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Additional Remarks</label>
                    <textarea name="remarks" rows="3" 
                              class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]"
                              placeholder="Additional comments about the result">{{ old('remarks', $result->remarks) }}</textarea>
                </div>
                
                <!-- Live Preview -->
                <div class="col-span-2">
                    <div class="bg-emerald-50 rounded-2xl p-4">
                        <h4 class="text-sm font-semibold text-[#06322C] mb-2">Live Preview</h4>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs text-[#3A7B72]">Percentage</p>
                                <p class="text-lg font-bold text-[#06322C]" id="previewPercentage">{{ number_format(($result->score / $result->max_score) * 100, 1) }}%</p>
                            </div>
                            <div>
                                <p class="text-xs text-[#3A7B72]">Grade</p>
                                <p class="text-lg font-bold text-[#06322C]" id="previewGrade">{{ $result->grade }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-[#3A7B72]">Status</p>
                                <p class="text-lg font-bold text-[#06322C]" id="previewStatus">{{ $result->percentage >= 50 ? 'Pass' : 'Fail' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <a href="{{ route('results.index') }}" class="px-6 py-2 border border-slate-200 text-slate-700 rounded-2xl hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-[#06322C] text-white rounded-2xl hover:bg-[#0B4A41] transition shadow-sm">
                    Update Result
                </button>
            </div>
        </form>
    </div>
    
    <!-- Danger Zone -->
    <div class="mt-8">
        <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-red-800">Danger Zone</h3>
                    <p class="text-sm text-red-600 mt-1">Once you delete this result, it will be permanently removed.</p>
                </div>
                <button type="button" 
                        onclick="confirmDelete({{ $result->id }})" 
                        class="px-4 py-2 bg-red-600 text-white rounded-2xl hover:bg-red-700 transition">
                    Delete Result
                </button>
            </div>
        </div>
    </div>
</div>

<form id="delete-form-{{ $result->id }}" action="{{ route('results.destroy', $result) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this result? This action cannot be undone.')) {
            document.getElementById(`delete-form-${id}`).submit();
        }
    }
    
    // Live preview update
    const scoreInput = document.querySelector('input[name="score"]');
    const maxScore = {{ $result->max_score }};
    const previewPercentage = document.getElementById('previewPercentage');
    const previewGrade = document.getElementById('previewGrade');
    const previewStatus = document.getElementById('previewStatus');
    
    function getGrade(percentage) {
        if (percentage >= 90) return 'A+';
        if (percentage >= 80) return 'A';
        if (percentage >= 70) return 'B';
        if (percentage >= 60) return 'C';
        if (percentage >= 50) return 'D';
        return 'F';
    }
    
    function updatePreview() {
        let score = parseFloat(scoreInput.value) || 0;
        let percentage = (score / maxScore) * 100;
        let grade = getGrade(percentage);
        let status = percentage >= 50 ? 'Pass' : 'Fail';
        
        previewPercentage.textContent = percentage.toFixed(1) + '%';
        previewGrade.textContent = grade;
        previewStatus.textContent = status;
        
        // Update grade color
        let gradeClass = '';
        if (grade == 'A+' || grade == 'A') gradeClass = 'bg-green-100 text-green-800';
        else if (grade == 'B') gradeClass = 'bg-emerald-50 text-blue-800';
        else if (grade == 'C') gradeClass = 'bg-amber-50 text-yellow-800';
        else if (grade == 'D') gradeClass = 'bg-orange-100 text-orange-800';
        else gradeClass = 'bg-red-100 text-red-800';
        
        const gradeSpan = document.querySelector('.bg-slate-100 .rounded-full');
        if (gradeSpan) {
            gradeSpan.className = `px-2 py-1 text-sm rounded-full ${gradeClass}`;
            gradeSpan.textContent = grade;
        }
    }
    
    if (scoreInput) {
        scoreInput.addEventListener('input', updatePreview);
    }
</script>
@endpush
@endsection