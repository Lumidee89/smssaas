{{-- resources/views/subjects/edit.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Edit Subject')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Edit Subject</h1>
            <p class="text-slate-500 mt-1">Update subject information</p>
        </div>
        <a href="{{ route('subjects.index') }}" class="text-slate-500 hover:text-[#06322C] transition">
            ← Back to Subjects
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <form action="{{ route('subjects.update', $subject) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Subject Name *</label>
                    <input type="text" name="name" value="{{ old('name', $subject->name) }}" required
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]"
                           placeholder="e.g., Mathematics, English, Physics">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Subject Code *</label>
                    <input type="text" name="code" value="{{ old('code', $subject->code) }}" required
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]"
                           placeholder="e.g., MATH101, ENG201, PHY301">
                    @error('code')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-slate-400 mt-1">Unique code for this subject</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Credit Hours</label>
                    <input type="number" name="credit_hours" value="{{ old('credit_hours', $subject->credit_hours) }}" min="1" max="10"
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                    @error('credit_hours')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                    <textarea name="description" rows="3" 
                              class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]"
                              placeholder="Brief description of the subject">{{ old('description', $subject->description) }}</textarea>
                </div>
                
                <!-- Assign Teachers -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-3">Assign Teachers</label>
                    <div class="grid md:grid-cols-3 gap-3 border border-gray-200 rounded-2xl p-4 max-h-60 overflow-y-auto">
                        @foreach($teachers as $teacher)
                            <label class="flex items-center space-x-2 p-2 hover:bg-slate-50 rounded-2xl cursor-pointer">
                                <input type="checkbox" name="teachers[]" value="{{ $teacher->id }}"
                                       {{ in_array($teacher->id, old('teachers', $assignedTeachers)) ? 'checked' : '' }}
                                       class="rounded border-slate-200 text-[#3A7B72] focus:ring-[#3A7B72]">
                                <span class="text-slate-700">{{ $teacher->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if($teachers->isEmpty())
                        <p class="text-yellow-600 text-sm mt-2">No teachers available. Please create teachers first.</p>
                    @else
                        <p class="text-xs text-slate-400 mt-2">
                            <span class="text-[#3A7B72] font-semibold" id="teacherCount">{{ count($assignedTeachers) }}</span> teachers currently assigned
                        </p>
                    @endif
                </div>
                
                <!-- Assign Classes -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-3">Assign Classes</label>
                    <div class="grid md:grid-cols-3 gap-3 border border-gray-200 rounded-2xl p-4 max-h-60 overflow-y-auto">
                        @foreach($classes as $class)
                            <label class="flex items-center space-x-2 p-2 hover:bg-slate-50 rounded-2xl cursor-pointer">
                                <input type="checkbox" name="classes[]" value="{{ $class->id }}"
                                       {{ in_array($class->id, old('classes', $assignedClasses)) ? 'checked' : '' }}
                                       class="rounded border-slate-200 text-[#3A7B72] focus:ring-[#3A7B72]">
                                <span class="text-slate-700">{{ $class->name }} {{ $class->section ? '- ' . $class->section : '' }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if($classes->isEmpty())
                        <p class="text-yellow-600 text-sm mt-2">No classes available. Please create classes first.</p>
                    @else
                        <p class="text-xs text-slate-400 mt-2">
                            <span class="text-[#3A7B72] font-semibold" id="classCount">{{ count($assignedClasses) }}</span> classes currently assigned
                        </p>
                    @endif
                </div>
                
                <!-- Subject Statistics -->
                <div class="col-span-2">
                    <div class="bg-slate-50 rounded-2xl p-4 mt-4">
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Subject Statistics</h4>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs text-slate-400">Teachers Assigned</p>
                                <p class="text-lg font-semibold text-[#06322C]">{{ $subject->teachers->count() }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Classes Offering</p>
                                <p class="text-lg font-semibold text-[#06322C]">{{ $subject->classes->count() }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Results Recorded</p>
                                <p class="text-lg font-semibold text-[#06322C]">{{ $subject->results->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <a href="{{ route('subjects.index') }}" 
                   class="px-6 py-2 border border-slate-200 text-slate-700 rounded-2xl hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-[#06322C] text-white rounded-2xl hover:bg-[#0B4A41] transition shadow-sm">
                    Update Subject
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
                    <p class="text-sm text-red-600 mt-1">Once you delete a subject, all associated data will be permanently removed.</p>
                    @if($subject->results()->count() > 0)
                        <p class="text-xs text-red-500 mt-2">
                            ⚠️ This subject has {{ $subject->results()->count() }} results recorded. Deleting will remove all these records.
                        </p>
                    @endif
                </div>
                <button type="button" 
                        onclick="confirmDelete({{ $subject->id }})" 
                        {{ $subject->results()->count() > 0 ? 'disabled' : '' }}
                        class="px-4 py-2 bg-red-600 text-white rounded-2xl hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Delete Subject
                </button>
            </div>
        </div>
    </div>
</div>

<form id="delete-form-{{ $subject->id }}" action="{{ route('subjects.destroy', $subject) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this subject? This action cannot be undone.')) {
            document.getElementById(`delete-form-${id}`).submit();
        }
    }
    
    // Update teacher count
    const teacherCheckboxes = document.querySelectorAll('input[name="teachers[]"]');
    const teacherCountDisplay = document.getElementById('teacherCount');
    
    function updateTeacherCount() {
        const checked = document.querySelectorAll('input[name="teachers[]"]:checked').length;
        if (teacherCountDisplay) {
            teacherCountDisplay.textContent = checked;
        }
    }
    
    teacherCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateTeacherCount);
    });
    
    // Update class count
    const classCheckboxes = document.querySelectorAll('input[name="classes[]"]');
    const classCountDisplay = document.getElementById('classCount');
    
    function updateClassCount() {
        const checked = document.querySelectorAll('input[name="classes[]"]:checked').length;
        if (classCountDisplay) {
            classCountDisplay.textContent = checked;
        }
    }
    
    classCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateClassCount);
    });
</script>
@endpush
@endsection