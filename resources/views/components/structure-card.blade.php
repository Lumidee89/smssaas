@props(['title','code','meta'])
<article class="rounded-2xl bg-white p-5 shadow-sm"><div class="flex items-start justify-between gap-3"><strong>{{ $title }}</strong><span class="rounded-lg bg-[#E7F5F1] px-2 py-1 text-xs font-bold text-[#06322C]">{{ $code }}</span></div><p class="mt-4 text-sm text-slate-500">{{ $meta }}</p></article>
