{{-- resources/views/teachers/edit.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Edit Teacher')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Edit Teacher</h1>
            <p class="text-slate-500 mt-1">Update teacher information and assigned subjects</p>
        </div>
        <a href="{{ route('teachers.index') }}" class="text-slate-500 hover:text-[#06322C] transition">
            ← Back to Teachers
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <form action="{{ route('teachers.update', $teacher) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Personal Information -->
                <div class="col-span-2">
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4 pb-2 border-b">Personal Information</h3>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $teacher->name) }}" required
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]"
                           placeholder="John Doe">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email', $teacher->email) }}" required
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]"
                           placeholder="teacher@school.com">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $teacher->phone) }}"
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]"
                           placeholder="+1234567890">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                    <div class="px-4 py-2 bg-slate-100 rounded-2xl text-slate-700">
                        {{ $teacher->email_verified_at ? 'Active' : 'Pending Verification' }}
                    </div>
                </div>
                
                <!-- Subjects Assignment -->
                <div class="col-span-2">
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4 pb-2 border-b mt-4">Subject Assignment</h3>
                    <p class="text-sm text-slate-500 mb-3">Select the subjects this teacher will teach</p>
                    
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
                            <span class="text-[#3A7B72] font-semibold" id="selectedCount">{{ count($assignedSubjects) }}</span> subjects currently assigned
                        </p>
                    @endif
                </div>
                
                <!-- Teacher Statistics -->
                <div class="col-span-2">
                    <div class="bg-slate-50 rounded-2xl p-4 mt-4">
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Teacher Statistics</h4>
                        <div class="grid md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-xs text-slate-400">Subjects Teaching</p>
                                <p class="text-lg font-semibold text-[#06322C]">{{ $teacher->taughtSubjects->count() }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Results Uploaded</p>
                                <p class="text-lg font-semibold text-[#06322C]">{{ $teacher->results->count() }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Member Since</p>
                                <p class="text-lg font-semibold text-[#06322C]">{{ $teacher->created_at->format('d M, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <a href="{{ route('teachers.index') }}" 
                   class="px-6 py-2 border border-slate-200 text-slate-700 rounded-2xl hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-[#06322C] text-white rounded-2xl hover:bg-[#0B4A41] transition shadow-sm">
                    Update Teacher
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
                    <p class="text-sm text-red-600 mt-1">Once you delete a teacher, all associated data will be permanently removed.</p>
                    @if($teacher->results()->count() > 0)
                        <p class="text-xs text-red-500 mt-2">
                            ⚠️ This teacher has uploaded {{ $teacher->results()->count() }} results. Deleting will remove all these records.
                        </p>
                    @endif
                </div>
                <button type="button" 
                        onclick="confirmDelete({{ $teacher->id }})" 
                        class="px-4 py-2 bg-red-600 text-white rounded-2xl hover:bg-red-700 transition">
                    Delete Teacher
                </button>
            </div>
        </div>
    </div>
</div>

<form id="delete-form-{{ $teacher->id }}" action="{{ route('teachers.destroy', $teacher) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this teacher? This action cannot be undone.')) {
            document.getElementById(`delete-form-${id}`).submit();
        }
    }
    
    // Update selected subjects count
    const checkboxes = document.querySelectorAll('input[name="subjects[]"]');
    const countDisplay = document.getElementById('selectedCount');
    
    function updateCount() {
        const checked = document.querySelectorAll('input[name="subjects[]"]:checked').length;
        if (countDisplay) {
            countDisplay.textContent = checked;
        }
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateCount);
    });
</script>
@endpush
@endsection