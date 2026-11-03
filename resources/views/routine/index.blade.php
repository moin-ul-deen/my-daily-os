@extends('layouts.app')
@section('content')
@php
  $templates = $templates ?? $items ?? collect();
  $dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
  $daysWords = function($t) use ($dayNames) {
      $type = $t->recurrence_type ?? 'daily';
      if ($type === 'daily') return 'Every day';
      if ($type === 'weekdays') return 'Every Monday to Friday';
      if ($type === 'weekly') return 'Every week on the starting day';
      if ($type === 'monthly') return 'Every month on the starting date';
      $days = collect($t->recurrence_days ?? [])->map(fn($d) => $dayNames[(int)$d] ?? null)->filter()->values();
      if ($days->count() === 5 && $days->diff(['Monday','Tuesday','Wednesday','Thursday','Friday'])->isEmpty()) return 'Every Monday to Friday';
      return $days->count() ? 'Every '. $days->implode(', ') : 'Custom days';
  };
  $isWorkday = function($t) {
      $type = $t->recurrence_type ?? 'daily';
      if ($type === 'weekdays' || $type === 'daily') return true;
      $days = collect($t->recurrence_days ?? [])->map(fn($d)=>(int)$d);
      return $days->count() && $days->diff([1,2,3,4,5])->isEmpty();
  };
  $workday = collect($templates)->filter($isWorkday);
  $daysoff = collect($templates)->reject($isWorkday);
@endphp
<h3>My Routine</h3>
<div class="card intro-card"><p class="mb-1"><strong>Your routine is the plan that repeats.</strong></p><p class="mb-0 text-muted-x">Build it once here — waking, learning, exercise, office hours, sleep. <strong>My Day</strong> then shows today's copy of this plan every morning. Changing your routine only changes future days — your history never changes. A one-time job does not belong here; add that as a task in My Day instead.</p></div>

@foreach([['Workday routine', $workday, 'Activities for your normal working days (your office shift is 5:00 PM – 2:00 AM unless you changed it in Settings).'], ['Days off & other', $daysoff, 'Activities for weekends, days off, or special days only.']] as [$gTitle, $group, $gNote])
<div class="card">
  <h5>{{ $gTitle }}</h5>
  <p class="text-muted-x">{{ $gNote }}</p>
  @forelse($group as $t)
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-2" style="border-top:1px solid var(--border)">
      <div>
        <strong>{{ $t->title }}</strong>
        <div class="text-muted-x small">@if($t->start_time){{ \Carbon\Carbon::parse($t->start_time)->format('h:i A') }}@if($t->end_time) – {{ \Carbon\Carbon::parse($t->end_time)->format('h:i A') }}@endif · @endif{{ $daysWords($t) }} · {{ $t->category ?? '' }}@if($t->description) · {{ $t->description }}@endif</div>
      </div>
      <div class="d-flex gap-1">
        <a class="btn btn-sm btn-ghost" href="{{ route('routine.edit', $t) }}">Edit</a>
        <form method="POST" action="{{ route('routine.destroy', $t) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
      </div>
    </div>
  @empty
    <div class="empty">Nothing here yet. Add an activity below and choose how it repeats.</div>
  @endforelse
</div>
@endforeach

<div class="card">
  <h5>Add an activity to your routine</h5>
  <form method="POST" action="{{ route('routine.store') }}">@csrf
    <div class="mb-3"><label class="form-label" for="r-title">Activity name</label><input id="r-title" name="title" class="form-control" placeholder="e.g. Morning walk" value="{{ old('title') }}" required><div class="form-text">One clear activity — the same words you would use for a task.</div></div>
    <div class="mb-3"><label class="form-label" for="r-description">Details (optional)</label><textarea id="r-description" name="description" class="form-control" placeholder="Optional details…">{{ old('description') }}</textarea></div>
    <div class="row">
      <div class="col-12 col-md-4 mb-3"><label class="form-label" for="r-category">Category</label><select id="r-category" name="category" class="form-select">@foreach(['Health','Salah','Job','Freelancing','Learning','Personal','Family','Finance','Other'] as $c)<option @selected(old('category','Personal')==$c)>{{ $c }}</option>@endforeach</select></div>
      <div class="col-12 col-md-4 mb-3"><label class="form-label" for="r-starts_on">Starts on</label><input type="date" id="r-starts_on" name="starts_on" class="form-control" value="{{ old('starts_on', now()->toDateString()) }}"><div class="form-text">The first day this activity appears in My Day. Today is already selected.</div></div>
      <div class="col-12 col-md-4 mb-3"><label class="form-label" for="r-priority">Priority</label><select id="r-priority" name="priority" class="form-select">@foreach(['Low','Medium','High','Urgent'] as $p)<option @selected(old('priority','Medium')==$p)>{{ $p }}</option>@endforeach</select></div>
    </div>
    <div class="row">
      <div class="col-12 col-md-4 mb-3"><label class="form-label" for="r-start">Planned start</label><input type="time" id="r-start" name="start_time" class="form-control" value="{{ old('start_time') }}"><div class="form-text">When do you plan to begin this activity each day?</div></div>
      <div class="col-12 col-md-4 mb-3"><label class="form-label" for="r-end">Planned end</label><input type="time" id="r-end" name="end_time" class="form-control" value="{{ old('end_time') }}"><div class="form-text">When do you plan to finish? An end time after midnight is fine (for example office work until 2:00 AM).</div></div>
      <div class="col-12 col-md-4 mb-3"><label class="form-label" for="r-reminder">Reminder (minutes before)</label><input type="number" id="r-reminder" name="reminder_minutes" class="form-control" placeholder="e.g. 10" value="{{ old('reminder_minutes') }}"><div class="form-text">Leave empty for no reminder.</div></div>
    </div>
    <div class="mb-3"><label class="form-label" for="r-recurrence">Repeat</label><select id="r-recurrence" name="recurrence_type" class="form-select"><option value="daily">Every day</option><option value="weekdays">Weekdays (Monday to Friday)</option><option value="weekly">Every week on the starting day</option><option value="monthly">Every month</option><option value="custom">Custom days…</option></select><div class="form-text">In plain words: “Repeat this activity every Monday to Friday” is the Weekdays choice. Pick Custom days to tick exact days.</div></div>
    <div class="mb-3" id="r-days" style="display:none"><label class="form-label">Which days?</label><div class="d-flex flex-wrap gap-3">@foreach($dayNames as $i=>$d)<label class="form-check"><input type="checkbox" class="form-check-input" name="recurrence_days[]" value="{{ $i }}"> {{ $d }}</label>@endforeach</div></div>
    <button class="btn btn-accent">Add to routine</button>
  </form>
</div>
@push('scripts')<script>var rs=document.getElementById('r-recurrence');function tog(){document.getElementById('r-days').style.display=(rs.value==='custom')?'block':'none';}rs.addEventListener('change',tog);tog();</script>@endpush
@endsection
