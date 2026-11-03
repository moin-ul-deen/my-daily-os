@extends('layouts.app')
@section('content')
<h3>Weekly Report</h3>
<p class="text-muted-x">{{ $from->format('d M Y') }} – {{ $to->format('d M Y') }} (this week)</p>
<div class="d-flex flex-wrap gap-1 mb-3">@foreach([['reports.daily','Daily'],['reports.weekly','Weekly'],['reports.monthly','Monthly'],['reports.yearly','Yearly'],['reports.custom','Custom']] as $n)<a href="{{ route($n[0]) }}" class="btn btn-sm {{ request()->routeIs($n[0]) ? 'btn-accent' : 'btn-ghost' }}">{{ $n[1] }}</a>@endforeach</div>
<div class="card intro-card"><p class="mb-0"><strong>Your current week at a glance, compared with last week.</strong> The comparison table below the graphs shows both weeks side by side, so you can see whether this week is stronger or slower — and adjust next week.</p></div>

@if(!empty($report['insights']))<div class="card"><h5>What this means</h5><ul class="mb-2">@foreach($report['insights'] as $ins)<li>{{ $ins }}</li>@endforeach</ul><p class="text-muted-x small mb-0">These lines come only from your real records in this period — nothing is estimated or filled in.</p></div>@endif
<div class="row">
<div class="col-6 col-lg-3"><div class="card"><h6>Tasks</h6><div class="stat">{{ $report['tasks']['completed'] }}/{{ $report['tasks']['total'] }}</div><p class="text-muted-x mb-0">Completed of total planned in this period.</p></div></div>
<div class="col-6 col-lg-3"><div class="card"><h6>Prayers marked</h6><div class="stat">{{ collect($report['prayers'] ?? [])->sum() }}</div><p class="text-muted-x mb-0">Prayers you gave a status to (of 5 per day).</p></div></div>
<div class="col-6 col-lg-3"><div class="card"><h6>Water</h6><div class="stat">{{ $report['water_ml'] }} ml</div><p class="text-muted-x mb-0">Total water you logged.</p></div></div>
<div class="col-6 col-lg-3"><div class="card"><h6>Walking</h6><div class="stat">{{ $report['walking_km'] }} km</div><p class="text-muted-x mb-0">Total walking distance.</p></div></div>
</div>
<div class="row">
<div class="col-12 col-lg-6"><div class="card"><h5>Prayer Distribution</h5><p class="text-muted-x">How your marked prayers divide between Waqt par, Late, Qaza and Missed in this period. Unmarked prayers are not counted.</p><div class="chart-box chart-doughnut"><canvas id="prayerChart"></canvas></div><p class="text-muted-x chart-note" id="prayerEmpty" style="display:none">No prayers marked in this period yet — mark prayers on the Salah page and this graph will appear.</p></div></div>
<div class="col-12 col-lg-6"><div class="card"><h5>Income vs Expenses</h5><p class="text-muted-x">Money in and money out for this period, from your Finance entries.</p><div class="chart-box"><canvas id="financeChart"></canvas></div><p class="mb-1">Income: <strong>{{ $report['income'] }}</strong> · Expense: <strong>{{ $report['expense'] }}</strong> · Net: <strong>{{ $report['income']-$report['expense'] }}</strong> · Freelance net: <strong>{{ $report['freelance_income'] }}</strong></p><p class="text-muted-x chart-note" id="financeEmpty" style="display:none">No income or expenses in this period yet — add entries in Finance and this graph will appear.</p></div></div>
</div>
<div class="row">
<div class="col-12 col-lg-6"><div class="card"><h5>Expenses by Category</h5><p class="text-muted-x">Where your money went in this period. Each slice is one expense category, for example Food or Transport.</p><div class="chart-box chart-doughnut"><canvas id="expenseChart"></canvas></div><p class="text-muted-x chart-note" id="expenseEmpty" style="display:none">No expenses in this period yet — log an expense in Finance and this graph will appear.</p></div></div>
<div class="col-12 col-lg-6"><div class="card"><h5>Weight Trend</h5><p class="text-muted-x">Your recorded weight over this period. Each point is one real entry from Health → Weight.</p><div class="chart-box"><canvas id="weightChart"></canvas></div><p class="text-muted-x chart-note" id="weightEmpty" style="display:none">No weight recorded in this period yet — add your weight in Health and this graph will appear.</p></div></div>
</div>
<div class="card"><h5>This week vs last week</h5><p class="text-muted-x">The same measures for both weeks, side by side — this is the honest way to see if your routine is improving.</p><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Measure</th><th>This week</th><th>Last week</th></tr></thead><tbody>
<tr><td>Tasks completed</td><td>{{ $report['tasks']['completed'] }} of {{ $report['tasks']['total'] }}</td><td>{{ $prev['tasks']['completed'] }} of {{ $prev['tasks']['total'] }}</td></tr>
<tr><td>Water</td><td>{{ $report['water_ml'] }} ml</td><td>{{ $prev['water_ml'] }} ml</td></tr>
<tr><td>Walking</td><td>{{ $report['walking_km'] }} km</td><td>{{ $prev['walking_km'] }} km</td></tr>
<tr><td>Learning</td><td>{{ $report['learning_minutes'] }} min</td><td>{{ $prev['learning_minutes'] }} min</td></tr>
<tr><td>Income</td><td>{{ $report['income'] }}</td><td>{{ $prev['income'] }}</td></tr>
<tr><td>Expenses</td><td>{{ $report['expense'] }}</td><td>{{ $prev['expense'] }}</td></tr>
</tbody></table></div></div>
<div class="card"><h5>All numbers for this period</h5><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Measure</th><th>Your record</th></tr></thead><tbody>
<tr><td>Tasks completed</td><td>{{ $report['tasks']['completed'] }} of {{ $report['tasks']['total'] }} planned</td></tr>
<tr><td>Prayers marked</td><td>{{ collect($report['prayers'] ?? [])->sum() }} (Waqt par {{ $report['prayers']['waqt_par'] ?? 0 }} · Late {{ $report['prayers']['late'] ?? 0 }} · Qaza {{ $report['prayers']['qaza'] ?? 0 }} · Missed {{ $report['prayers']['missed'] ?? 0 }})</td></tr>
<tr><td>Water</td><td>{{ $report['water_ml'] }} ml</td></tr>
<tr><td>Walking</td><td>{{ $report['walking_km'] }} km</td></tr>
<tr><td>Exercise</td><td>{{ $report['exercise_sessions'] }} sessions · {{ $report['exercise_minutes'] }} minutes</td></tr>
<tr><td>Sleep (average)</td><td>{{ !empty($report['sleep_minutes_avg']) ? round($report['sleep_minutes_avg']/60,1).' hours per night' : 'Not recorded' }}</td></tr>
<tr><td>Meals logged</td><td>{{ $report['meals'] }}</td></tr>
<tr><td>Learning</td><td>{{ $report['learning_minutes'] }} minutes</td></tr>
<tr><td>Income</td><td>{{ $report['income'] }}</td></tr>
<tr><td>Expenses</td><td>{{ $report['expense'] }}</td></tr>
<tr><td>Freelance income (net)</td><td>{{ $report['freelance_income'] }}</td></tr>
</tbody></table></div></div>
@push('scripts')<script>
mdosApplyChartDefaults(); var T=mdosChartTheme();
function chartEmpty(canvasId,noteId,empty){ if(!empty) return false; var c=document.getElementById(canvasId),n=document.getElementById(noteId); if(c)c.style.display='none'; if(n)n.style.display='block'; return true; }
const PL={waqt_par:'Waqt par',late:'Late',qaza:'Qaza',missed:'Missed'};
const prRaw=@json($report['prayers'] ?? []); const pr={}; Object.keys(prRaw||{}).forEach(function(k){ if(k && prRaw[k]>0) pr[PL[k]||k]=prRaw[k]; });
if(!chartEmpty('prayerChart','prayerEmpty',Object.keys(pr).length===0)){ new Chart(document.getElementById('prayerChart'),{type:'doughnut',data:{labels:Object.keys(pr),datasets:[{data:Object.values(pr),backgroundColor:T.palette}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}}}}); }
const inc={{ $report['income'] ?? 0 }}, exp={{ $report['expense'] ?? 0 }};
if(!chartEmpty('financeChart','financeEmpty',inc===0&&exp===0)){ new Chart(document.getElementById('financeChart'),{type:'bar',data:{labels:['Income','Expense'],datasets:[{data:[inc,exp],backgroundColor:[T.accent,'#D87979']}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}}); }
const ex=@json($report['expense_by_category'] ?? []);
if(!chartEmpty('expenseChart','expenseEmpty',!ex||Object.keys(ex).length===0)){ new Chart(document.getElementById('expenseChart'),{type:'doughnut',data:{labels:Object.keys(ex),datasets:[{data:Object.values(ex),backgroundColor:T.palette}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}}}}); }
const w=@json($report['weights'] ?? []);
if(!chartEmpty('weightChart','weightEmpty',!w||w.length===0)){ new Chart(document.getElementById('weightChart'),{type:'line',data:{labels:w.map(function(x){return (x.date||'').toString().slice(0,10);}),datasets:[{label:'Weight (kg)',data:w.map(function(x){return x.weight_kg;}),borderColor:T.accent,backgroundColor:'rgba(139,203,155,0.15)',fill:true,tension:0.3}]},options:{responsive:true,maintainAspectRatio:false}}); }
</script>@endpush
@endsection
