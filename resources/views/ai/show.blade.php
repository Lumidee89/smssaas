@extends('layouts.app')
@section('title','Copilot result')
@section('page_title','Copilot result')
@section('content')
<div class="mx-auto max-w-4xl space-y-5"><div class="flex items-start justify-between"><div><h2 class="text-3xl font-extrabold text-[#06322C]">{{ ucfirst(str_replace('_',' ',$generation->type)) }}</h2><p class="text-slate-500">Requested {{ $generation->created_at->diffForHumans() }}</p></div><span class="rounded-full bg-slate-100 px-4 py-2 text-sm font-bold">{{ ucfirst($generation->status) }}</span></div>
 @if($generation->status==='completed')<div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800"><strong>Educator review required.</strong> Verify accuracy, curriculum alignment, bias, and age suitability before using this draft.</div><div class="rounded-2xl bg-white p-7 shadow-sm"><pre class="whitespace-pre-wrap font-sans text-sm leading-7 text-slate-700">{{ json_encode($generation->output,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) }}</pre></div><p class="text-xs text-slate-400">Provider: {{ $generation->provider }} · Model: {{ $generation->model }} · Tokens: {{ $generation->input_tokens??'—' }} in / {{ $generation->output_tokens??'—' }} out</p>
 @elseif($generation->status==='failed')<div class="rounded-2xl bg-red-50 p-6 text-red-700"><strong>Generation failed.</strong><p class="mt-2">{{ $generation->error }}</p></div>
 @else<div class="rounded-2xl bg-white p-12 text-center shadow-sm"><div class="mx-auto h-9 w-9 animate-spin rounded-full border-4 border border-slate-300 border-t-[#06322C]"></div><p class="mt-4 text-slate-500">Your request is {{ $generation->status }}. Refresh shortly.</p></div>@endif
 <a href="{{ route('ai.index') }}" class="inline-block font-bold text-[#3A7B72]">← Back to Copilot</a></div>
@endsection
