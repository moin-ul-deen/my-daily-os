<?php
namespace App\Http\Controllers;
use App\Models\WaterLog; use Illuminate\Http\Request;
class WaterController extends Controller {
 public function index(){ $items=WaterLog::where('user_id',auth()->id())->orderByDesc('date')->paginate(30); return view('health.water.index',compact('items')); }
 public function create(){ return view('health.water.create'); }
 public function store(Request $r){ $v=$r->validate(['date'=>'required|date','quantity_ml'=>'required|integer|min:1|max:10000','time'=>'nullable','note'=>'nullable|string']);  WaterLog::create(array_merge($v,['user_id'=>auth()->id()])); return redirect()->route('health.water.index')->with('success','Saved'); }
 public function edit(WaterLog $water){ abort_unless($water->user_id===auth()->id(),403); $item=$water; return view('health.water.edit',compact('item')); }
 public function update(Request $r,WaterLog $water){ abort_unless($water->user_id===auth()->id(),403); $v=$r->validate(['date'=>'required|date','quantity_ml'=>'required|integer|min:1|max:10000','time'=>'nullable','note'=>'nullable|string']);  $water->update($v); return redirect()->route('health.water.index')->with('success','Updated'); }
 public function destroy($water){ $water=WaterLog::find($water); if(!$water) return redirect()->route('health.water.index')->with('success','Already deleted'); abort_unless($water->user_id===auth()->id(),403); $water->delete(); return redirect()->route('health.water.index')->with('success','Water log deleted'); }
}
