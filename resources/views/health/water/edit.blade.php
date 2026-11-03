@extends('layouts.app')
@section('content')<h3>Edit Water</h3>
<div class="card intro-card"><p class="mb-1"><strong>Fix this water entry.</strong></p><p class="mb-0 text-muted-x">Change the amount, date or time, then tap Update. Your other entries do not change.</p></div>
<div class="card"><form method="POST" action="{{ route('health.water.update',$item) }}">@csrf @method('PUT')<div class="mb-3"><label class="form-label" for="date">Date</label><input type="date" id="date" name="date" value="{{ $item->date }}" class="form-control"></div>
<div class="mb-3"><label class="form-label" for="quantity_ml">Quantity (ml)</label><input type="number" id="quantity_ml" name="quantity_ml" value="{{ $item->quantity_ml }}" class="form-control" placeholder="e.g. 250 (one glass)"><div class="form-text">250 ml = one glass · 500 ml = a small bottle · 1000 ml = 1 litre.</div></div>
<div class="mb-3"><label class="form-label" for="time">Time</label><input type="time" id="time" name="time" value="{{ $item->time }}" class="form-control"></div>
<div class="mb-3"><label class="form-label" for="note">Note</label><input type="text" id="note" name="note" value="{{ $item->note }}" class="form-control" placeholder="Optional note…"></div>
<button class="btn btn-accent">Update</button></form></div>@endsection
