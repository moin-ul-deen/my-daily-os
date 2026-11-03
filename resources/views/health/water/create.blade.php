@extends('layouts.app')
@section('content')<h3>Add Water</h3>
<div class="card intro-card"><p class="mb-1"><strong>Log one drink of water.</strong></p><p class="mb-0 text-muted-x">Today's date and the current time are already filled. Just enter how much you drank — 250 ml is one glass.</p></div>
<div class="card"><form method="POST" action="{{ route('health.water.store') }}">@csrf<div class="mb-3"><label class="form-label" for="date">Date</label><input type="date" id="date" name="date" class="form-control" value="{{ old('date', now()->toDateString()) }}" required><div class="form-text">Today is already selected.</div></div>
<div class="mb-3"><label class="form-label" for="quantity_ml">Quantity (ml)</label><input type="number" id="quantity_ml" name="quantity_ml" class="form-control" placeholder="e.g. 250 (one glass)" value="{{ old('quantity_ml') }}" required><div class="form-text">250 ml = one glass · 500 ml = a small bottle · 1000 ml = 1 litre.</div></div>
<div class="mb-3"><label class="form-label" for="time">Time</label><input type="time" id="time" name="time" class="form-control" value="{{ old('time', now()->format('H:i')) }}"><div class="form-text">The current time is already filled — change it if you drank earlier.</div></div>
<div class="mb-3"><label class="form-label" for="note">Note</label><input type="text" id="note" name="note" class="form-control" placeholder="Optional note…" value="{{ old('note') }}"></div>
<button class="btn btn-accent">Save</button></form></div>@endsection
