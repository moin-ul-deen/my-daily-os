<?php
namespace App\Http\Controllers;
use App\Models\WeightLog; use Illuminate\Http\Request;
class WeightController extends Controller {
 public function index(){ $items=WeightLog::where('user_id',auth()->id())->orderByDesc('date')->paginate(30); return view('health.weight.index',compact('items')); }
 public function create(){ return view('health.weight.create'); }
 public function store(Request $r){ $v=$r->validate(['date'=>'required|date','weight_kg'=>'required|numeric|min:20|max:500','note'=>'nullable|string']);  WeightLog::create(array_merge($v,['user_id'=>auth()->id()])); return redirect()->route('health.weight.index')->with('success','Saved'); }
 public function edit(WeightLog $weight){ abort_unless($weight->user_id===auth()->id(),403); $item=$weight; return view('health.weight.edit',compact('item')); }
 public function update(Request $r,WeightLog $weight){ abort_unless($weight->user_id===auth()->id(),403); $v=$r->validate(['date'=>'required|date','weight_kg'=>'required|numeric|min:20|max:500','note'=>'nullable|string']);  $weight->update($v); return redirect()->route('health.weight.index')->with('success','Updated'); }
 public function destroy($weight){ $weight=WeightLog::find($weight); if(!$weight) return redirect()->route('health.weight.index')->with('success','Already deleted'); abort_unless($weight->user_id===auth()->id(),403); $weight->delete(); return redirect()->route('health.weight.index')->with('success','Weight log deleted'); }
}
