@extends('layouts.app')
@section('content')
@php
  $expl = [
    'profile' => 'Your name, city and timezone — the app uses these for the clock, dates and prayer times.',
    'work' => 'Your office start and end times and working days, so your timeline shows work at the right place. Overnight shifts (like 5:00 PM to 2:00 AM) are fully supported.',
    'task' => 'One thing you need to do. Give it a name and a date; a time only if it has one.',
    'routine' => 'The activities that repeat — waking, learning, exercise, office, sleep. Build the plan once; My Day then shows the copy for today every morning.',
    'salah' => 'Confirm your city and calculation method once. Times are calculated for your location, with Hanafi Asr — never typed in by hand.',
    'health' => 'Try one small entry: a glass of water (+250 ml on the Water page), your weight, or a short walk.',
    'habits' => 'One small repeated action, like a 30-minute walk. Tick it daily and watch your streak and 12-week picture grow.',
    'notifications' => 'Turn on reminders so prayer and task reminders reach you — in the app inbox, and as a pop-up on this device.',
  ];
  $stepsList = $steps ?? [];
  $doneCount = collect($stepsList)->where('done', true)->count();
@endphp
<h3>Welcome to My Daily OS</h3>
<div class="card intro-card"><p class="mb-1"><strong>Let&rsquo;s set up your day in a few short steps.</strong></p><p class="mb-0 text-muted-x">There is no wrong order and nothing here is locked — do one step now and the rest later if you like. A step gets its tick automatically when you have really done it. So far: <strong>{{ $doneCount }} of {{ count($stepsList) }} done</strong>.</p></div>

<div class="card">
  @forelse($stepsList as $i => $step)
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-3" @if(!$loop->last) style="border-bottom:1px solid var(--border)" @endif>
    <div class="d-flex gap-3 align-items-start" style="min-width:240px;flex:1">
      <span class="salah-dot {{ !empty($step['done']) ? 'done' : '' }}" style="width:22px;height:22px;margin-top:2px" aria-hidden="true"></span>
      <div>
        <strong>Step {{ $i + 1 }}: {{ $step['title'] }}</strong> @if(!empty($step['done']))<span class="pill pill-completed">Done</span>@endif
        <div class="text-muted-x">{{ $expl[$step['key']] ?? '' }}</div>
      </div>
    </div>
    <a class="btn {{ !empty($step['done']) ? 'btn-ghost' : 'btn-accent' }}" href="{{ $step['url'] }}">{{ !empty($step['done']) ? 'Review' : 'Start' }}</a>
  </div>
  @empty
  <div class="empty">The setup steps could not be loaded right now. Open the Dashboard — everything still works, and you can return here later.</div>
  @endforelse
</div>

<div class="card">
  <h5>Your day, once set up</h5>
  <p><strong>Morning:</strong> open the Dashboard — your timeline shows what happens today and what is next.</p>
  <p><strong>During the day:</strong> tick tasks and habits as you finish them, mark each prayer, and log water and meals as you go.</p>
  <p class="mb-0"><strong>Night:</strong> record your sleep and glance at the Daily Report. Two minutes, and tomorrow&rsquo;s plan is already waiting.</p>
</div>

@if(Route::has('onboarding.dismiss'))
<form method="POST" action="{{ route('onboarding.dismiss') }}">@csrf<button class="btn btn-ghost">Skip for now</button></form>
<p class="text-muted-x small mt-2">You can reopen this guide any time from <a href="{{ route('help') }}">Help</a>.</p>
@endif
@endsection
