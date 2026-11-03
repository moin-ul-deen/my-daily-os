@extends('layouts.app')
@section('content')<h3>Edit Walking</h3>
<div class="card intro-card"><p class="mb-1"><strong>Fix this walking entry.</strong></p><p class="mb-0 text-muted-x">Change what you need, then tap Update.</p></div>
<div class="card"><form method="POST" action="{{ route('health.walking.update',$item) }}">@csrf @method('PUT')<div class="mb-3"><label class="form-label" for="date">Date</label><input type="date" id="date" name="date" value="{{ $item->date }}" class="form-control"></div>
<div class="mb-3"><label class="form-label" for="distance_km">Distance (km)</label><input type="number" step="0.01" id="distance_km" name="distance_km" value="{{ $item->distance_km }}" class="form-control" placeholder="e.g. 2.5"></div>
<div class="mb-3"><label class="form-label" for="duration_minutes">Duration (minutes)</label><input type="number" id="duration_minutes" name="duration_minutes" value="{{ $item->duration_minutes }}" class="form-control" placeholder="e.g. 30"></div>
<div class="mb-3"><label class="form-label" for="steps">Steps</label><input type="number" id="steps" name="steps" value="{{ $item->steps }}" class="form-control" placeholder="e.g. 3200"><div class="form-text">Optional — copy it from your phone or watch if it counted your steps.</div></div>
<div class="mb-3"><label class="form-label" for="calories">Calories</label><input type="number" id="calories" name="calories" value="{{ $item->calories }}" class="form-control" placeholder="e.g. 120"><div class="form-text">Optional — only add if you know it; this is your own estimate.</div></div>
<div class="mb-3"><label class="form-label" for="notes">Notes</label><input type="text" id="notes" name="notes" value="{{ $item->notes }}" class="form-control" placeholder="Optional note…"></div>
<button class="btn btn-accent">Update</button></form></div>@endsection
