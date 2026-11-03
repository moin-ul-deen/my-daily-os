<?php
namespace App\Http\Controllers;
use App\Models\FreelanceIncome; use Illuminate\Http\Request;
class FreelanceIncomeController extends Controller {
 public function index(){ $items=FreelanceIncome::where('user_id',auth()->id())->orderByDesc('date')->paginate(30); return view('freelancing.income.index',compact('items')); }
 public function create(){ return view('freelancing.income.create'); }
 public function store(Request $r){ $v=$r->validate(['date'=>'required|date','project_id'=>'nullable|exists:projects,id','platform'=>'nullable|string','gross_income'=>'required|numeric|min:0','fees'=>'nullable|numeric|min:0','currency'=>'nullable|string','exchange_rate'=>'nullable|numeric','payment_status'=>'required|string','notes'=>'nullable|string']);  $v['net_income']=($v['gross_income']??0)-($v['fees']??0); $v['currency']=$v['currency']??'PKR'; FreelanceIncome::create(array_merge($v,['user_id'=>auth()->id()])); return redirect()->route('freelancing.income.index')->with('success','Saved'); }
 public function edit(FreelanceIncome $income){ abort_unless($income->user_id===auth()->id(),403); $item=$income; return view('freelancing.income.edit',compact('item')); }
 public function update(Request $r,FreelanceIncome $income){ abort_unless($income->user_id===auth()->id(),403); $v=$r->validate(['date'=>'required|date','project_id'=>'nullable|exists:projects,id','platform'=>'nullable|string','gross_income'=>'required|numeric|min:0','fees'=>'nullable|numeric|min:0','currency'=>'nullable|string','exchange_rate'=>'nullable|numeric','payment_status'=>'required|string','notes'=>'nullable|string']);  $v['net_income']=($v['gross_income']??0)-($v['fees']??0); $v['currency']=$v['currency']??'PKR'; $income->update($v); return redirect()->route('freelancing.income.index')->with('success','Updated'); }
 public function destroy($income){ $income=FreelanceIncome::find($income); if(!$income) return redirect()->route('freelancing.income.index')->with('success','Already deleted'); abort_unless($income->user_id===auth()->id(),403); $income->delete(); return redirect()->route('freelancing.income.index')->with('success','Income record deleted'); }
}
