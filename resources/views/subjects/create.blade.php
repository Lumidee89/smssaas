{{-- resources/views/subjects/create.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Add New Subject')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#06322C]">Add New Subject</h1>
            <p class="text-slate-500 mt-1">Create a new subject for your school</p>
        </div>
        <a href="{{ route('subjects.index') }}" class="text-slate-500 hover:text-[#06322C] transition">
            ← Back to Subjects
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <form action="{{ route('subjects.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Subject Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]"
                           placeholder="e.g., Mathematics, English, Physics">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Subject Code *</label>
                    <input type="text" name="code" value="{{ old('code') }}" required
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]"
                           placeholder="e.g., MATH101, ENG201, PHY301">
                    @error('code')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-slate-400 mt-1">Unique code for this subject</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Credit Hours</label>
                    <input type="number" name="credit_hours" value="{{ old('credit_hours', 1) }}" min="1" max="10"
                           class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]">
                    @error('credit_hours')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Description</label>
                    <textarea name="description" rows="3" 
                              class="w-full px-4 py-2 border border-slate-200 rounded-2xl focus:ring-2 focus:ring-[#3A7B72]"
                              placeholder="Brief description of the subject">{{ old('description') }}</textarea>
                </div>
                
                <!-- Assign Teachers -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-3">Assign Teachers</label>
                    <div class="grid md:grid-cols-3 gap-3 border border-gray-200 rounded-2xl p-4 max-h-60 overflow-y-auto">
                        @foreach($teachers as $teacher)
                            <label class="flex items-center space-x-2 p-2 hover:bg-slate-50 rounded-2xl cursor-pointer">
                                <input type="checkbox" name="teachers[]" value="{{ $teacher->id }}"
                                       {{ in_array($teacher->id, old('teachers', [])) ? 'checked' : '' }}
                                       class="rounded border-slate-200 text-[#3A7B72] focus:ring-[#3A7B72]">
                                <span class="text-slate-700">{{ $teacher->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if($teachers->isEmpty())
                        <p class="text-yellow-600 text-sm mt-2">No teachers available. Please create teachers first.</p>
                    @endif
                </div>
                
                <!-- Assign Classes -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-3">Assign Classes</label>
                    <div class="grid md:grid-cols-3 gap-3 border border-gray-200 rounded-2xl p-4 max-h-60 overflow-y-auto">
                        @foreach($classes as $class)
                            <label class="flex items-center space-x-2 p-2 hover:bg-slate-50 rounded-2xl cursor-pointer">
                                <input type="checkbox" name="classes[]" value="{{ $class->id }}"
                                       {{ in_array($class->id, old('classes', [])) ? 'checked' : '' }}
                                       class="rounded border-slate-200 text-[#3A7B72] focus:ring-[#3A7B72]">
                                <span class="text-slate-700">{{ $class->name }} {{ $class->section ? '- ' . $class->section : '' }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if($classes->isEmpty())
                        <p class="text-yellow-600 text-sm mt-2">No classes available. Please create classes first.</p>
                    @endif
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <a href="{{ route('subjects.index') }}" class="px-6 py-2 border border-slate-200 text-slate-700 rounded-2xl hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-[#06322C] text-white rounded-2xl hover:bg-[#0B4A41] transition shadow-sm">
                    Create Subject
                </button>
            </div>
        </form>
    </div>
</div>
@endsection