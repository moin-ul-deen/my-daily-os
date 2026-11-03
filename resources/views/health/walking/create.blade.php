@extends('layouts.app')
@section('content')<h3>Add Walking</h3>
<div class="card intro-card"><p class="mb-1"><strong>Log one walk.</strong></p><p class="mb-0 text-muted-x">Enter the distance you walked. Everything else is optional — fill only what you know.</p></div>
<div class="card"><form method="POST" action="{{ route('health.walking.store') }}">@csrf<div class="mb-3"><label class="form-label" for="date">Date</label><input type="date" id="date" name="date" class="form-control" value="{{ old('date', now()->toDateString()) }}" required><div class="form-text">Today is already selected.</div></div>
<div class="mb-3"><label class="form-label" for="distance_km">Distance (km)</label><input type="number" step="0.01" id="distance_km" name="distance_km" class="form-control" placeholder="e.g. 2.5" value="{{ old('distance_km') }}" required></div>
<div class="mb-3"><label class="form-label" for="duration_minutes">Duration (minutes)</label><input type="number" id="duration_minutes" name="duration_minutes" class="form-control" placeholder="e.g. 30" value="{{ old('duration_minutes') }}"></div>
<div class="mb-3"><label class="form-label" for="steps">Steps</label><input type="number" id="steps" name="steps" class="form-control" placeholder="e.g. 3200" value="{{ old('steps') }}"><div class="form-text">Optional — copy it from your phone or watch if it counted your steps.</div></div>
<div class="mb-3"><label class="form-label" for="calories">Calories</label><input type="number" id="calories" name="calories" class="form-control" placeholder="e.g. 120" value="{{ old('calories') }}"><div class="form-text">Optional — only add if you know it; this is your own estimate.</div></div>
<div class="mb-3"><label class="form-label" for="notes">Notes</label><input type="text" id="notes" name="notes" class="form-control" placeholder="Optional note…" value="{{ old('notes') }}"></div>
<button class="btn btn-accent">Save</button></form></div>@endsection
