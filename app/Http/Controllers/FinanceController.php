<?php
namespace App\Http\Controllers;
use App\Models\FinancialRecord; use Illuminate\Http\Request;
class FinanceController extends Controller {
 public function index(){ $items=FinancialRecord::where('user_id',auth()->id())->orderByDesc('date')->paginate(30); return view('finance.index',compact('items')); }
 public function create(){ return view('finance.create'); }
 public function store(Request $r){ $v=$r->validate(['type'=>'required|in:income,expense','category'=>'required|string','amount'=>'required|numeric|min:0','currency'=>'nullable|string','date'=>'required|date','payment_method'=>'nullable|string','description'=>'nullable|string','is_recurring'=>'nullable|boolean','recurrence'=>'nullable|string']);  FinancialRecord::create(array_merge($v,['user_id'=>auth()->id()])); return redirect()->route('finance.index')->with('success','Saved'); }
 public function edit(FinancialRecord $financial){ abort_unless($financial->user_id===auth()->id(),403); $item=$financial; return view('finance.edit',compact('item')); }
 public function update(Request $r,FinancialRecord $financial){ abort_unless($financial->user_id===auth()->id(),403); $v=$r->validate(['type'=>'required|in:income,expense','category'=>'required|string','amount'=>'required|numeric|min:0','currency'=>'nullable|string','date'=>'required|date','payment_method'=>'nullable|string','description'=>'nullable|string','is_recurring'=>'nullable|boolean','recurrence'=>'nullable|string']);  $financial->update($v); return redirect()->route('finance.index')->with('success','Updated'); }
 public function destroy($financial){ $financial=FinancialRecord::withTrashed()->find($financial); if(!$financial) return redirect()->route('finance.index')->with('success','Already deleted'); abort_unless($financial->user_id===auth()->id(),403); if($financial->trashed()) return redirect()->route('finance.index')->with('success','Already deleted'); $financial->delete(); return redirect()->route('finance.index')->with('success','Record deleted'); }
}
