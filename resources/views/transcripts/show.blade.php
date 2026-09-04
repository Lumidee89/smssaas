@extends('layouts.app')
@section('title','Transcript') @section('page_title','Academic transcript')
@section('content')
<div class="mx-auto max-w-5xl rounded-2xl bg-white p-8 shadow-sm">
 <div class="flex justify-between"><div><h2 class="text-3xl font-extrabold">{{ data_get($transcript->snapshot,'student.name') }}</h2><p class="text-slate-500">{{ data_get($transcript->snapshot,'student.admission_number') }} · CGPA {{ $transcript->cgpa }}</p></div><button onclick="window.print()" class="rounded-xl bg-[#06322C] px-5 py-3 font-bold text-white">Print transcript</button></div>
 @foreach(data_get($transcript->snapshot,'terms',[]) as $term)<section class="mt-8"><div class="flex justify-between border-b pb-2"><h3 class="font-bold">{{ $term['academic_year'] }} · {{ $term['term'] }}</h3><strong>GPA {{ $term['gpa'] }}</strong></div><table class="mt-3 w-full text-sm"><thead class="text-left text-slate-400"><tr><th>Course</th><th>Credits</th><th>Score</th><th>Grade</th></tr></thead><tbody>@foreach($term['subjects'] as $subject)<tr class="border-b"><td class="py-3">{{ $subject['code'] }} — {{ $subject['subject'] }}</td><td>{{ $subject['credit_hours'] }}</td><td>{{ $subject['percentage'] }}%</td><td>{{ $subject['letter_grade'] }}</td></tr>@endforeach</tbody></table></section>@endforeach
 <div class="mt-8 flex items-center gap-4"><div class="h-40 w-40">{!! $qrCode !!}</div><p class="break-all text-xs text-slate-500">Scan to verify this transcript.<br>{{ route('transcripts.verify',$transcript->public_id) }}</p></div>
</div>
@endsection
