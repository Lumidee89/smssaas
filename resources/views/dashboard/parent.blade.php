{{-- resources/views/dashboard/parent.blade.php --}}
@extends('layouts.app')

@section('page_title', 'Parent Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Welcome Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Welcome, {{ Auth::user()->name }}!</h1>
        <p class="text-gray-600 mt-1">Track your children's academic progress and payments.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">My Children</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $students->count() }}</h3>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending Payments</p>
                    <h3 class="text-2xl font-bold text-gray-800">${{ number_format($pendingPayments, 2) }}</h3>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Paid</p>
                    <h3 class="text-2xl font-bold text-gray-800">${{ number_format($payments->where('status', 'paid')->sum('amount'), 2) }}</h3>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Children List -->
    <div class="grid md:grid-cols-2 gap-6 mb-8">
        @foreach($students as $student)
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center">
                            <span class="text-indigo-600 font-bold text-lg">{{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}</span>
                        </div>
                        <div class="ml-4 text-white">
                            <h3 class="text-lg font-semibold">{{ $student->full_name }}</h3>
                            <p class="text-sm opacity-90">Admission: {{ $student->admission_number }}</p>
                        </div>
                    </div>
                    <span class="px-3 py-1 bg-white bg-opacity-20 rounded-full text-sm">
                        {{ $student->class->name ?? 'No Class' }}
                    </span>
                </div>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center">
                        <p class="text-xs text-gray-500">Average Score</p>
                        <p class="text-xl font-bold text-gray-800">{{ number_format($student->results->avg('score'), 1) }}%</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-500">Total Payments</p>
                        <p class="text-xl font-bold text-gray-800">${{ number_format($student->payments->where('status', 'paid')->sum('amount'), 2) }}</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('parent.results', $student) }}" 
                       class="flex-1 text-center px-3 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm">
                        View Results
                    </a>
                    <a href="{{ route('parent.pay', $student) }}" 
                       class="flex-1 text-center px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm">
                        Make Payment
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Recent Results -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Recent Results</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Exam Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Term</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentResults as $result)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">{{ $result->student->full_name }}</td>
                        <td class="px-6 py-4">{{ $result->subject->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 capitalize">{{ $result->exam_type }}</td>
                        <td class="px-6 py-4">{{ $result->score }}/{{ $result->max_score }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($result->grade == 'A+') bg-green-100 text-green-800
                                @elseif($result->grade == 'A') bg-green-100 text-green-800
                                @elseif($result->grade == 'B') bg-blue-100 text-blue-800
                                @elseif($result->grade == 'C') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $result->grade }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ $result->term }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No results available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Payment History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">{{ $payment->invoice_number }}</td>
                        <td class="px-6 py-4">{{ $payment->student->full_name }}</td>
                        <td class="px-6 py-4">${{ number_format($payment->amount, 2) }}</td>
                        <td class="px-6 py-4 capitalize">{{ str_replace('_', ' ', $payment->payment_type) }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full {{ $payment->status == 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ $payment->created_at->format('d M, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">No payment records found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection