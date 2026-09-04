<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $exam->title }} | {{ $exam->school->name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>[x-cloak]{display:none!important}*{font-family:Inter,sans-serif}</style>
</head>
<body class="min-h-screen bg-[#f4f7f6] text-slate-900" x-data="cbtPortal()" x-init="init()">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <div class="flex min-w-0 items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="Plus36 SchoolOS" class="h-11 w-auto object-contain">
                <div class="hidden h-8 w-px bg-slate-200 sm:block"></div>
                <div class="hidden min-w-0 sm:block"><p class="truncate text-sm font-extrabold">{{ $exam->school->name }}</p><p class="text-xs text-slate-500">Student examination portal</p></div>
            </div>
            <div x-show="screen === 'exam'" x-cloak class="rounded-xl bg-[#06322C] px-4 py-2 text-center text-white">
                <p class="text-[10px] font-bold uppercase tracking-wider text-[#7ED3C4]">Time left</p>
                <p class="font-mono text-lg font-black" x-text="clock"></p>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl p-4 py-8 sm:p-6 lg:py-12">
        <div x-show="error" x-cloak class="mx-auto mb-5 max-w-3xl rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700" x-text="error"></div>

        <section x-show="screen === 'login'" class="mx-auto grid max-w-5xl overflow-hidden rounded-[2rem] bg-white shadow-xl shadow-[#06322C]/10 lg:grid-cols-[.9fr_1.1fr]">
            <aside class="bg-[#06322C] p-7 text-white sm:p-10">
                <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-[#7ED3C4]">{{ strtoupper($exam->status) }}</span>
                <h1 class="mt-6 text-3xl font-black leading-tight sm:text-4xl">{{ $exam->title }}</h1>
                <p class="mt-3 text-slate-300">{{ $exam->subject->name }} · {{ $exam->schoolClass->full_name }}</p>
                <dl class="mt-8 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-white/10 p-4"><dt class="text-xs text-slate-300">Duration</dt><dd class="mt-1 font-extrabold">{{ $exam->duration_minutes }} minutes</dd></div>
                    <div class="rounded-2xl bg-white/10 p-4"><dt class="text-xs text-slate-300">Questions</dt><dd class="mt-1 font-extrabold">{{ $exam->questions_count }}</dd></div>
                    <div class="rounded-2xl bg-white/10 p-4"><dt class="text-xs text-slate-300">Opens</dt><dd class="mt-1 text-sm font-bold">{{ $exam->opens_at->format('M j, g:i A') }}</dd></div>
                    <div class="rounded-2xl bg-white/10 p-4"><dt class="text-xs text-slate-300">Closes</dt><dd class="mt-1 text-sm font-bold">{{ $exam->closes_at->format('M j, g:i A') }}</dd></div>
                </dl>
            </aside>
            <div class="p-7 sm:p-10">
                <p class="text-xs font-bold uppercase tracking-[.2em] text-[#3A7B72]">Candidate access</p>
                <h2 class="mt-3 text-2xl font-black">Enter your details</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Use the admission number registered by your school. Your timer starts after successful verification.</p>
                <form @submit.prevent="start" class="mt-8 space-y-5">
                    <div><label for="admission" class="mb-2 block text-sm font-bold">Admission number</label><input id="admission" x-model.trim="admissionNumber" required autocomplete="username" placeholder="e.g. SCH/2026/001" class="h-13 w-full rounded-2xl border border-slate-300 px-4 py-3 focus:border-[#3A7B72] focus:outline-none focus:ring-4 focus:ring-[#7ED3C4]/20"></div>
                    @if($exam->access_code_hash)
                        <div><label for="access-code" class="mb-2 block text-sm font-bold">Exam access code</label><input id="access-code" x-model="accessCode" required autocomplete="one-time-code" placeholder="Enter the code from your teacher" class="h-13 w-full rounded-2xl border border-slate-300 px-4 py-3 focus:border-[#3A7B72] focus:outline-none focus:ring-4 focus:ring-[#7ED3C4]/20"></div>
                    @endif
                    <label class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4 text-sm text-slate-600"><input x-model="accepted" type="checkbox" required class="mt-1 h-4 w-4 accent-[#06322C]"><span>I am ready to begin and understand that the examination timer cannot be paused.</span></label>
                    <button :disabled="busy || !accepted" class="flex w-full items-center justify-center rounded-2xl bg-[#06322C] px-5 py-4 font-extrabold text-white disabled:opacity-50"><span x-show="!busy">Start examination →</span><span x-show="busy">Verifying…</span></button>
                </form>
            </div>
        </section>

        <section x-show="screen === 'exam'" x-cloak class="grid gap-6 lg:grid-cols-[240px_minmax(0,1fr)]">
            <aside class="h-fit rounded-3xl bg-white p-5 shadow-sm lg:sticky lg:top-5">
                <div class="flex items-center justify-between"><h2 class="font-extrabold">Questions</h2><span class="text-xs font-bold text-[#3A7B72]" x-text="`${answeredCount}/${questions.length} answered`"></span></div>
                <div class="mt-4 grid grid-cols-6 gap-2 lg:grid-cols-4">
                    <template x-for="(question, index) in questions" :key="question.id"><button @click="current=index" class="aspect-square rounded-xl text-xs font-bold transition" :class="current===index ? 'bg-[#06322C] text-white' : hasAnswer(question) ? 'bg-[#e7f5f1] text-[#06322C]' : 'bg-slate-100 text-slate-500'" x-text="index+1"></button></template>
                </div>
                <button @click="confirmSubmit=true" class="mt-5 w-full rounded-xl border border-[#06322C] px-4 py-3 text-sm font-extrabold text-[#06322C]">Submit exam</button>
            </aside>

            <div class="min-w-0 rounded-3xl bg-white p-5 shadow-sm sm:p-8" x-show="questions.length">
                <div class="flex items-start justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-wider text-[#3A7B72]" x-text="`Question ${current+1} of ${questions.length}`"></p><p class="mt-1 text-xs text-slate-400" x-text="`${activeQuestion?.points || 0} point(s)`"></p></div><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500" x-text="typeLabel(activeQuestion?.type)"></span></div>
                <h2 class="mt-7 whitespace-pre-wrap text-lg font-extrabold leading-8 sm:text-xl" x-text="activeQuestion?.prompt"></h2>

                <div class="mt-7 space-y-3" x-show="activeQuestion?.type === 'single_choice' || activeQuestion?.type === 'true_false'">
                    <template x-for="option in optionsFor(activeQuestion)" :key="option"><label class="flex cursor-pointer items-center gap-3 rounded-2xl border p-4 transition" :class="isSelected(option) ? 'border-[#3A7B72] bg-[#e7f5f1]' : 'border-slate-200 hover:border-slate-300'"><input type="radio" :name="`question-${activeQuestion.id}`" :value="option" :checked="isSelected(option)" @change="setSingle(option)" class="h-4 w-4 accent-[#06322C]"><span class="font-semibold" x-text="option"></span></label></template>
                </div>
                <div class="mt-7 space-y-3" x-show="activeQuestion?.type === 'multiple_choice'">
                    <template x-for="option in optionsFor(activeQuestion)" :key="option"><label class="flex cursor-pointer items-center gap-3 rounded-2xl border p-4 transition" :class="isSelected(option) ? 'border-[#3A7B72] bg-[#e7f5f1]' : 'border-slate-200 hover:border-slate-300'"><input type="checkbox" :value="option" :checked="isSelected(option)" @change="toggleMultiple(option)" class="h-4 w-4 accent-[#06322C]"><span class="font-semibold" x-text="option"></span></label></template>
                </div>
                <textarea x-show="activeQuestion?.type === 'short_text'" :value="answerFor(activeQuestion)[0] || ''" @input.debounce.600ms="setText($event.target.value)" rows="7" placeholder="Type your answer here…" class="mt-7 w-full rounded-2xl border border-slate-300 p-4 focus:border-[#3A7B72] focus:outline-none focus:ring-4 focus:ring-[#7ED3C4]/20"></textarea>

                <div class="mt-9 flex items-center justify-between gap-3 border-t border-slate-100 pt-6">
                    <button @click="current--" :disabled="current===0" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold disabled:opacity-30">← Previous</button>
                    <span class="text-xs font-semibold" :class="saving ? 'text-amber-600' : 'text-[#3A7B72]'" x-text="saving ? 'Saving…' : 'Answers saved'"></span>
                    <button x-show="current < questions.length-1" @click="current++" class="rounded-xl bg-[#06322C] px-5 py-3 text-sm font-bold text-white">Next →</button>
                    <button x-show="current === questions.length-1" @click="confirmSubmit=true" class="rounded-xl bg-[#06322C] px-5 py-3 text-sm font-bold text-white">Finish</button>
                </div>
            </div>
        </section>

        <section x-show="screen === 'result'" x-cloak class="mx-auto max-w-2xl rounded-[2rem] bg-white p-8 text-center shadow-xl sm:p-12">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-[#e7f5f1] text-4xl text-[#06322C]">✓</div>
            <p class="mt-6 text-xs font-bold uppercase tracking-[.2em] text-[#3A7B72]">Exam submitted</p>
            <h1 class="mt-3 text-3xl font-black" x-text="result?.status === 'pending_review' ? 'Pending teacher review' : 'Submission complete'"></h1>
            <template x-if="result?.status === 'graded'"><div class="mt-7 rounded-3xl bg-[#06322C] p-7 text-white"><p class="text-sm text-[#7ED3C4]">Your score</p><p class="mt-1 text-4xl font-black" x-text="`${result.percentage}%`"></p><p class="mt-2 text-sm text-white/70" x-text="`${result.score} out of ${result.maximum_score} points`"></p></div></template>
            <p class="mt-7 text-sm leading-6 text-slate-500">Your answers have been securely recorded. You may now close this page.</p>
        </section>
    </main>

    <div x-show="confirmSubmit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4">
        <div @click.outside="confirmSubmit=false" class="w-full max-w-md rounded-3xl bg-white p-7 shadow-2xl"><h2 class="text-xl font-black">Submit examination?</h2><p class="mt-2 text-sm leading-6 text-slate-500">You answered <strong x-text="answeredCount"></strong> of <strong x-text="questions.length"></strong> questions. You cannot change answers after submission.</p><div class="mt-6 flex gap-3"><button @click="confirmSubmit=false" class="flex-1 rounded-xl border border-slate-300 py-3 font-bold">Continue exam</button><button @click="submitExam" :disabled="busy" class="flex-1 rounded-xl bg-[#06322C] py-3 font-bold text-white disabled:opacity-50">Submit now</button></div></div>
    </div>

    <script>
        function cbtPortal() {
            return {
                examId: {{ $exam->id }}, screen: 'login', admissionNumber: '', accessCode: '', accepted: false,
                busy: false, saving: false, error: '', token: '', attempt: null, questions: [], current: 0,
                secondsLeft: 0, clock: '00:00', timer: null, confirmSubmit: false, result: null,
                async init() {
                    this.token = sessionStorage.getItem(`cbt-token-${this.examId}`) || '';
                    if (this.token) await this.loadAttempt();
                },
                async request(path, options = {}) {
                    const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json', ...(options.headers || {}) };
                    if (this.token) headers.Authorization = `Bearer ${this.token}`;
                    const response = await fetch(path, { ...options, headers });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) throw new Error(payload.message || Object.values(payload.errors || {})[0]?.[0] || 'Something went wrong.');
                    return payload.data;
                },
                async start() {
                    this.busy = true; this.error = '';
                    try {
                        const data = await this.request(`/api/v1/cbt/exams/${this.examId}/start`, { method: 'POST', body: JSON.stringify({ admission_number: this.admissionNumber, access_code: this.accessCode || null }) });
                        this.token = data.attempt_token; sessionStorage.setItem(`cbt-token-${this.examId}`, this.token); this.useAttempt(data);
                    } catch (error) { this.error = error.message; } finally { this.busy = false; }
                },
                async loadAttempt() {
                    try { this.useAttempt(await this.request('/api/v1/cbt/attempt')); } catch (error) { sessionStorage.removeItem(`cbt-token-${this.examId}`); this.token = ''; this.error = error.message; }
                },
                useAttempt(data) {
                    this.attempt = data; this.questions = data.questions || [];
                    if (data.status !== 'in_progress') { this.result = data; this.screen = 'result'; return; }
                    this.screen = 'exam'; this.startTimer(data.expires_at, data.server_time);
                },
                startTimer(expiresAt, serverTime) {
                    clearInterval(this.timer); this.secondsLeft = Math.max(0, Math.floor((new Date(expiresAt) - new Date(serverTime)) / 1000)); this.updateClock();
                    this.timer = setInterval(() => { this.secondsLeft--; this.updateClock(); if (this.secondsLeft <= 0) { clearInterval(this.timer); this.submitExam(); } }, 1000);
                },
                updateClock() { const value = Math.max(0, this.secondsLeft); this.clock = `${String(Math.floor(value / 60)).padStart(2,'0')}:${String(value % 60).padStart(2,'0')}`; },
                get activeQuestion() { return this.questions[this.current]; },
                get answeredCount() { return this.questions.filter(question => this.hasAnswer(question)).length; },
                optionsFor(question) { return question?.type === 'true_false' ? ['true', 'false'] : (question?.options || []); },
                answerFor(question) { return Array.isArray(question?.answer) ? question.answer : []; },
                hasAnswer(question) { return this.answerFor(question).some(value => String(value).trim() !== ''); },
                isSelected(option) { return this.answerFor(this.activeQuestion).includes(option); },
                typeLabel(type) { return ({ single_choice: 'Single choice', multiple_choice: 'Multiple choice', true_false: 'True / false', short_text: 'Written answer' })[type] || ''; },
                setSingle(option) { this.activeQuestion.answer = [option]; this.save(this.activeQuestion); },
                toggleMultiple(option) { const values = [...this.answerFor(this.activeQuestion)]; const index = values.indexOf(option); index >= 0 ? values.splice(index, 1) : values.push(option); this.activeQuestion.answer = values; this.save(this.activeQuestion); },
                setText(value) { this.activeQuestion.answer = value.trim() ? [value] : []; this.save(this.activeQuestion); },
                async save(question) { this.saving = true; this.error = ''; try { await this.request(`/api/v1/cbt/attempt/questions/${question.id}`, { method: 'PUT', body: JSON.stringify({ answer: question.answer || [] }) }); } catch (error) { this.error = `Answer not saved: ${error.message}`; } finally { this.saving = false; } },
                async submitExam() { if (this.busy || this.screen !== 'exam') return; this.busy = true; this.confirmSubmit = false; this.error = ''; try { this.result = await this.request('/api/v1/cbt/attempt/submit', { method: 'POST' }); clearInterval(this.timer); sessionStorage.removeItem(`cbt-token-${this.examId}`); this.screen = 'result'; window.scrollTo({ top: 0 }); } catch (error) { this.error = error.message; } finally { this.busy = false; } },
            };
        }
    </script>
</body>
</html>
