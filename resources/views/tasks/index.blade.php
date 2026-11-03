@extends('layouts.app')
@section('content')
@php
  $selDate = request('date') ? \Carbon\Carbon::parse(request('date')) : now();
  $items = collect($tasks->items());
  $timed = $items->filter(fn($t) => $t->start_time)->sortBy('start_time');
  $anytime = $items->filter(fn($t) => !$t->start_time);
  $pillMap = ['Completed'=>'pill-completed','In progress'=>'pill-active','Pending'=>'pill-upcoming','Skipped'=>'pill-skipped','Cancelled'=>'pill-missed'];
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
  <h3 class="mb-0">My Day</h3>
  <a class="btn btn-accent" href="{{ route('tasks.create') }}">Add Task</a>
</div>
<div class="card intro-card mt-3"><p class="mb-1"><strong>Everything you planned for one day, in time order.</strong></p><p class="mb-0 text-muted-x">Tasks with a time appear under “Timed”. Tasks without a time appear under “Anytime” — do them whenever you are free. Finish a task? Tap Complete. Cannot do it today? Tap “Move to tomorrow” — nothing is lost. @if(Route::has('routine.index')) Activities that repeat every day live in <a href="{{ route('routine.index') }}">My Routine</a> — this page shows today's copy of them. @endif</p></div>

<div class="card summary-card">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="btn-group" role="group" aria-label="Day navigation">
      <a class="btn btn-ghost" href="{{ route('tasks.index', array_merge(request()->except('date','page'), ['date' => $selDate->copy()->subDay()->toDateString()])) }}">&larr; Previous day</a>
      <a class="btn btn-ghost" href="{{ route('tasks.index', request()->except('date','page')) }}">Today</a>
      <a class="btn btn-ghost" href="{{ route('tasks.index', array_merge(request()->except('date','page'), ['date' => $selDate->copy()->addDay()->toDateString()])) }}">Next day &rarr;</a>
    </div>
    <form method="GET" class="d-flex gap-2 align-items-end">
      @foreach(request()->except('date','page') as $k=>$v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
      <div><label class="form-label" for="f-date">Date</label><input type="date" id="f-date" name="date" class="form-control" value="{{ $selDate->toDateString() }}"></div>
      <button class="btn btn-accent">Go</button>
    </form>
  </div>
  <h6 class="mt-3 mb-2">{{ $selDate->format('l, d F Y') }}</h6>
  <div class="summary-chips">
    <span class="chip"><span class="chip-label">Tasks shown</span> <strong>{{ $items->count() }}</strong></span>
    <span class="chip"><span class="chip-label">Completed</span> <strong>{{ $items->where('status','Completed')->count() }}</strong></span>
    <span class="chip"><span class="chip-label">Still to do</span> <strong>{{ $items->whereIn('status',['Pending','In progress'])->count() }}</strong></span>
  </div>
</div>

<form class="card" method="GET"><div class="row g-2"><input type="hidden" name="date" value="{{ request('date') }}"><div class="col-12 col-md-4"><label class="form-label" for="f-category">Category</label><input id="f-category" name="category" class="form-control" placeholder="e.g. Freelancing" value="{{ request('category') }}"></div><div class="col-12 col-md-4"><label class="form-label" for="f-search">Search</label><input id="f-search" name="search" class="form-control" placeholder="Search by name…" value="{{ request('search') }}"></div><div class="col-12 col-md-4 d-flex align-items-end"><button class="btn btn-ghost w-100">Filter</button></div></div></form>

@if($items->count() === 0)
  <div class="empty">No tasks for this day yet. Tap “Add Task” to plan something — even one small task makes the day clearer.</div>
@endif

@if($timed->count())
<h5 class="mt-4">Timed</h5>
@foreach($timed as $t)
<div class="card">
  <div class="d-flex flex-wrap justify-content-between gap-2">
    <div>
      <strong>{{ $t->title }}</strong> <span class="pill {{ $pillMap[$t->status] ?? 'pill-upcoming' }}">{{ $t->status }}</span>
      <div class="text-muted-x">{{ \Carbon\Carbon::parse($t->start_time)->format('h:i A') }}@if($t->end_time) – {{ \Carbon\Carbon::parse($t->end_time)->format('h:i A') }}@endif · {{ $t->category }} · {{ $t->priority }} priority · {{ $t->date->format('d M Y') }}</div>
      @if($t->description)<p class="mb-0 mt-1">{{ $t->description }}</p>@endif
    </div>
    <div class="d-flex flex-wrap gap-1 align-content-start">
      @if($t->status!=='Completed')<form method="POST" action="{{ route('tasks.complete',$t) }}">@csrf<button class="btn btn-sm btn-accent">Complete</button></form>@else<form method="POST" action="{{ route('tasks.reopen',$t) }}">@csrf<button class="btn btn-sm btn-ghost">Reopen</button></form>@endif
      <form method="POST" action="{{ route('tasks.postpone',$t) }}">@csrf<button class="btn btn-sm btn-ghost">Move to tomorrow</button></form>
      <form method="POST" action="{{ route('tasks.duplicate',$t) }}">@csrf<button class="btn btn-sm btn-ghost">Duplicate</button></form>
      <a href="{{ route('tasks.edit',$t) }}" class="btn btn-sm btn-ghost">Edit</a>
      <form method="POST" action="{{ route('tasks.destroy',$t) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
    </div>
  </div>
</div>
@endforeach
@endif

@if($anytime->count())
<h5 class="mt-4">Anytime</h5>
@foreach($anytime as $t)
<div class="card">
  <div class="d-flex flex-wrap justify-content-between gap-2">
    <div>
      <strong>{{ $t->title }}</strong> <span class="pill {{ $pillMap[$t->status] ?? 'pill-upcoming' }}">{{ $t->status }}</span>
      <div class="text-muted-x">Any time of day · {{ $t->category }} · {{ $t->priority }} priority · {{ $t->date->format('d M Y') }}</div>
      @if($t->description)<p class="mb-0 mt-1">{{ $t->description }}</p>@endif
    </div>
    <div class="d-flex flex-wrap gap-1 align-content-start">
      @if($t->status!=='Completed')<form method="POST" action="{{ route('tasks.complete',$t) }}">@csrf<button class="btn btn-sm btn-accent">Complete</button></form>@else<form method="POST" action="{{ route('tasks.reopen',$t) }}">@csrf<button class="btn btn-sm btn-ghost">Reopen</button></form>@endif
      <form method="POST" action="{{ route('tasks.postpone',$t) }}">@csrf<button class="btn btn-sm btn-ghost">Move to tomorrow</button></form>
      <form method="POST" action="{{ route('tasks.duplicate',$t) }}">@csrf<button class="btn btn-sm btn-ghost">Duplicate</button></form>
      <a href="{{ route('tasks.edit',$t) }}" class="btn btn-sm btn-ghost">Edit</a>
      <form method="POST" action="{{ route('tasks.destroy',$t) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
    </div>
  </div>
</div>
@endforeach
@endif

{{ $tasks->appends(request()->except('page'))->links() }}
@endsection
