<?php
namespace App\Http\Controllers;
use App\Models\ExerciseLog; use Illuminate\Http\Request;
class ExerciseController extends Controller {
 public function index(){ $items=ExerciseLog::where('user_id',auth()->id())->orderByDesc('date')->paginate(30); return view('health.exercise.index',compact('items')); }
 public function create(){ return view('health.exercise.create'); }
 public function store(Request $r){ $v=$r->validate(['exercise_type'=>'required|string','date'=>'required|date','duration_minutes'=>'nullable|integer','sets'=>'nullable|integer','repetitions'=>'nullable|integer','weight_kg'=>'nullable|numeric','notes'=>'nullable|string']);  ExerciseLog::create(array_merge($v,['user_id'=>auth()->id()])); return redirect()->route('health.exercise.index')->with('success','Saved'); }
 public function edit(ExerciseLog $exercise){ abort_unless($exercise->user_id===auth()->id(),403); $item=$exercise; return view('health.exercise.edit',compact('item')); }
 public function update(Request $r,ExerciseLog $exercise){ abort_unless($exercise->user_id===auth()->id(),403); $v=$r->validate(['exercise_type'=>'required|string','date'=>'required|date','duration_minutes'=>'nullable|integer','sets'=>'nullable|integer','repetitions'=>'nullable|integer','weight_kg'=>'nullable|numeric','notes'=>'nullable|string']);  $exercise->update($v); return redirect()->route('health.exercise.index')->with('success','Updated'); }
 public function destroy($exercise){ $exercise=ExerciseLog::find($exercise); if(!$exercise) return redirect()->route('health.exercise.index')->with('success','Already deleted'); abort_unless($exercise->user_id===auth()->id(),403); $exercise->delete(); return redirect()->route('health.exercise.index')->with('success','Exercise log deleted'); }
}
