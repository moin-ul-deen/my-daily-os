<?php
namespace App\Http\Controllers; use App\Models\{Meal,MealItem}; use Illuminate\Http\Request; use Illuminate\Support\Facades\DB;
class MealController extends Controller {
 public function index(){ $items=Meal::with('items')->where('user_id',auth()->id())->orderByDesc('date')->paginate(30); return view('meals.index',compact('items')); }
 public function create(){ return view('meals.create'); }
 public function store(Request $r){ $v=$r->validate(['meal_type'=>'required|string','date'=>'required|date','time'=>'nullable','is_home_cooked'=>'nullable|boolean','notes'=>'nullable|string','items'=>'nullable|array','items.*.food_name'=>'required_with:items|string','items.*.quantity'=>'nullable|string','items.*.calories'=>'nullable|integer','items.*.protein_g'=>'nullable|numeric','items.*.carbs_g'=>'nullable|numeric','items.*.fats_g'=>'nullable|numeric','food_name'=>'nullable|string']);
  DB::transaction(function()use($v){ $m=Meal::create(['user_id'=>auth()->id(),'meal_type'=>$v['meal_type'],'date'=>$v['date'],'time'=>$v['time']??null,'is_home_cooked'=>$v['is_home_cooked']??true,'notes'=>$v['notes']??null]);
   $items=$v['items']??[]; if(empty($items) && !empty($v['food_name'])) $items=[['food_name'=>$v['food_name']]];
   foreach($items as $it){ if(!empty($it['food_name'])) MealItem::create(array_merge($it,['meal_id'=>$m->id,'user_id'=>auth()->id()])); } });
  return redirect()->route('meals.index')->with('success','Meal saved'); }
 public function edit(Meal $meal){ abort_unless($meal->user_id===auth()->id(),403); $item=$meal; return view('meals.edit',compact('item')); }
 public function update(Request $r,Meal $meal){ abort_unless($meal->user_id===auth()->id(),403); $v=$r->validate(['meal_type'=>'required|string','date'=>'required|date','time'=>'nullable','is_home_cooked'=>'nullable|boolean','notes'=>'nullable|string']); $meal->update($v); return redirect()->route('meals.index'); }
 public function destroy($meal){ $meal=Meal::find($meal); if(!$meal) return redirect()->route('meals.index')->with('success','Already deleted'); abort_unless($meal->user_id===auth()->id(),403); DB::transaction(function() use ($meal){ $meal->items()->delete(); $meal->delete(); }); return redirect()->route('meals.index')->with('success','Meal deleted'); }
}