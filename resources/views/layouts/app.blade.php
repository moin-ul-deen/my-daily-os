@php
  $themeMode = 'dark'; $accentColor = '#8BCB9B';
  if (auth()->check()) {
    $st = auth()->user()->settings;
    $t = $st->theme ?? 'dark';
    $themeMode = in_array($t, ['dark','light','system']) ? $t : 'dark';
    $a = $st->accent_color ?? '#8BCB9B';
    $accentColor = (is_string($a) && preg_match('/^#[0-9A-Fa-f]{6}$/', $a)) ? $a : '#8BCB9B';
  }
  $themeResolved = $themeMode === 'system' ? 'dark' : $themeMode;
@endphp
<!DOCTYPE html><html lang="en" data-theme="{{ $themeMode }}" data-theme-resolved="{{ $themeResolved }}" style="--accent: {{ $accentColor }}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><script>(function(){var d=document.documentElement;function res(){var t=d.getAttribute('data-theme');var r=(t==='system')?(window.matchMedia('(prefers-color-scheme: light)').matches?'light':'dark'):t;d.setAttribute('data-theme-resolved',r);}res();try{window.matchMedia('(prefers-color-scheme: light)').addEventListener('change',res);}catch(e){}})();</script><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="vapid-public-key" content="{{ config('services.vapid.public_key') }}"><meta name="theme-color" content="#101411"><link rel="manifest" href="/manifest.json"><link rel="icon" href="/icons/icon.svg"><title>{{ $title ?? 'My Daily OS' }}</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="/css/app.css?v=3.1"><script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script></head>
<body><nav class="sidebar"><h4 style="color:var(--accent-text)">My Daily OS</h4><div class="text-muted-x mb-3">{{ auth()->user()->name ?? '' }}</div>
@foreach([['dashboard','Dashboard'],['my-day','My Day'],['salah.index','Salah'],['health.water.index','Health'],['meals.index','Meals'],['sleep.index','Sleep'],['freelancing.projects.index','Freelancing'],['learning.index','Learning'],['habits.index','Habits'],['finance.index','Finance'],['calendar.index','Calendar'],['reports.daily','Reports'],['settings.index','Settings'],['help','Help']] as $n)<a href="{{ route($n[0]) }}">{{ $n[1] }}</a>@endforeach
@if(Route::has('routine.index'))<a href="{{ route('routine.index') }}">My Routine</a>@endif
@if(Route::has('onboarding.index'))<a href="{{ route('onboarding.index') }}">Set Up Guide</a>@endif
<form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-ghost mt-3">Logout</button></form></nav>
<div class="main"><div class="d-flex justify-content-end align-items-center mb-2">@auth @php $unreadCount = \App\Models\NotificationLog::where('user_id', auth()->id())->where('channel','in_app')->whereNull('read_at')->count(); @endphp <a href="{{ route('notifications.inbox') }}" class="btn btn-sm btn-ghost position-relative" title="Notifications">&#128276; Notifications @if($unreadCount)<span class="badge bg-danger">{{ $unreadCount }}</span>@endif</a> @endauth</div>@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif @yield('content')</div>
<nav class="bottomnav"><a href="{{ route('dashboard') }}">Home</a><a href="{{ route('tasks.index') }}">Tasks</a><a href="{{ route('salah.index') }}">Salah</a><a href="{{ route('health.water.index') }}">Health</a><a href="{{ route('settings.index') }}">Settings</a></nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="/js/app.js?v=3.1"></script><script src="/js/push.js" defer></script>@stack('scripts')</body></html>
