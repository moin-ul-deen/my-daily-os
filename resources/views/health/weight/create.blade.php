@extends('layouts.app')
@section('content')<h3>Add Weight</h3>
<div class="card intro-card"><p class="mb-1"><strong>Record today's weight.</strong></p><p class="mb-0 text-muted-x">Enter the number from your scale. This becomes a new entry in your history — it does not replace older entries.</p></div>
<div class="card"><form method="POST" action="{{ route('health.weight.store') }}">@csrf<div class="mb-3"><label class="form-label" for="date">Date</label><input type="date" id="date" name="date" class="form-control" value="{{ old('date', now()->toDateString()) }}" required><div class="form-text">Today is already selected.</div></div>
<div class="mb-3"><label class="form-label" for="weight_kg">Weight (kg)</label><input type="number" step="0.1" id="weight_kg" name="weight_kg" class="form-control" placeholder="e.g. 72.5" value="{{ old('weight_kg') }}" required><div class="form-text">Tip: weigh yourself at the same time of day (for example, morning) so the trend is easy to compare.</div></div>
<div class="mb-3"><label class="form-label" for="note">Note</label><input type="text" id="note" name="note" class="form-control" placeholder="Optional note…" value="{{ old('note') }}"></div>
<button class="btn btn-accent">Save</button></form></div>@endsection
