{{-- resources/views/teachers/create.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Add New Teacher')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Add New Teacher</h1>
            <p class="text-slate-500 mt-1">Create a new teacher account</p>
        </div>
        <a href="{{ route('teachers.index') }}" class="text-slate-500 hover:text-[#06322C] transition">
            ← Back to Teachers
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <form action="{{ route('teachers.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Personal Information -->
                <div class="col-span-2">
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4 pb-2 border-b">Personal Information</h3>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]"
                           placeholder="John Doe">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]"
                           placeholder="teacher@school.com">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]"
                           placeholder="+1234567890">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Password *</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]"
                           placeholder="Minimum 8 characters">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Confirm Password *</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Qualification</label>
                    <input type="text" name="qualification" value="{{ old('qualification') }}"
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]"
                           placeholder="B.Ed, M.Sc, etc.">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Hire Date</label>
                    <input type="date" name="hire_date" value="{{ old('hire_date') }}"
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72] focus:border-[#3A7B72]">
                </div>
                
                <!-- Subjects Assignment -->
                <div class="col-span-2">
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4 pb-2 border-b mt-4">Subject Assignment</h3>
                    <p class="text-sm text-slate-500 mb-3">Select the subjects this teacher will teach</p>
                    
                    <div class="grid md:grid-cols-3 gap-3">
                        @foreach($subjects as $subject)
                            <label class="flex items-center space-x-2 p-2 hover:bg-slate-50 rounded-2xl">
                                <input type="checkbox" name="subjects[]" value="{{ $subject->id }}"
                                       {{ in_array($subject->id, old('subjects', [])) ? 'checked' : '' }}
                                       class="rounded border-slate-200 text-[#3A7B72] focus:ring-[#3A7B72]">
                                <span class="text-slate-700">{{ $subject->name }} ({{ $subject->code }})</span>
                            </label>
                        @endforeach
                    </div>
                    
                    @if($subjects->isEmpty())
                        <p class="text-yellow-600 text-sm mt-2">No subjects available. Please create subjects first.</p>
                    @endif
                </div>
                
                <!-- Address -->
                <div class="col-span-2">
                    <h3 class="text-lg font-semibold text-[#06322C] mb-4 pb-2 border-b mt-4">Address</h3>
                    <textarea name="address" rows="3" 
                              class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]"
                              placeholder="Enter teacher's address">{{ old('address') }}</textarea>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <a href="{{ route('teachers.index') }}" class="px-6 py-2 border border-slate-200 text-slate-700 rounded-2xl hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-[#06322C] text-white rounded-2xl hover:bg-[#0B4A41] transition shadow-sm">
                    Create Teacher
                </button>
            </div>
        </form>
    </div>
</div>
@endsection