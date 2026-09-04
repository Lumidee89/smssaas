{{-- resources/views/students/edit.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Edit Student')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Edit Student</h1>
            <p class="text-slate-500 mt-1">Update student information</p>
        </div>
        <a href="{{ route('students.index') }}" class="text-slate-500 hover:text-[#06322C] transition">
            ← Back to Students
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <form action="{{ route('students.update', $student) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Personal Information -->
                <div class="col-span-2">
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4 pb-2 border-b">Personal Information</h3>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $student->first_name) }}" required
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]">
                    @error('first_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Last Name *</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $student->last_name) }}" required
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]">
                    @error('last_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Admission Number</label>
                    <input type="text" value="{{ $student->admission_number }}" disabled
                           class="w-full px-4 py-2 bg-slate-100 border border-slate-200 rounded-2xl text-slate-500">
                    <p class="text-xs text-slate-400 mt-1">Admission number cannot be changed</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $student->email) }}" required
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $student->phone) }}"
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Date of Birth *</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth->format('Y-m-d')) }}" required
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]">
                    @error('date_of_birth')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Gender *</label>
                    <select name="gender" required
                            class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender', $student->gender) == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender', $student->gender) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Blood Group</label>
                    <select name="blood_group" class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                        <option value="">Select Blood Group</option>
                        <option value="A+" {{ old('blood_group', $student->blood_group) == 'A+' ? 'selected' : '' }}>A+</option>
                        <option value="A-" {{ old('blood_group', $student->blood_group) == 'A-' ? 'selected' : '' }}>A-</option>
                        <option value="B+" {{ old('blood_group', $student->blood_group) == 'B+' ? 'selected' : '' }}>B+</option>
                        <option value="B-" {{ old('blood_group', $student->blood_group) == 'B-' ? 'selected' : '' }}>B-</option>
                        <option value="O+" {{ old('blood_group', $student->blood_group) == 'O+' ? 'selected' : '' }}>O+</option>
                        <option value="O-" {{ old('blood_group', $student->blood_group) == 'O-' ? 'selected' : '' }}>O-</option>
                        <option value="AB+" {{ old('blood_group', $student->blood_group) == 'AB+' ? 'selected' : '' }}>AB+</option>
                        <option value="AB-" {{ old('blood_group', $student->blood_group) == 'AB-' ? 'selected' : '' }}>AB-</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Emergency Contact</label>
                    <input type="text" name="emergency_contact" value="{{ old('emergency_contact', $student->emergency_contact) }}"
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]"
                           placeholder="Emergency contact number">
                </div>
                
                <!-- Academic Information -->
                <div class="col-span-2">
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4 pb-2 border-b mt-4">Academic Information</h3>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Class</label>
                    <select name="class_id" class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" 
                                {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} {{ $class->section ? '- ' . $class->section : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Parent/Guardian</label>
                    <select name="parent_id" class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                        <option value="">Select Parent</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" 
                                {{ old('parent_id', $student->parent_id) == $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }} ({{ $parent->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Address -->
                <div class="col-span-2">
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4 pb-2 border-b mt-4">Address Information</h3>
                </div>
                
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Address</label>
                    <textarea name="address" rows="3" 
                              class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">{{ old('address', $student->address) }}</textarea>
                </div>
                
                <!-- Student Statistics -->
                <div class="col-span-2">
                    <div class="bg-slate-50 rounded-2xl p-4 mt-4">
                        <h4 class="text-sm font-semibold text-slate-700 mb-3">Student Statistics</h4>
                        <div class="grid md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-xs text-slate-400">Average Score</p>
                                <p class="text-lg font-semibold text-[#06322C]">{{ number_format($student->results->avg('score'), 2) }}%</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Total Payments</p>
                                <p class="text-lg font-semibold text-[#06322C]">${{ number_format($student->payments->where('status', 'paid')->sum('amount'), 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Results</p>
                                <p class="text-lg font-semibold text-[#06322C]">{{ $student->results->count() }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Enrolled Since</p>
                                <p class="text-lg font-semibold text-[#06322C]">{{ $student->created_at->format('d M, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <a href="{{ route('students.index') }}" 
                   class="px-6 py-2 border border-slate-200 text-slate-700 rounded-2xl hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-[#06322C] text-white rounded-2xl hover:bg-[#0B4A41] transition shadow-sm">
                    Update Student
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
                    <p class="text-sm text-red-600 mt-1">Once you delete a student, all associated data will be permanently removed.</p>
                    @if($student->results()->count() > 0 || $student->payments()->count() > 0)
                        <p class="text-xs text-red-500 mt-2">
                            ⚠️ This student has {{ $student->results()->count() }} results and {{ $student->payments()->count() }} payments. 
                            Deleting will remove all these records.
                        </p>
                    @endif
                </div>
                <button type="button" 
                        onclick="confirmDelete({{ $student->id }})" 
                        class="px-4 py-2 bg-red-600 text-white rounded-2xl hover:bg-red-700 transition">
                    Delete Student
                </button>
            </div>
        </div>
    </div>
</div>

<form id="delete-form-{{ $student->id }}" action="{{ route('students.destroy', $student) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
            document.getElementById(`delete-form-${id}`).submit();
        }
    }
    
    // Calculate age from date of birth
    const dobInput = document.querySelector('input[name="date_of_birth"]');
    if (dobInput) {
        dobInput.addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            // Show age hint
            const ageHint = document.createElement('p');
            ageHint.className = 'text-xs text-slate-400 mt-1';
            ageHint.textContent = `Age: ${age} years`;
            
            const existingHint = this.parentNode.querySelector('.age-hint');
            if (existingHint) {
                existingHint.remove();
            }
            ageHint.classList.add('age-hint');
            this.parentNode.appendChild(ageHint);
        });
    }
</script>
@endpush
@endsection