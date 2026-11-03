@extends('layouts.app')
@section('content')
@php $t = $template ?? $item ?? $routine ?? null; $dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']; $selDays = collect(old('recurrence_days', $t->recurrence_days ?? []))->map(fn($d)=>(int)$d); @endphp
<h3>Edit Routine Activity</h3>
<div class="card intro-card"><p class="mb-1"><strong>Change this repeating activity.</strong></p><p class="mb-0 text-muted-x">Your changes apply to future days. Days that already happened keep their own history — nothing in the past is rewritten.</p></div>
@if($t)
<div class="card"><form method="POST" action="{{ route('routine.update', $t) }}">@csrf @method('PUT')
  <div class="mb-3"><label class="form-label" for="r-title">Activity name</label><input id="r-title" name="title" class="form-control" value="{{ old('title', $t->title) }}" required></div>
  <div class="mb-3"><label class="form-label" for="r-description">Details (optional)</label><textarea id="r-description" name="description" class="form-control">{{ old('description', $t->description) }}</textarea></div>
  <div class="row">
    <div class="col-12 col-md-4 mb-3"><label class="form-label" for="r-category">Category</label><select id="r-category" name="category" class="form-select">@foreach(['Health','Salah','Job','Freelancing','Learning','Personal','Family','Finance','Other'] as $c)<option @selected(old('category', $t->category)==$c)>{{ $c }}</option>@endforeach</select></div>
    <div class="col-12 col-md-4 mb-3"><label class="form-label" for="r-starts_on">Starts on</label><input type="date" id="r-starts_on" name="starts_on" class="form-control" value="{{ old('starts_on', $t->starts_on ? \Carbon\Carbon::parse($t->starts_on)->toDateString() : now()->toDateString()) }}"></div>
    <div class="col-12 col-md-4 mb-3"><label class="form-label" for="r-priority">Priority</label><select id="r-priority" name="priority" class="form-select">@foreach(['Low','Medium','High','Urgent'] as $p)<option @selected(old('priority', $t->priority)==$p)>{{ $p }}</option>@endforeach</select></div>
  </div>
  <div class="row">
    <div class="col-12 col-md-4 mb-3"><label class="form-label" for="r-start">Planned start</label><input type="time" id="r-start" name="start_time" class="form-control" value="{{ old('start_time', $t->start_time) }}"><div class="form-text">When do you plan to begin this activity each day?</div></div>
    <div class="col-12 col-md-4 mb-3"><label class="form-label" for="r-end">Planned end</label><input type="time" id="r-end" name="end_time" class="form-control" value="{{ old('end_time', $t->end_time) }}"><div class="form-text">When do you plan to finish? After-midnight end times are fine.</div></div>
    <div class="col-12 col-md-4 mb-3"><label class="form-label" for="r-reminder">Reminder (minutes before)</label><input type="number" id="r-reminder" name="reminder_minutes" class="form-control" value="{{ old('reminder_minutes', $t->reminder_minutes) }}"><div class="form-text">Leave empty for no reminder.</div></div>
  </div>
  <div class="mb-3"><label class="form-label" for="r-recurrence">Repeat</label><select id="r-recurrence" name="recurrence_type" class="form-select">@foreach(['daily'=>'Every day','weekdays'=>'Weekdays (Monday to Friday)','weekly'=>'Every week on the starting day','monthly'=>'Every month','custom'=>'Custom days…'] as $k=>$label)<option value="{{ $k }}" @selected(old('recurrence_type', $t->recurrence_type)==$k)>{{ $label }}</option>@endforeach</select></div>
  <div class="mb-3" id="r-days"><label class="form-label">Which days?</label><div class="d-flex flex-wrap gap-3">@foreach($dayNames as $i=>$d)<label class="form-check"><input type="checkbox" class="form-check-input" name="recurrence_days[]" value="{{ $i }}" @checked($selDays->contains($i))> {{ $d }}</label>@endforeach</div><div class="form-text">Only used when Repeat is “Custom days”.</div></div>
  <button class="btn btn-accent">Save changes</button> <a class="btn btn-ghost" href="{{ route('routine.index') }}">Back to routine</a>
</form></div>
@else
<div class="empty">This routine activity could not be found.</div>
@endif
@endsection
