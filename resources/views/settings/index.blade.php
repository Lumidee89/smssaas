@extends('layouts.app')
@section('title', 'Settings')
@section('page_title', 'Settings')

@section('content')
@php
    $schoolTabs = $school ? ['school', 'appearance'] : [];
    $initialTab = $errors->has('current_password') || $errors->has('password')
        ? 'security'
        : ($errors->has('school_name') ? 'school' : 'profile');
    $roleLabel = str($user->role)->replace('_', ' ')->title();
    $initials = collect(explode(' ', $user->name))->filter()->take(2)->map(fn ($part) => strtoupper(substr($part, 0, 1)))->join('');
@endphp

<div class="mx-auto max-w-7xl space-y-7" x-data="{ active: '{{ $initialTab }}', color: '{{ $school?->theme_color ?: '#06322C' }}' }">
    <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[.2em] text-[#3A7B72]">Workspace preferences</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Settings</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Manage your personal account, security, school information and workspace appearance.</p>
        </div>
        <div class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-white px-4 py-3 shadow-sm">
            <span class="grid h-11 w-11 place-items-center rounded-xl bg-[#06322C] text-sm font-black text-[#7ED3C4]">{{ $initials ?: 'SO' }}</span>
            <div><strong class="block text-sm text-slate-900">{{ $user->name }}</strong><span class="text-xs text-slate-400">{{ $roleLabel }}</span></div>
            <span class="ml-2 h-2.5 w-2.5 rounded-full bg-emerald-500" title="Account active"></span>
        </div>
    </header>

    @if($errors->any())
        <div class="flex gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-red-100 font-black">!</span>
            <div><strong class="block">Please check the highlighted information.</strong><span>{{ $errors->first() }}</span></div>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[260px_minmax(0,1fr)]">
        <aside class="h-fit rounded-3xl border border-slate-100 bg-white p-3 shadow-sm lg:sticky lg:top-6">
            <nav class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-1" aria-label="Settings sections">
                <button type="button" @click="active='profile'" :class="active==='profile' ? 'bg-[#e7f5f1] text-[#06322C]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'" class="flex items-center gap-3 rounded-2xl px-4 py-3.5 text-left text-sm font-bold transition">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-base shadow-sm">◎</span><span>My profile<small class="hidden font-normal text-slate-400 lg:block">Personal details</small></span>
                </button>
                <button type="button" @click="active='security'" :class="active==='security' ? 'bg-[#e7f5f1] text-[#06322C]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'" class="flex items-center gap-3 rounded-2xl px-4 py-3.5 text-left text-sm font-bold transition">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-base shadow-sm">◇</span><span>Security<small class="hidden font-normal text-slate-400 lg:block">Password access</small></span>
                </button>
                @if($school)
                    <button type="button" @click="active='school'" :class="active==='school' ? 'bg-[#e7f5f1] text-[#06322C]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'" class="flex items-center gap-3 rounded-2xl px-4 py-3.5 text-left text-sm font-bold transition">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-base shadow-sm">⌂</span><span>School<small class="hidden font-normal text-slate-400 lg:block">Institution details</small></span>
                    </button>
                    <button type="button" @click="active='appearance'" :class="active==='appearance' ? 'bg-[#e7f5f1] text-[#06322C]' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'" class="flex items-center gap-3 rounded-2xl px-4 py-3.5 text-left text-sm font-bold transition">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-white text-base shadow-sm">◐</span><span>Appearance<small class="hidden font-normal text-slate-400 lg:block">Brand color</small></span>
                    </button>
                @endif
            </nav>
            <div class="mx-2 mt-3 hidden rounded-2xl bg-[#06322C] p-4 text-white lg:block">
                <p class="text-xs font-bold text-[#7ED3C4]">SchoolOS tip</p>
                <p class="mt-2 text-xs leading-5 text-white/70">Keep your contact information current so important account messages reach you.</p>
            </div>
        </aside>

        <main class="min-w-0">
            <section x-show="active==='profile'" x-transition.opacity class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-6 sm:p-8">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#e7f5f1] text-xl text-[#06322C]">◎</span>
                    <h2 class="mt-5 text-2xl font-extrabold text-slate-900">Personal profile</h2>
                    <p class="mt-1 text-sm text-slate-500">The details associated with your SchoolOS account.</p>
                </div>
                <form action="{{ route('settings.profile') }}" method="POST" class="p-6 sm:p-8">@csrf
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block"><span class="mb-2 block text-sm font-bold text-slate-700">Full name</span><input type="text" name="name" required value="{{ old('name', $user->name) }}" class="w-full rounded-xl px-4 py-3.5" placeholder="Your full name">@error('name')<small class="mt-1 block text-red-600">{{ $message }}</small>@enderror</label>
                        <label class="block"><span class="mb-2 block text-sm font-bold text-slate-700">Email address</span><input type="email" name="email" required value="{{ old('email', $user->email) }}" class="w-full rounded-xl px-4 py-3.5" placeholder="name@school.com">@error('email')<small class="mt-1 block text-red-600">{{ $message }}</small>@enderror</label>
                        <label class="block"><span class="mb-2 block text-sm font-bold text-slate-700">Phone number</span><input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-xl px-4 py-3.5" placeholder="+234 800 000 0000"></label>
                        <label class="block"><span class="mb-2 block text-sm font-bold text-slate-700">Account role</span><div class="flex h-[54px] items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4"><span class="font-semibold text-slate-600">{{ $roleLabel }}</span><span class="rounded-full bg-white px-3 py-1 text-[10px] font-black uppercase tracking-wider text-[#3A7B72]">Managed</span></div></label>
                    </div>
                    <div class="mt-7 flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-center sm:justify-between"><p class="text-xs text-slate-400">Your role can only be changed by an administrator.</p><button type="submit" class="rounded-xl bg-[#06322C] px-6 py-3.5 text-sm font-extrabold text-white transition hover:bg-[#08423B]">Save profile</button></div>
                </form>
            </section>

            <section x-show="active==='security'" x-cloak x-transition.opacity class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-6 sm:p-8"><span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#e7f5f1] text-xl text-[#06322C]">◇</span><h2 class="mt-5 text-2xl font-extrabold">Password & security</h2><p class="mt-1 text-sm text-slate-500">Choose a strong password you do not use elsewhere.</p></div>
                <form action="{{ route('settings.password') }}" method="POST" class="p-6 sm:p-8">@csrf
                    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
                        <div class="space-y-5">
                            <label class="block"><span class="mb-2 block text-sm font-bold text-slate-700">Current password</span><input type="password" name="current_password" required autocomplete="current-password" class="w-full rounded-xl px-4 py-3.5">@error('current_password')<small class="mt-1 block text-red-600">{{ $message }}</small>@enderror</label>
                            <div class="grid gap-5 sm:grid-cols-2"><label><span class="mb-2 block text-sm font-bold text-slate-700">New password</span><input type="password" name="password" required minlength="8" autocomplete="new-password" class="w-full rounded-xl px-4 py-3.5">@error('password')<small class="mt-1 block text-red-600">{{ $message }}</small>@enderror</label><label><span class="mb-2 block text-sm font-bold text-slate-700">Confirm password</span><input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="w-full rounded-xl px-4 py-3.5"></label></div>
                        </div>
                        <aside class="rounded-2xl bg-slate-50 p-5"><p class="text-sm font-extrabold text-slate-800">A strong password has:</p><ul class="mt-4 space-y-3 text-xs text-slate-500"><li class="flex gap-2"><b class="text-[#3A7B72]">✓</b> At least 8 characters</li><li class="flex gap-2"><b class="text-[#3A7B72]">✓</b> A mix of words and symbols</li><li class="flex gap-2"><b class="text-[#3A7B72]">✓</b> No reused personal information</li></ul></aside>
                    </div>
                    <div class="mt-7 flex justify-end border-t border-slate-100 pt-6"><button type="submit" class="rounded-xl bg-[#06322C] px-6 py-3.5 text-sm font-extrabold text-white hover:bg-[#08423B]">Update password</button></div>
                </form>
            </section>

            @if($school)
            <section x-show="active==='school'" x-cloak x-transition.opacity class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-6 sm:p-8"><div class="flex flex-col gap-5 sm:flex-row sm:items-center"><span class="grid h-14 w-14 place-items-center rounded-2xl bg-[#06322C] text-xl font-black text-[#7ED3C4]">{{ strtoupper(substr($school->name, 0, 1)) }}</span><div><h2 class="text-2xl font-extrabold">School information</h2><p class="mt-1 text-sm text-slate-500">Public and operational details for your institution.</p></div></div></div>
                <form action="{{ route('settings.update') }}" method="POST" class="p-6 sm:p-8">@csrf @method('PUT')
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="block"><span class="mb-2 block text-sm font-bold text-slate-700">School name</span><input type="text" name="school_name" required value="{{ old('school_name', $school->name) }}" class="w-full rounded-xl px-4 py-3.5">@error('school_name')<small class="mt-1 block text-red-600">{{ $message }}</small>@enderror</label>
                        <label class="block"><span class="mb-2 block text-sm font-bold text-slate-700">Workspace address</span><div class="flex h-[54px] items-center rounded-xl border border-slate-200 bg-slate-50 px-4"><span class="min-w-0 truncate font-semibold text-slate-600">{{ $school->subdomain }}.plus36networks.com</span><span class="ml-auto rounded-full bg-white px-3 py-1 text-[10px] font-black text-slate-400">LOCKED</span></div></label>
                        <label class="block"><span class="mb-2 block text-sm font-bold text-slate-700">School email</span><input type="email" name="email" required value="{{ old('email', $school->email) }}" class="w-full rounded-xl px-4 py-3.5"></label>
                        <label class="block"><span class="mb-2 block text-sm font-bold text-slate-700">School phone</span><input type="tel" name="phone" required value="{{ old('phone', $school->phone) }}" class="w-full rounded-xl px-4 py-3.5"></label>
                        <label class="block md:col-span-2"><span class="mb-2 block text-sm font-bold text-slate-700">School address</span><textarea name="address" required rows="4" class="w-full rounded-xl px-4 py-3.5">{{ old('address', $school->address) }}</textarea></label>
                    </div>
                    <div class="mt-7 flex justify-end border-t border-slate-100 pt-6"><button type="submit" class="rounded-xl bg-[#06322C] px-6 py-3.5 text-sm font-extrabold text-white hover:bg-[#08423B]">Save school details</button></div>
                </form>
            </section>

            <section x-show="active==='appearance'" x-cloak x-transition.opacity class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-6 sm:p-8"><span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#e7f5f1] text-xl text-[#06322C]">◐</span><h2 class="mt-5 text-2xl font-extrabold">Workspace appearance</h2><p class="mt-1 text-sm text-slate-500">Choose the accent used for your school identity.</p></div>
                <form action="{{ route('settings.theme') }}" method="POST" class="p-6 sm:p-8">@csrf
                    <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_340px]">
                        <div><p class="text-sm font-bold text-slate-700">Brand color</p><p class="mt-1 text-xs text-slate-400">Select a preset or choose a custom color.</p>
                            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                @foreach([['#06322C','SchoolOS'],['#3A7B72','Teal'],['#2563EB','Blue'],['#7C3AED','Violet'],['#DC2626','Crimson']] as [$color, $name])
                                    <label class="cursor-pointer rounded-2xl border border-slate-200 p-3 transition has-[:checked]:border-[#3A7B72] has-[:checked]:bg-[#e7f5f1]"><input type="radio" name="theme_color" value="{{ $color }}" class="sr-only" @checked(strtoupper($school->theme_color) === $color) @change="color='{{ $color }}'"><span class="flex items-center gap-3"><i class="h-8 w-8 rounded-xl shadow-inner" style="background:{{ $color }}"></i><b class="text-xs text-slate-700">{{ $name }}</b></span></label>
                                @endforeach
                            </div>
                            <label class="mt-5 block"><span class="mb-2 block text-sm font-bold text-slate-700">Custom color</span><div class="flex items-center gap-3"><input type="color" name="theme_color" x-model="color" class="h-12 w-16 cursor-pointer rounded-xl border border-slate-200 bg-white p-1"><input type="text" x-model="color" pattern="#[0-9a-fA-F]{6}" class="w-36 rounded-xl px-4 py-3 font-mono text-sm uppercase"></div></label>
                        </div>
                        <aside class="overflow-hidden rounded-3xl border border-slate-200 bg-slate-50"><div class="p-5 text-white transition" :style="`background:${color}`"><div class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-xl bg-white/15 font-black">{{ strtoupper(substr($school->name, 0, 1)) }}</span><div><strong class="block text-sm">{{ $school->name }}</strong><small class="text-white/70">Workspace preview</small></div></div><p class="mt-8 text-xs text-white/70">Today’s overview</p><strong class="mt-1 block text-2xl">Welcome back.</strong></div><div class="grid grid-cols-2 gap-3 p-5"><div class="rounded-xl bg-white p-3"><small class="text-slate-400">Students</small><strong class="mt-1 block text-lg text-slate-800">1,248</strong></div><div class="rounded-xl bg-white p-3"><small class="text-slate-400">Attendance</small><strong class="mt-1 block text-lg" :style="`color:${color}`">94%</strong></div></div></aside>
                    </div>
                    <div class="mt-7 flex justify-end border-t border-slate-100 pt-6"><button type="submit" class="rounded-xl bg-[#06322C] px-6 py-3.5 text-sm font-extrabold text-white hover:bg-[#08423B]">Save appearance</button></div>
                </form>
            </section>
            @endif
        </main>
    </div>
</div>
@endsection
