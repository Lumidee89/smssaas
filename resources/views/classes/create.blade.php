{{-- resources/views/classes/create.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Add New Class')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Add New Class</h1>
            <p class="text-slate-500 mt-1">Create a new class for your school</p>
        </div>
        <a href="{{ route('classes.index') }}" class="text-slate-500 hover:text-[#06322C] transition">
            ← Back to Classes
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <form action="{{ route('classes.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Class Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]"
                           placeholder="e.g., Grade 1, Class 5, Form 3">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Section</label>
                    <input type="text" name="section" value="{{ old('section') }}"
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]"
                           placeholder="e.g., A, B, C, Science, Arts">
                    @error('section')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Class Teacher</label>
                    <select name="teacher_id" class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                        <option value="">Select Class Teacher</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->name }} ({{ $teacher->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('teacher_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Maximum Capacity</label>
                    <input type="number" name="capacity" value="{{ old('capacity') }}"
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]"
                           placeholder="Leave empty for unlimited">
                    @error('capacity')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-3">Subjects Offered</label>
                    <div class="grid md:grid-cols-3 gap-3 border border-gray-200 rounded-2xl p-4 max-h-60 overflow-y-auto">
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
            </div>
            
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <a href="{{ route('classes.index') }}" class="px-6 py-2 border border-slate-200 text-slate-700 rounded-2xl hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-[#06322C] text-white rounded-2xl hover:bg-[#0B4A41] transition shadow-sm">
                    Create Class
                </button>
            </div>
        </form>
    </div>
</div>
@endsection