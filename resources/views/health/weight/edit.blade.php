@extends('layouts.app')
@section('content')<h3>Edit Weight</h3>
<div class="card intro-card"><p class="mb-1"><strong>Fix this weight entry.</strong></p><p class="mb-0 text-muted-x">Only this date's entry changes — the rest of your history stays as it is.</p></div>
<div class="card"><form method="POST" action="{{ route('health.weight.update',$item) }}">@csrf @method('PUT')<div class="mb-3"><label class="form-label" for="date">Date</label><input type="date" id="date" name="date" value="{{ $item->date }}" class="form-control"></div>
<div class="mb-3"><label class="form-label" for="weight_kg">Weight (kg)</label><input type="number" step="0.1" id="weight_kg" name="weight_kg" value="{{ $item->weight_kg }}" class="form-control" placeholder="e.g. 72.5"></div>
<div class="mb-3"><label class="form-label" for="note">Note</label><input type="text" id="note" name="note" value="{{ $item->note }}" class="form-control" placeholder="Optional note…"></div>
<button class="btn btn-accent">Update</button></form></div>@endsection
