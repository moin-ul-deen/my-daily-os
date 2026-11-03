@extends('layouts.app')
@section('content')
@php
  $viewDate = \Carbon\Carbon::parse($date);
  $isToday = $viewDate->isToday();
  $suggestions = [
    ['name' => 'Walk 30 minutes', 'frequency' => 'daily'],
    ['name' => 'Drink 8 glasses of water', 'frequency' => 'daily'],
    ['name' => 'Learn for 30 minutes', 'frequency' => 'daily'],
    ['name' => 'Sleep by schedule', 'frequency' => 'daily'],
    ['name' => 'Freelance work 1 hour', 'frequency' => 'daily'],
  ];
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
  <h3 class="mb-0">Habits</h3>
  <a class="btn btn-accent" href="{{ route('habits.create') }}">Add Habit</a>
</div>
<div class="card intro-card mt-3"><p class="mb-1"><strong>A habit is one small thing you repeat — not a one-time task.</strong></p><p class="mb-0 text-muted-x">Each day you do it, tap “Done today”. The app remembers every day separately, shows your streak (how many days in a row) and your last 12 weeks, so you can see your consistency grow. A missed day is just information — start again the next day.</p></div>

<div class="card summary-card">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <strong>{{ $viewDate->format('l, d F Y') }}</strong>@if($isToday) <span class="pill pill-active">Today</span>@endif
      <div class="text-muted-x small">Done on this day: {{ collect($logs)->filter()->count() }} of {{ $habits->count() }} habits</div>
    </div>
    <div class="btn-group" role="group" aria-label="Day navigation">
      <a class="btn btn-ghost" href="{{ route('habits.index', ['date' => $viewDate->copy()->subDay()->toDateString()]) }}">&larr; Previous day</a>
      @if(!$isToday)<a class="btn btn-ghost" href="{{ route('habits.index') }}">Back to today</a>@endif
      <a class="btn btn-ghost" href="{{ route('habits.index', ['date' => $viewDate->copy()->addDay()->toDateString()]) }}">Next day &rarr;</a>
    </div>
  </div>
</div>

<div class="card">
  <h6 class="mb-2">Ideas to start with <span class="text-muted-x">(examples — tap one to pre-fill the form, nothing is saved until you press Save)</span></h6>
  <div class="d-flex flex-wrap gap-2">
    @foreach($suggestions as $s)<a class="chip text-decoration-none" href="{{ route('habits.create', $s) }}">+ {{ $s['name'] }}</a>@endforeach
  </div>
</div>

@forelse($habits as $h)
@php
  $done = (bool)($logs[$h->id] ?? false);
  $stats = (isset($habitStats) && is_array($habitStats)) ? ($habitStats[$h->id] ?? null) : null;
  $heat = $stats['last_84'] ?? null;
  $heatMap = [];
  if (is_array($heat)) { foreach ($heat as $cell) { $heatMap[data_get($cell,'date')] = (bool) data_get($cell,'completed'); } }
  $weekStart = $viewDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
@endphp
<div class="card">
  <div class="d-flex flex-wrap justify-content-between gap-2">
    <div style="min-width:230px;flex:1">
      <strong style="font-size:1.1rem">{{ $h->name }}</strong>
      <div class="text-muted-x small">{{ $h->category ?: 'Personal' }} · {{ $h->frequency === 'weekly' ? 'Weekly rhythm' : 'Every day' }}@if($h->target) · Target: {{ $h->target }}@endif</div>
      @if($h->description)<p class="mb-0 mt-1">{{ $h->description }}</p>@endif
      @if($stats && isset($stats['streak']))<p class="mb-0 mt-1"><strong>{{ $stats['streak'] }} day streak</strong> <span class="text-muted-x small">— days in a row up to today. Every day you tick it, it grows.</span></p>@endif
    </div>
    <div class="d-flex flex-column align-items-end gap-1">
      <form method="POST" action="{{ route('habits.toggle', $h) }}">@csrf<input type="hidden" name="date" value="{{ $date }}"><button class="btn {{ $done ? 'btn-accent' : 'btn-ghost' }}" style="min-width:140px">{{ $done ? 'Done ✓' : 'Done today' }}</button></form>
      <div class="d-flex gap-1">
        <a class="btn btn-sm btn-ghost" href="{{ route('habits.edit', $h) }}">Edit</a>
        <form method="POST" action="{{ route('habits.destroy', $h) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
      </div>
    </div>
  </div>

  @if($stats)
  <div class="mt-3">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2">
      <div>
        <div class="small text-muted-x mb-1">This week @if(isset($stats['week_done'])) — <strong style="color:var(--text)">{{ $stats['week_done'] }}@if(!empty($stats['week_target'])) of {{ $stats['week_target'] }}@endif done this week</strong>@endif</div>
        <div class="week-dots">
          @for($i = 0; $i < 7; $i++)
            @php $d = $weekStart->copy()->addDays($i); $dDone = $heatMap[$d->toDateString()] ?? false; @endphp
            <span class="week-dot {{ $dDone ? 'done' : '' }} {{ $d->isToday() ? 'today' : '' }}"><span class="wd-dot"></span>{{ $d->format('D')[0] }}</span>
          @endfor
        </div>
      </div>
      <div class="small text-muted-x">Filled circle = done that day. The outlined one is today.</div>
    </div>
    @if(is_array($heat) && count($heat))
    <div class="mt-3">
      <div class="small text-muted-x mb-1">Your last 12 weeks — each square is one day.</div>
      <div class="hm-scroll"><div class="heatmap">@foreach($heat as $cell)<span class="hm-cell {{ data_get($cell,'completed') ? 'done' : '' }} {{ data_get($cell,'date') === now()->toDateString() ? 'today' : '' }}" title="{{ data_get($cell,'date') }}{{ data_get($cell,'completed') ? ' — done' : '' }}"></span>@endforeach</div></div>
      <div class="small text-muted-x mt-1">Green squares are days you did this habit. Grey squares are days not ticked — they simply show where to build next.</div>
    </div>
    @endif
  </div>
  @endif
</div>
@empty
<div class="empty">No habits yet — and that is fine. A habit is a small action you repeat, like a 30-minute walk. Tap one of the examples above, or press “Add Habit”, give it a name and a small daily target, then tick “Done today” each day you do it. Your streak and weekly picture will grow from your real ticks.</div>
@endforelse
@endsection
