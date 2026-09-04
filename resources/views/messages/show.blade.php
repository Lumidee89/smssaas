@extends('layouts.app')
@section('title', $conversation->subject)
@section('page_title', 'Parent conversation')

@section('content')
<div class="mx-auto max-w-5xl">
    <a href="{{ route('messages.index') }}" class="mb-4 inline-flex items-center gap-2 text-sm font-bold text-[#3A7B72]">← Back to messages</a>
    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px]">
        <section class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <header class="border-b border-slate-100 p-5 sm:p-6"><p class="text-xs font-bold uppercase tracking-wider text-[#3A7B72]">Conversation</p><h1 class="mt-2 text-2xl font-extrabold">{{ $conversation->subject }}</h1></header>
            <div class="max-h-[55vh] space-y-4 overflow-y-auto bg-slate-50/60 p-5 sm:p-6" id="messages">
                @foreach($conversation->messages as $message)
                    @php($mine = $message->sender_id === Auth::id())
                    <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] rounded-2xl px-4 py-3 {{ $mine ? 'rounded-br-md bg-[#06322C] text-white' : 'rounded-bl-md border border-slate-200 bg-white text-slate-800' }}">
                            <p class="text-xs font-bold {{ $mine ? 'text-[#7ED3C4]' : 'text-[#3A7B72]' }}">{{ $message->sender->name }}</p>
                            <p class="mt-1 whitespace-pre-wrap text-sm leading-6">{{ $message->body }}</p>
                            <time class="mt-2 block text-[10px] {{ $mine ? 'text-white/50' : 'text-slate-400' }}">{{ $message->created_at->format('M j, g:i A') }}</time>
                        </div>
                    </div>
                @endforeach
            </div>
            <form method="POST" action="{{ route('messages.reply', $conversation) }}" class="border-t border-slate-100 p-5 sm:p-6">
                @csrf
                <label for="reply" class="mb-2 block text-sm font-bold text-slate-700">Reply to parent</label>
                <textarea id="reply" name="body" required maxlength="5000" rows="4" placeholder="Write your response…" class="w-full rounded-2xl border border-slate-300 p-4">{{ old('body') }}</textarea>
                @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                <div class="mt-3 flex justify-end"><button class="rounded-xl bg-[#06322C] px-6 py-3 text-sm font-extrabold text-white">Send reply</button></div>
            </form>
        </section>
        <aside class="h-fit rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#e7f5f1] text-xl font-black text-[#06322C]">{{ strtoupper(substr($parent?->name ?? 'P', 0, 1)) }}</div>
            <h2 class="mt-4 font-extrabold">{{ $parent?->name ?? 'Parent' }}</h2>
            <p class="mt-1 break-all text-sm text-slate-500">{{ $parent?->email }}</p>
            <p class="text-sm text-slate-500">{{ $parent?->phone }}</p>
            <div class="my-5 h-px bg-slate-100"></div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Linked students</p>
            <div class="mt-3 space-y-2">@forelse($parent?->children ?? [] as $student)<div class="rounded-xl bg-slate-50 p-3"><p class="text-sm font-bold">{{ $student->full_name }}</p><p class="text-xs text-slate-400">{{ $student->admission_number }}</p></div>@empty<p class="text-sm text-slate-400">No linked students.</p>@endforelse</div>
        </aside>
    </div>
</div>
@push('scripts')<script>document.getElementById('messages')?.scrollTo(0, document.getElementById('messages').scrollHeight);</script>@endpush
@endsection
