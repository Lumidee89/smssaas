@extends('layouts.app')
@section('title', 'Parent messages')
@section('page_title', 'Communication')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.2em] text-[#3A7B72]">Parent support</p>
            <h1 class="mt-2 text-3xl font-extrabold text-slate-900">Messages</h1>
            <p class="mt-1 text-sm text-slate-500">Read and respond to conversations started by parents.</p>
        </div>
        <a href="{{ route('announcements.index') }}" class="inline-flex items-center justify-center rounded-xl border border-[#3A7B72] px-5 py-3 text-sm font-bold text-[#06322C] hover:bg-[#e7f5f1]">Manage announcements</a>
    </div>

    <section class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
        @forelse($conversations as $conversation)
            @php
                $parent = $conversation->participants->firstWhere('role', 'parent');
                $lastMessage = $conversation->messages->first();
                $unread = $lastMessage && (!$conversation->pivot->last_read_at || $lastMessage->created_at->gt($conversation->pivot->last_read_at));
            @endphp
            <a href="{{ route('messages.show', $conversation) }}" class="flex gap-4 border-b border-slate-100 p-5 transition last:border-0 hover:bg-slate-50 sm:p-6">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $unread ? 'bg-[#06322C] text-white' : 'bg-[#e7f5f1] text-[#06322C]' }} font-extrabold">
                    {{ strtoupper(substr($parent?->name ?? 'P', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0"><h2 class="truncate font-extrabold text-slate-900">{{ $conversation->subject }}</h2><p class="mt-1 text-sm font-semibold text-[#3A7B72]">{{ $parent?->name ?? 'Parent' }}</p></div>
                        <div class="shrink-0 text-right">@if($unread)<span class="rounded-full bg-[#7ED3C4]/30 px-2 py-1 text-[10px] font-extrabold text-[#06322C]">NEW</span>@endif<time class="mt-1 block text-xs text-slate-400">{{ $conversation->last_message_at?->diffForHumans() }}</time></div>
                    </div>
                    <p class="mt-2 truncate text-sm text-slate-500">{{ $lastMessage?->body ?? 'No messages yet.' }}</p>
                </div>
            </a>
        @empty
            <div class="px-6 py-20 text-center"><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#e7f5f1] text-2xl text-[#06322C]">✉</div><h2 class="mt-5 text-lg font-extrabold">No parent messages yet</h2><p class="mt-1 text-sm text-slate-500">New conversations from the parent app will appear here.</p></div>
        @endforelse
    </section>
    {{ $conversations->links() }}
</div>
@endsection
