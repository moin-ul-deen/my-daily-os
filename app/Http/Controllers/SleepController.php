<?php
namespace App\Http\Controllers; use App\Models\SleepRecord; use Carbon\Carbon; use Illuminate\Http\Request;
class SleepController extends Controller {
 public function index(){ $items=SleepRecord::where('user_id',auth()->id())->orderByDesc('sleep_start')->paginate(30); $active=SleepRecord::where('user_id',auth()->id())->whereNull('wake_time')->first(); return view('sleep.index',compact('items','active')); }
 public function create(){ return view('sleep.create'); }
 public function store(Request $r){ $v=$r->validate(['sleep_start'=>'required|date','wake_time'=>'nullable|date|after:sleep_start','quality_rating'=>'nullable|integer|between:1,5','notes'=>'nullable|string']);
  $overlap=SleepRecord::where('user_id',auth()->id())->where(function($q)use($v){ $q->whereBetween('sleep_start',[$v['sleep_start'],$v['wake_time']??$v['sleep_start']])->orWhereNull('wake_time'); })->exists(); // simple overlap guard for open sessions checked below
  if(SleepRecord::where('user_id',auth()->id())->whereNull('wake_time')->exists() && empty($v['wake_time'])) return back()->withErrors(['sleep_start'=>'An open sleep session already exists. End it first.']);
  $dur=null; if(!empty($v['wake_time'])) $dur=Carbon::parse($v['sleep_start'])->diffInMinutes(Carbon::parse($v['wake_time']));
  SleepRecord::create(array_merge($v,['user_id'=>auth()->id(),'duration_minutes'=>$dur])); return redirect()->route('sleep.index')->with('success','Sleep saved'); }
 public function start(){ if(SleepRecord::where('user_id',auth()->id())->whereNull('wake_time')->exists()) return back()->withErrors(['sleep'=>'Already sleeping session open']); SleepRecord::create(['user_id'=>auth()->id(),'sleep_start'=>now()]); return back(); }
 public function end(SleepRecord $sleep){ abort_unless($sleep->user_id===auth()->id(),403); $sleep->update(['wake_time'=>now(),'duration_minutes'=>Carbon::parse($sleep->sleep_start)->diffInMinutes(now())]); return back(); }
 public function edit(SleepRecord $sleep){ abort_unless($sleep->user_id===auth()->id(),403); $item=$sleep; return view('sleep.edit',compact('item')); }
 public function update(Request $r,SleepRecord $sleep){ abort_unless($sleep->user_id===auth()->id(),403); $v=$r->validate(['sleep_start'=>'required|date','wake_time'=>'nullable|date|after:sleep_start','quality_rating'=>'nullable|integer|between:1,5','notes'=>'nullable|string']); if(!empty($v['wake_time'])) $v['duration_minutes']=Carbon::parse($v['sleep_start'])->diffInMinutes(Carbon::parse($v['wake_time'])); $sleep->update($v); return redirect()->route('sleep.index'); }
 public function destroy($sleep){ $sleep=SleepRecord::find($sleep); if(!$sleep) return redirect()->route('sleep.index')->with('success','Already deleted'); abort_unless($sleep->user_id===auth()->id(),403); $sleep->delete(); return redirect()->route('sleep.index')->with('success','Sleep record deleted'); }
}