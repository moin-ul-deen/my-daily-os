<?php
namespace App\Http\Controllers;
use App\Models\WalkingLog; use Illuminate\Http\Request;
class WalkingController extends Controller {
 public function index(){ $items=WalkingLog::where('user_id',auth()->id())->orderByDesc('date')->paginate(30); return view('health.walking.index',compact('items')); }
 public function create(){ return view('health.walking.create'); }
 public function store(Request $r){ $v=$r->validate(['date'=>'required|date','distance_km'=>'required|numeric|min:0','duration_minutes'=>'nullable|integer','steps'=>'nullable|integer','calories'=>'nullable|integer','notes'=>'nullable|string']);  WalkingLog::create(array_merge($v,['user_id'=>auth()->id()])); return redirect()->route('health.walking.index')->with('success','Saved'); }
 public function edit(WalkingLog $walking){ abort_unless($walking->user_id===auth()->id(),403); $item=$walking; return view('health.walking.edit',compact('item')); }
 public function update(Request $r,WalkingLog $walking){ abort_unless($walking->user_id===auth()->id(),403); $v=$r->validate(['date'=>'required|date','distance_km'=>'required|numeric|min:0','duration_minutes'=>'nullable|integer','steps'=>'nullable|integer','calories'=>'nullable|integer','notes'=>'nullable|string']);  $walking->update($v); return redirect()->route('health.walking.index')->with('success','Updated'); }
 public function destroy($walking){ $walking=WalkingLog::find($walking); if(!$walking) return redirect()->route('health.walking.index')->with('success','Already deleted'); abort_unless($walking->user_id===auth()->id(),403); $walking->delete(); return redirect()->route('health.walking.index')->with('success','Walking log deleted'); }
}
