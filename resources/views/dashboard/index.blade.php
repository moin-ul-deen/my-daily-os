@extends('layouts.app')
@section('content')
@php
  $tz = auth()->user()->timezone ?? 'Asia/Karachi';
  $nowLocal = isset($date) ? $date : now()->setTimezone($tz);
  $todayStr = $nowLocal->toDateString();
  $hour = $nowLocal->hour;
  $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : ($hour < 21 ? 'Good evening' : 'Good night'));
  $waterTarget = (int)(auth()->user()->settings->water_target_ml ?? 2500);
  $waterPct = $waterTarget > 0 ? min(100, round(($waterToday ?? 0) / $waterTarget * 100)) : 0;
  $taskPct = ($totalTasks ?? 0) ? round($completedTasks / $totalTasks * 100) : 0;
  $prayerMarked = collect($prayerRecords ?? [])->filter()->count();
  $statusLabels = ['waqt_par'=>'Waqt par','late'=>'Late','qaza'=>'Qaza','missed'=>'Missed'];

  /* ---------- Timeline: prefer backend $timeline, else build from today's real data ---------- */
  $tlItems = [];
  if (isset($timeline) && is_iterable($timeline) && count($timeline)) {
      foreach ($timeline as $it) {
          $tlItems[] = [
              'time_label' => data_get($it,'time_label') ?? data_get($it,'start') ?? '',
              'title' => data_get($it,'title') ?? 'Activity',
              'type' => data_get($it,'type') ?? 'task',
              'state' => data_get($it,'state') ?? 'upcoming',
              'meta' => data_get($it,'meta') ?? '',
              'url' => data_get($it,'url'),
              'task_id' => null, 'prayer' => null,
          ];
      }
  } else {
      $raw = [];
      if (!empty($prayerTimes)) {
          foreach (['Fajr','Dhuhr','Asr','Maghrib','Isha'] as $p) {
              $tstr = $prayerTimes->{strtolower($p)};
              if (!$tstr) continue;
              $start = \Carbon\Carbon::parse($todayStr.' '.$tstr, $tz);
              $rec = $prayerRecords[$p] ?? null;
              $state = in_array($rec, ['waqt_par','late','qaza']) ? 'completed' : ($rec === 'missed' ? 'missed' : ((($nextPrayer['name'] ?? '') === $p) ? 'active' : 'upcoming'));
              $raw[] = ['sort' => $start->hour * 60 + $start->minute, 'time_label' => $start->format('h:i A'), 'end_label' => null,
                  'title' => $p.' prayer', 'type' => 'prayer', 'state' => $state,
                  'meta' => $rec ? ('Status: '.($statusLabels[$rec] ?? $rec)) : 'Not marked yet', 'url' => route('salah.index'),
                  'task_id' => null, 'prayer' => $p];
          }
      }
      foreach (($tasks ?? collect()) as $t) {
          if (!$t->start_time) continue;
          $start = \Carbon\Carbon::parse($todayStr.' '.$t->start_time, $tz);
          $end = $t->end_time ? \Carbon\Carbon::parse($todayStr.' '.$t->end_time, $tz) : null;
          $state = $t->status === 'Completed' ? 'completed' : (in_array($t->status, ['Skipped','Cancelled']) ? 'skipped' : (($nowLocal->gte($start) && (!$end || $nowLocal->lte($end))) ? 'active' : 'upcoming'));
          $raw[] = ['sort' => $start->hour * 60 + $start->minute, 'time_label' => $start->format('h:i A'), 'end_label' => $end ? $end->format('h:i A') : null,
              'title' => $t->title, 'type' => 'task', 'state' => $state,
              'meta' => trim(($t->category ?? '').($t->status ? ' · '.$t->status : '')), 'url' => route('tasks.edit', $t),
              'task_id' => $t->id, 'prayer' => null];
      }
      $os = auth()->user()->settings->office_start ?? null; $oe = auth()->user()->settings->office_end ?? null;
      if ($os && $oe && $os !== $oe) {
          $oStart = \Carbon\Carbon::parse($todayStr.' '.$os, $tz);
          $oEnd = \Carbon\Carbon::parse($todayStr.' '.$oe, $tz);
          $cross = $oEnd->lte($oStart); if ($cross) $oEnd->addDay();
          $state = 'upcoming'; $sort = $oStart->hour * 60 + $oStart->minute;
          if ($nowLocal->between($oStart, $oEnd)) { $state = 'active'; }
          elseif ($nowLocal->gt($oEnd)) { $state = 'completed'; }
          elseif ($cross && $nowLocal->lt($oEnd) && $nowLocal->format('H:i') < $oe) { $state = 'active'; $sort = -1; }
          $raw[] = ['sort' => $sort, 'time_label' => $oStart->format('h:i A'), 'end_label' => \Carbon\Carbon::parse($oe)->format('h:i A'),
              'title' => 'Office hours', 'type' => 'office', 'state' => $state,
              'meta' => 'Work shift'.($cross ? ' — continues until '.\Carbon\Carbon::parse($oe)->format('h:i A').' after midnight' : ''), 'url' => route('office.index'),
              'task_id' => null, 'prayer' => null];
      }
      usort($raw, fn($a,$b) => $a['sort'] <=> $b['sort']);
      $tlItems = $raw;
  }
  if (!isset($nextUp) || !$nextUp) {
      $nextUpItem = collect($tlItems)->firstWhere('state','active') ?? collect($tlItems)->firstWhere('state','upcoming');
  } else {
      $nextUpItem = ['time_label' => data_get($nextUp,'time_label') ?? data_get($nextUp,'start') ?? '', 'title' => data_get($nextUp,'title') ?? '', 'type' => data_get($nextUp,'type') ?? '', 'state' => data_get($nextUp,'state') ?? 'upcoming', 'meta' => data_get($nextUp,'meta') ?? '', 'url' => data_get($nextUp,'url'), 'task_id' => null, 'prayer' => null];
  }
  $anytimeTasks = collect($tasks ?? [])->filter(fn($t) => !$t->start_time);
@endphp

{{-- (a) Greeting header --}}
<div class="d-flex flex-wrap justify-content-between align-items-end mb-1">
  <div>
    <h3 class="mb-0">{{ $greeting }}, {{ auth()->user()->name }}</h3>
    <p class="text-muted-x mb-0">{{ $nowLocal->format('l, d F Y') }} · <span id="clock">{{ $nowLocal->format('h:i A') }}</span> · {{ $tz }}</p>
  </div>
</div>
<p class="mb-3">Today: <strong>{{ $completedTasks ?? 0 }} of {{ $totalTasks ?? 0 }} tasks done</strong> · <strong>{{ $prayerMarked }} of 5 prayers marked</strong> · <strong>{{ $waterToday ?? 0 }} ml water</strong>. @if(($totalTasks ?? 0) === 0) Add your first task or build your routine to plan the day. @endif</p>

{{-- (b) Onboarding --}}
@if(!empty($showOnboarding) && Route::has('onboarding.index'))
<div class="card intro-card">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div><h5 class="mb-1">Set up My Daily OS</h5><p class="mb-0 text-muted-x">A few short steps — profile, work hours, routine, Salah and health targets — and the app starts guiding your day.</p></div>
    <div class="d-flex gap-2">
      <a class="btn btn-accent" href="{{ route('onboarding.index') }}">Start setup</a>
      @if(Route::has('onboarding.dismiss'))<form method="POST" action="{{ route('onboarding.dismiss') }}">@csrf<button class="btn btn-ghost">Dismiss</button></form>@endif
    </div>
  </div>
</div>
@endif

{{-- (c) Next Up hero --}}
<div class="card" style="border-color:color-mix(in srgb,var(--accent) 55%,var(--border))">
  <h6 class="text-muted-x text-uppercase mb-1" style="letter-spacing:.06em">Next up</h6>
  @if($nextUpItem)
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <div class="stat" style="font-size:1.6rem">{{ $nextUpItem['title'] }}</div>
        <p class="mb-0 text-muted-x">{{ $nextUpItem['time_label'] }}@if(!empty($nextUpItem['meta'])) · {{ $nextUpItem['meta'] }}@endif @if($nextUpItem['state']==='active') · <span class="pill pill-active">Happening now</span>@endif</p>
      </div>
      <div class="d-flex gap-2">
        @if(!empty($nextUpItem['task_id']))<form method="POST" action="{{ route('tasks.complete', $nextUpItem['task_id']) }}">@csrf<button class="btn btn-accent">Mark complete</button></form>@endif
        @if(!empty($nextUpItem['url']))<a class="btn btn-ghost" href="{{ $nextUpItem['url'] }}">Open</a>@endif
      </div>
    </div>
  @elseif(count($tlItems))
    <p class="mb-0">Everything planned for today is done or marked. Well done — check your <a href="{{ route('reports.daily') }}">Daily Report</a> to review the day.</p>
  @else
    <p class="mb-0">Nothing is planned yet. Add a task below, or build your repeating routine so every day starts with a plan.</p>
  @endif
</div>

<div class="row">
  <div class="col-12 col-lg-7">
    {{-- (d) Timeline --}}
    <div class="card">
      <div class="d-flex justify-content-between align-items-center"><h5 class="mb-0">Today's Timeline</h5><a class="btn btn-sm btn-ghost" href="{{ route('my-day') }}">Open My Day</a></div>
      <p class="text-muted-x">Your planned day in time order — prayers, tasks and office hours together. <span class="pill pill-active">Now</span> shows what is happening at this time.</p>
      @if(count($tlItems))
      <ul class="timeline">
        @foreach($tlItems as $it)
        <li class="timeline-item state-{{ $it['state'] }}">
          <div class="timeline-time">{{ $it['time_label'] }}@if(!empty($it['end_label']))<small>to {{ $it['end_label'] }}</small>@endif</div>
          <div class="timeline-dot"></div>
          <div class="timeline-body @if($it['type']==='office') timeline-office @endif">
            <div class="d-flex flex-wrap justify-content-between gap-1">
              <span class="tl-title">{{ $it['title'] }}</span>
              <span class="pill pill-{{ $it['state'] }}">{{ ['completed'=>'Completed','active'=>'Now','upcoming'=>'Upcoming','skipped'=>'Skipped','missed'=>'Missed'][$it['state']] ?? ucfirst($it['state']) }}</span>
            </div>
            @if(!empty($it['meta']))<div class="text-muted-x small">{{ $it['meta'] }}</div>@endif
            @if(!empty($it['prayer']))
              <form method="POST" action="{{ route('salah.status') }}" class="d-flex flex-wrap gap-2 mt-2">@csrf
                <input type="hidden" name="prayer_name" value="{{ $it['prayer'] }}"><input type="hidden" name="prayer_date" value="{{ $todayStr }}">
                <select name="status" class="form-select form-select-sm" style="max-width:170px;min-height:38px" aria-label="Status for {{ $it['prayer'] }}"><option value="">Unmarked</option>@foreach($statusLabels as $k=>$label)<option value="{{ $k }}" @selected(($prayerRecords[$it['prayer']] ?? '')==$k)>{{ $label }}</option>@endforeach</select>
                <button class="btn btn-sm btn-accent">Save</button>
              </form>
            @elseif(!empty($it['task_id']) && $it['state']!=='completed')
              <form method="POST" action="{{ route('tasks.complete', $it['task_id']) }}" class="mt-2">@csrf<button class="btn btn-sm btn-accent">Complete</button></form>
            @endif
          </div>
        </li>
        @endforeach
      </ul>
      @else
      <div class="empty">Nothing planned yet — add your first task or build your routine.<div class="mt-3 d-flex flex-wrap gap-2 justify-content-center"><a class="btn btn-accent" href="{{ route('tasks.create') }}">Add a task</a>@if(Route::has('routine.index'))<a class="btn btn-ghost" href="{{ route('routine.index') }}">Build my routine</a>@endif</div></div>
      @endif
      @if($anytimeTasks->count())
        <h6 class="mt-3">Anytime today (no fixed time)</h6>
        @foreach($anytimeTasks as $t)
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-1" style="border-top:1px solid var(--border)">
            <span>{{ $t->title }} <span class="text-muted-x small">· {{ $t->category }} · {{ $t->status }}</span></span>
            @if($t->status!=='Completed')<form method="POST" action="{{ route('tasks.complete', $t) }}">@csrf<button class="btn btn-sm btn-accent">Complete</button></form>@else<span class="pill pill-completed">Completed</span>@endif
          </div>
        @endforeach
      @endif
    </div>
  </div>

  <div class="col-12 col-lg-5">
    {{-- (e) Progress row --}}
    <div class="card">
      <h5>How am I doing today?</h5>
      <div class="text-center mb-3">
        <div class="ring" style="--p:{{ $taskPct }}"><div class="ring-inner">{{ $taskPct }}%</div></div>
        <p class="text-muted-x mb-0 mt-2">{{ $completedTasks ?? 0 }} of {{ $totalTasks ?? 0 }} tasks completed</p>
      </div>
      <div class="d-flex justify-content-between align-items-center mb-1"><span>Salah today</span><span class="text-muted-x small">{{ $prayerMarked }}/5 marked</span></div>
      <div class="salah-dots mb-1">@foreach(['Fajr','Dhuhr','Asr','Maghrib','Isha'] as $p)<span class="salah-dot {{ !empty($prayerRecords[$p]) ? 'done' : '' }} {{ (($nextPrayer['name'] ?? '')===$p && empty($prayerRecords[$p])) ? 'next' : '' }}" title="{{ $p }}"></span>@endforeach</div>
      @if(!empty($prayerTimes))<p class="text-muted-x small">Next prayer: <strong style="color:var(--text)">{{ $nextPrayer['name'] ?? '-' }}</strong> {{ isset($nextPrayer['time']) && $nextPrayer['time'] ? $nextPrayer['time']->format('h:i A') : '' }} {{ $nextPrayer['remaining'] ?? '' }}</p>@else<p class="text-muted-x small">Prayer times unavailable — check Salah Settings.</p>@endif
      <div class="d-flex justify-content-between mb-1"><span>Water</span><span class="text-muted-x small">{{ $waterToday ?? 0 }} / {{ $waterTarget }} ml</span></div>
      <div class="progress mb-3"><div class="progress-bar" style="width:{{ $waterPct }}%"></div></div>
      <p class="mb-1">Sleep last night: <strong>{{ ($latestSleep ?? null) && $latestSleep->duration_minutes ? round($latestSleep->duration_minutes/60,1).' hours' : 'Not recorded' }}</strong></p>
      <p class="mb-0">Walking today: <strong>{{ $walkingToday ?? 0 }} km</strong> · Exercise: <strong>{{ $exerciseToday ?? 0 }} min</strong> · Meals: <strong>{{ $mealsToday ?? 0 }}</strong>@if(($latestWeight ?? null)) · Weight: <strong>{{ $latestWeight->weight_kg }} kg</strong>@endif</p>
    </div>

    {{-- (g) Quick actions --}}
    <div class="card">
      <h5>Quick Actions</h5>
      <div class="qa-grid">
        <a class="btn btn-accent" href="{{ route('tasks.create') }}">Add Task</a>
        <form method="POST" action="{{ route('health.water.store') }}">@csrf<input type="hidden" name="date" value="{{ $todayStr }}"><input type="hidden" name="time" value="{{ $nowLocal->format('H:i') }}"><input type="hidden" name="quantity_ml" value="250"><input type="hidden" name="note" value="Quick add from Dashboard"><button class="btn btn-ghost w-100">Log Water +250 ml</button></form>
        <a class="btn btn-ghost" href="{{ route('meals.create') }}">Log Meal</a>
        <a class="btn btn-ghost" href="{{ route('health.weight.create') }}">Record Weight</a>
        <a class="btn btn-ghost" href="{{ route('salah.index') }}">Mark Prayer</a>
        <a class="btn btn-ghost" href="{{ route('sleep.index') }}">Sleep</a>
      </div>
    </div>

    <div class="card">
      <h5>Coming Up</h5>
      @forelse(($upcoming ?? collect()) as $t)
        <div class="d-flex justify-content-between py-1"><span>{{ $t->title }} <span class="text-muted-x small">({{ $t->category }})</span></span><span class="text-muted-x small">{{ $t->date->format('d M') }} {{ $t->start_time }}</span></div>
      @empty
        <div class="empty">No upcoming tasks yet. A task with a future date will appear here.</div>
      @endforelse
    </div>
  </div>
</div>

{{-- (f) Charts --}}
<h4 class="mt-2">Your Progress Graphs</h4>
<p class="text-muted-x">These graphs use only your real records. An empty graph means nothing has been logged for that period yet.</p>
<div class="row">
 <div class="col-12 col-lg-6"><div class="card"><h5>Tasks — Last 7 Days</h5><p class="text-muted-x">Completed tasks compared with total planned tasks, per day.</p><div class="chart-wrap"><canvas id="dashTasksChart"></canvas></div><p class="text-muted-x chart-note" id="dashTasksEmpty" style="display:none">No data yet — log your first task to see this graph.</p></div></div>
 <div class="col-12 col-lg-6"><div class="card"><h5>Water — Last 7 Days</h5><p class="text-muted-x">How much water (ml) you drank each day this week.</p><div class="chart-wrap"><canvas id="dashWaterChart"></canvas></div><p class="text-muted-x chart-note" id="dashWaterEmpty" style="display:none">No data yet — log your first water to see this graph.</p></div></div>
</div>
<div class="row">
 <div class="col-12 col-lg-6"><div class="card"><h5>Prayer Statuses — Last 30 Days</h5><p class="text-muted-x">How your marked prayers divide between Waqt par, Late, Qaza and Missed.</p><div class="chart-wrap"><canvas id="dashPrayerChart"></canvas></div><p class="text-muted-x chart-note" id="dashPrayerEmpty" style="display:none">No data yet — mark your prayers on the Salah page to see this graph.</p></div></div>
 <div class="col-12 col-lg-6"><div class="card"><h5>Weight Trend — Last 30 Days</h5><p class="text-muted-x">Your recorded weight over the last 30 days. Each point is one real entry.</p><div class="chart-wrap"><canvas id="dashWeightChart"></canvas></div><p class="text-muted-x chart-note" id="dashWeightEmpty" style="display:none">No data yet — record your weight in Health to see this graph.</p></div></div>
</div>

@push('scripts')<script>
mdosApplyChartDefaults();
var T=mdosChartTheme();
function dashEmpty(canvasId,noteId){ var c=document.getElementById(canvasId),n=document.getElementById(noteId); if(c)c.style.display='none'; if(n)n.style.display='block'; }
const PRAYER_LABELS={waqt_par:'Waqt par',late:'Late',qaza:'Qaza',missed:'Missed'};
fetch("{{ route('reports.chart') }}?from={{ now()->subDays(29)->toDateString() }}&to={{ now()->toDateString() }}",{headers:{'Accept':'application/json'}})
.then(function(r){ return r.json(); })
.then(function(data){
  var daily=(data && Array.isArray(data.daily)) ? data.daily.slice(-7) : null;
  if(daily && daily.length && daily.some(function(d){return (d.tasks_total||0)>0;})){
    new Chart(document.getElementById('dashTasksChart'),{type:'bar',data:{labels:daily.map(function(d){return (d.date||'').toString().slice(5,10);}),datasets:[{label:'Completed',data:daily.map(function(d){return d.tasks_completed||0;}),backgroundColor:T.accent},{label:'Total',data:daily.map(function(d){return d.tasks_total||0;}),backgroundColor:T.grid}]},options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true,ticks:{precision:0}}}}});
  } else { dashEmpty('dashTasksChart','dashTasksEmpty'); }
  if(daily && daily.length && daily.some(function(d){return (d.water_ml||0)>0;})){
    new Chart(document.getElementById('dashWaterChart'),{type:'line',data:{labels:daily.map(function(d){return (d.date||'').toString().slice(5,10);}),datasets:[{label:'Water (ml)',data:daily.map(function(d){return d.water_ml||0;}),borderColor:T.accent,backgroundColor:'rgba(139,203,155,0.15)',fill:true,tension:0.3}]},options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true}}}});
  } else { dashEmpty('dashWaterChart','dashWaterEmpty'); }
  var pr={}; var prRaw=(data && data.prayers) ? data.prayers : {};
  Object.keys(prRaw).forEach(function(k){ if(k && prRaw[k]>0) pr[PRAYER_LABELS[k]||k]=prRaw[k]; });
  if(Object.keys(pr).length){ new Chart(document.getElementById('dashPrayerChart'),{type:'doughnut',data:{labels:Object.keys(pr),datasets:[{data:Object.values(pr),backgroundColor:T.palette}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}}}}); }
  else { dashEmpty('dashPrayerChart','dashPrayerEmpty'); }
  var w=(data && Array.isArray(data.weights)) ? data.weights : [];
  if(w.length){ new Chart(document.getElementById('dashWeightChart'),{type:'line',data:{labels:w.map(function(x){return (x.date||'').toString().slice(0,10);}),datasets:[{label:'Weight (kg)',data:w.map(function(x){return x.weight_kg;}),borderColor:T.accent,backgroundColor:'rgba(139,203,155,0.15)',fill:true,tension:0.3}]},options:{responsive:true,maintainAspectRatio:false}}); }
  else { dashEmpty('dashWeightChart','dashWeightEmpty'); }
})
.catch(function(){ dashEmpty('dashTasksChart','dashTasksEmpty'); dashEmpty('dashWaterChart','dashWaterEmpty'); dashEmpty('dashPrayerChart','dashPrayerEmpty'); dashEmpty('dashWeightChart','dashWeightEmpty'); });
</script>@endpush
@endsection
