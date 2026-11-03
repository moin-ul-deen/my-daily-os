<?php
namespace App\Http\Controllers; use App\Models\{Habit,HabitLog}; use Carbon\Carbon; use Illuminate\Http\Request;
class HabitController extends Controller {
 public function index(Request $r){ $date=$r->date??now()->toDateString(); $habits=Habit::where('user_id',auth()->id())->get(); $logs=HabitLog::where('user_id',auth()->id())->whereDate('date',$date)->pluck('completed','habit_id');
   // Stats for streaks, weekly progress and the 84-day consistency grid - one logs query for the whole window.
   $today=Carbon::today(); $since=$today->copy()->subDays(83);
   $allLogs=HabitLog::where('user_id',auth()->id())->whereDate('date','>=',$since->toDateString())->whereDate('date','<=',$today->toDateString())->get()->groupBy('habit_id');
   $habitStats=[];
   foreach($habits as $h){ $map=[]; foreach(($allLogs[$h->id]??collect()) as $l){ $map[Carbon::parse($l->date)->toDateString()]=(bool)$l->completed; }
     $last84=[]; for($d=$since->copy(); $d->lte($today); $d->addDay()){ $k=$d->toDateString(); $last84[]=['date'=>$k,'completed'=>$map[$k]??false]; }
     $cursor=$today->copy(); if(empty($map[$cursor->toDateString()])) $cursor->subDay(); $streak=0; while(!empty($map[$cursor->toDateString()])){ $streak++; $cursor->subDay(); }
     $mon=$today->copy()->startOfWeek(Carbon::MONDAY); $sun=$today->copy()->endOfWeek(Carbon::SUNDAY); $weekDone=0; foreach($map as $k=>$v){ if($v && $k>=$mon->toDateString() && $k<=$sun->toDateString()) $weekDone++; }
     $weekTarget=null; if(is_numeric($h->target)) $weekTarget=(int)$h->target; elseif($h->frequency && preg_match('/(\d+)/',(string)$h->frequency,$mm)) $weekTarget=(int)$mm[1];
     $habitStats[$h->id]=['streak'=>$streak,'week_done'=>$weekDone,'week_target'=>$weekTarget,'last_84'=>$last84]; }
   return view('habits.index',compact('habits','logs','date','habitStats')); }
 public function create(){ return view('habits.create'); }
 public function store(Request $r){ $v=$r->validate(['name'=>'required|string','description'=>'nullable|string','category'=>'nullable|string','frequency'=>'required|string','target'=>'nullable|string','start_date'=>'required|date','end_date'=>'nullable|date']); Habit::create(array_merge($v,['user_id'=>auth()->id()])); return redirect()->route('habits.index'); }
 public function edit(Habit $habit){ abort_unless($habit->user_id===auth()->id(),403); $item=$habit; return view('habits.edit',compact('item')); }
 public function update(Request $r,Habit $habit){ abort_unless($habit->user_id===auth()->id(),403); $v=$r->validate(['name'=>'required|string','description'=>'nullable|string','category'=>'nullable|string','frequency'=>'required|string','target'=>'nullable|string','start_date'=>'required|date','end_date'=>'nullable|date','is_active'=>'nullable|boolean']); $habit->update($v); return redirect()->route('habits.index'); }
 public function destroy($habit){ $habit=Habit::withTrashed()->find($habit); if(!$habit) return redirect()->route('habits.index')->with('success','Already deleted'); abort_unless($habit->user_id===auth()->id(),403); if($habit->trashed()) return redirect()->route('habits.index')->with('success','Already deleted'); $habit->delete(); return redirect()->route('habits.index')->with('success','Habit deleted'); }
 public function toggle(Request $r,Habit $habit){ abort_unless($habit->user_id===auth()->id(),403); $date=$r->date??now()->toDateString(); $log=HabitLog::where('habit_id',$habit->id)->whereDate('date',$date)->first(); if($log) $log->update(['completed'=>!$log->completed]); else HabitLog::create(['user_id'=>auth()->id(),'habit_id'=>$habit->id,'date'=>$date,'completed'=>true]); return back(); }
}