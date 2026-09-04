@php
$nav = [
 ['Dashboard','dashboard','dashboard','M4 6h6v6H4V6zm10 0h6v4h-6V6zM4 16h6v4H4v-4zm10-2h6v6h-6v-6z'],
 ['Students','students.index','students.*','M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m7-10a4 4 0 100-8 4 4 0 000 8zm13 10v-2a4 4 0 00-3-3.87m-1-12a4 4 0 010 7.75'],
 ['Teachers','teachers.index','teachers.*','M12 14l9-5-9-5-9 5 9 5zm0 0l6-3.33V16l-6 4-6-4v-5.33L12 14z'],
 ['Parents','parents.index','parents.*','M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m7-10a4 4 0 100-8 4 4 0 000 8z'],
 ['Classes','classes.index','classes.*','M3 21h18M5 21V5l7-3 7 3v16M9 9h.01M9 13h.01M15 9h.01M15 13h.01'],
 ['Subjects','subjects.index','subjects.*','M4 19.5A2.5 2.5 0 016.5 17H20V5H6.5A2.5 2.5 0 004 7.5v12zm0 0A2.5 2.5 0 006.5 22H20v-5'],
 ['Examinations','results.index','results.*','M9 17v-4m3 4V9m3 8v-6m4 10H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2z'],
 ['Finance','payments.index','payments.*','M12 8c-2 0-3 .9-3 2s1 2 3 2 3 .9 3 2-1 2-3 2m0-8V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z'],
 ['Reports','reports.index','reports.*','M9 17v-2m3 2v-5m3 5V8m4 13H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2z'],
];
if (Auth::user()->role === 'super_admin') {
    $nav = [
      ['Overview','platform.index','platform.index','M4 6h6v6H4V6zm10 0h6v4h-6V6zM4 16h6v4H4v-4zm10-2h6v6h-6v-6z'],
      ['Schools','platform.schools.index','platform.schools.*','M3 21h18M5 21V5l7-3 7 3v16M9 9h.01M15 9h.01M9 14h.01M15 14h.01'],
      ['Platform users','platform.users.index','platform.users.*','M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m7-10a4 4 0 100-8 4 4 0 000 8z'],
      ['Product payments','platform.payments.index','platform.payments.*','M12 8c-2 0-3 .9-3 2s1 2 3 2 3 .9 3 2-1 2-3 2m0-8V6m0 12v-2'],
    ];
}
if (Auth::user()->school_id && in_array(Auth::user()->role, ['school_admin','principal','vice_principal','academic_admin','teacher'], true)) {
    array_splice($nav, 4, 0, [
        ['Attendance','attendance.index','attendance.*','M9 11l3 3L22 4M21 12a9 9 0 11-5.27-8.2'],
        ['Communication','announcements.index','announcements.*','M8 10h8M8 14h5m8-2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Assessments','assessments.index','assessments.*','M9 12l2 2 4-4m5-6H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2z'],
        ['Planning','planning.index','planning.*','M8 7V3m8 4V3M5 11h14M6 5h12a2 2 0 012 2v12H4V7a2 2 0 012-2z'],
        ['CBT','cbt.index','cbt.*','M9 9h6m-6 4h6m-6 4h3M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z'],
        ['AI Assistant','ai.index','ai.*','M12 3l1.8 4.2L18 9l-4.2 1.8L12 15l-1.8-4.2L6 9l4.2-1.8L12 3zm6 12l.8 1.8L21 18l-2.2 1.2L18 21l-.8-1.8L15 18l2.2-1.2L18 15z'],
    ]);
}
if (Auth::user()->school_id && in_array(Auth::user()->role, ['school_admin','principal','vice_principal','academic_admin'], true)) {
    foreach ($nav as &$item) {
        if ($item[0] === 'Communication') $item = ['Communication','messages.index','messages.*','M8 10h8M8 14h5m8-2a9 9 0 11-18 0 9 9 0 0118 0z'];
    }
    unset($item);
    array_splice($nav, 6, 0, [
        ['Academic Setup','academic-setup.index','academic-setup.*','M12 6v6l4 2m5-2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Institution Structure','academic-structure.index','academic-structure.*','M3 21h18M5 21V5l7-3 7 3v16M9 9h.01M15 9h.01M9 14h.01M15 14h.01'],
    ]);
    if (in_array(Auth::user()->role, ['school_admin','principal','vice_principal'], true)) $nav[] = ['Analytics','analytics.index','analytics.*','M3 3v18h18M7 15l4-4 3 3 5-7'];
    if (in_array(Auth::user()->role, ['school_admin','principal'], true)) $nav[] = ['Campus Operations','campus.index','campus.*','M3 21h18M5 21V7l7-4 7 4v14M9 12h6M9 16h6'];
    foreach ($nav as &$item) {
        if ($item[0] === 'Finance') $item = ['Finance','finance.index','finance.*','M12 8c-2 0-3 .9-3 2s1 2 3 2 3 .9 3 2-1 2-3 2m0-8V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z'];
    }
    unset($item);
}
if (Auth::user()->role === 'bursar') {
    $nav = array_values(array_filter($nav, fn($item) => in_array($item[0], ['Dashboard','Finance','Reports'], true)));
    $nav[] = ['Communication','messages.index','messages.*','M8 10h8M8 14h5m8-2a9 9 0 11-18 0 9 9 0 0118 0z'];
    $nav[] = ['Campus Operations','campus.index','campus.*','M3 21h18M5 21V7l7-4 7 4v14M9 12h6M9 16h6'];
    foreach ($nav as &$item) if ($item[0] === 'Finance') $item = ['Finance','finance.index','finance.*','M12 8c-2 0-3 .9-3 2s1 2 3 2 3 .9 3 2-1 2-3 2m0-8V6m0 12v-2m9-4a9 9 0 11-18 0 9 9 0 0118 0z'];
    unset($item);
}
if (Auth::user()->role === 'teacher') $nav = array_values(array_filter($nav, fn($item) => !in_array($item[0], ['Students','Teachers','Parents','Finance','Reports'], true)));
if (Auth::user()->role === 'academic_admin') $nav = array_values(array_filter($nav, fn($item) => !in_array($item[0], ['Teachers','Finance'], true)));
if (Auth::user()->role === 'vice_principal') $nav = array_values(array_filter($nav, fn($item) => $item[0] !== 'Finance'));
if (Auth::user()->role === 'parent') $nav = array_values(array_filter($nav, fn($item) => in_array($item[0], ['Dashboard','Finance'], true)));
if (Auth::user()->school) {
    $featureRoutes = ['cbt.index'=>'cbt','analytics.index'=>'analytics','ai.index'=>'ai'];
    $nav = array_values(array_filter($nav, fn($item) => !isset($featureRoutes[$item[1]]) || app(\App\Services\Saas\EntitlementService::class)->allows(Auth::user()->school, $featureRoutes[$item[1]])));
}
@endphp
<aside class="w-72 h-screen bg-white border-r border-slate-100 flex flex-col p-5">
 <a href="{{ route('dashboard') }}" class="block px-3 py-2"><img src="{{ asset('images/logo.png') }}" alt="Plus36 SchoolOS" class="h-14 w-auto max-w-full object-contain object-left"><small class="mt-1 block truncate text-slate-400">{{ Auth::user()->school->name ?? 'Platform workspace' }}</small></a>
 <p class="mt-8 px-3 text-[10px] font-bold tracking-[.2em] text-slate-400">WORKSPACE</p>
 <nav class="mt-3 space-y-1 overflow-y-auto">@foreach($nav as [$label,$route,$match,$path])<a href="{{ route($route) }}" class="sidebar-item {{ request()->routeIs($match) ? 'active' : '' }} flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-slate-500"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $path }}"/></svg>{{ $label }}</a>@endforeach</nav>
 <div class="mt-auto border-t border-slate-100 pt-4">@if(in_array(Auth::user()->role, ['super_admin','school_admin','principal'], true))<a href="{{ route('settings.index') }}" class="sidebar-item {{ request()->routeIs('settings.*') ? 'active' : '' }} flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-slate-500"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="1.8" d="M12 15.5a3.5 3.5 0 100-7 3.5 3.5 0 000 7zM19.4 15a1.7 1.7 0 00.34 1.88l.06.06-2.83 2.83-.06-.06a1.7 1.7 0 00-1.88-.34 1.7 1.7 0 00-1.03 1.56V21h-4v-.08A1.7 1.7 0 008.94 19.4a1.7 1.7 0 00-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 004.57 15 1.7 1.7 0 003 14H3v-4h.08A1.7 1.7 0 004.6 8.94a1.7 1.7 0 00-.34-1.88L4.2 7l2.83-2.83.06.06A1.7 1.7 0 009 4.57 1.7 1.7 0 0010 3h4v.08a1.7 1.7 0 001.06 1.52 1.7 1.7 0 001.88-.34L17 4.2 19.83 7l-.06.06A1.7 1.7 0 0019.43 9 1.7 1.7 0 0021 10v4h-.08A1.7 1.7 0 0019.4 15z"/></svg>Settings</a>@endif<form method="POST" action="{{ route('logout') }}">@csrf<button class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-slate-500 hover:bg-red-50 hover:text-red-600">Sign out</button></form></div>
</aside>
