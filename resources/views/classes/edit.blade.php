{{-- resources/views/classes/edit.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Edit Class')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Edit Class</h1>
            <p class="text-slate-500 mt-1">Update class information</p>
        </div>
        <a href="{{ route('classes.index') }}" class="text-slate-500 hover:text-[#06322C] transition">
            ← Back to Classes
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <form action="{{ route('classes.update', $class) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Class Name -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Class Name *</label>
                    <input type="text" name="name" value="{{ old('name', $class->name) }}" required
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]"
                           placeholder="e.g., Grade 1, Class 5, Form 3">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Section -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Section</label>
                    <input type="text" name="section" value="{{ old('section', $class->section) }}"
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]"
                           placeholder="e.g., A, B, C, Science, Arts">
                    @error('section')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Class Teacher -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Class Teacher</label>
                    <select name="teacher_id" class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]">
                        <option value="">Select Class Teacher</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" 
                                {{ old('teacher_id', $class->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }} ({{ $teacher->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('teacher_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Maximum Capacity -->
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Maximum Capacity</label>
                    <input type="number" name="capacity" value="{{ old('capacity', $class->capacity) }}"
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]"
                           placeholder="Leave empty for unlimited" min="1" max="500">
                    @error('capacity')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-slate-400 mt-1">
                        Current enrollment: {{ $class->students()->count() }} students
                    </p>
                </div>
                
                <!-- Subjects Offered -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-3">Subjects Offered</label>
                    <div class="grid md:grid-cols-3 gap-3 border border-gray-200 rounded-2xl p-4 max-h-60 overflow-y-auto">
                        @foreach($subjects as $subject)
                            <label class="flex items-center space-x-2 p-2 hover:bg-slate-50 rounded-2xl cursor-pointer">
                                <input type="checkbox" name="subjects[]" value="{{ $subject->id }}"
                                       {{ in_array($subject->id, old('subjects', $assignedSubjects)) ? 'checked' : '' }}
                                       class="rounded border-slate-200 text-[#3A7B72] focus:ring-[#3A7B72]">
                                <span class="text-slate-700">{{ $subject->name }}</span>
                                <span class="text-xs text-slate-400">({{ $subject->code }})</span>
                            </label>
                        @endforeach
                    </div>
                    @if($subjects->isEmpty())
                        <p class="text-yellow-600 text-sm mt-2">No subjects available. Please create subjects first.</p>
                    @else
                        <p class="text-xs text-slate-400 mt-2">
                            <span class="text-[#3A7B72] font-semibold">{{ count($assignedSubjects) }}</span> subjects currently assigned
                        </p>
                    @endif
                </div>
                
                <!-- Class Statistics -->
                <div class="col-span-2">
                    <div class="bg-slate-50 rounded-2xl p-4 mt-4">
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Class Statistics</h4>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs text-slate-400">Total Students</p>
                                <p class="text-lg font-semibold text-[#06322C]">{{ $class->students()->count() }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Subjects Assigned</p>
                                <p class="text-lg font-semibold text-[#06322C]">{{ $class->subjects->count() }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Created Date</p>
                                <p class="text-lg font-semibold text-[#06322C]">{{ $class->created_at->format('d M, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <a href="{{ route('classes.index') }}" 
                   class="px-6 py-2 border border-slate-200 text-slate-700 rounded-2xl hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-[#06322C] text-white rounded-2xl hover:bg-[#0B4A41] transition shadow-sm">
                    Update Class
                </button>
            </div>
        </form>
    </div>
    
    <!-- Danger Zone (Optional - for destructive actions) -->
    <div class="mt-8">
        <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-red-800">Danger Zone</h3>
                    <p class="text-sm text-red-600 mt-1">Once you delete a class, there is no going back. Please be certain.</p>
                    @if($class->students()->count() > 0)
                        <p class="text-xs text-red-500 mt-2">
                            ⚠️ This class has {{ $class->students()->count() }} students. You cannot delete it until you transfer all students.
                        </p>
                    @endif
                </div>
                <button type="button" 
                        onclick="confirmDelete({{ $class->id }})" 
                        {{ $class->students()->count() > 0 ? 'disabled' : '' }}
                        class="px-4 py-2 bg-red-600 text-white rounded-2xl hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Delete Class
                </button>
            </div>
        </div>
    </div>
</div>

<form id="delete-form-{{ $class->id }}" action="{{ route('classes.destroy', $class) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this class? This action cannot be undone.')) {
            document.getElementById(`delete-form-${id}`).submit();
        }
    }
    
    // Show selected subjects count
    const checkboxes = document.querySelectorAll('input[name="subjects[]"]');
    const updateCount = () => {
        const checked = document.querySelectorAll('input[name="subjects[]"]:checked').length;
        const countDisplay = document.querySelector('.text-[#3A7B72].font-semibold');
        if (countDisplay) {
            countDisplay.textContent = checked;
        }
    };
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateCount);
    });
    
    // Capacity validation
    const capacityInput = document.querySelector('input[name="capacity"]');
    if (capacityInput) {
        capacityInput.addEventListener('change', function() {
            const currentStudents = {{ $class->students()->count() }};
            if (this.value && parseInt(this.value) < currentStudents) {
                alert(`Warning: Capacity cannot be less than current enrollment (${currentStudents} students).`);
                this.value = currentStudents;
            }
        });
    }
</script>
@endpush
@endsection