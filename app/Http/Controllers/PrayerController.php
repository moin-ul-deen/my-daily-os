<?php
namespace App\Http\Controllers;
use App\Models\{PrayerRecord,PrayerTime}; use App\Services\PrayerTimeService; use Carbon\Carbon; use Illuminate\Http\Request;
class PrayerController extends Controller {
 public function index(Request $r, PrayerTimeService $svc){ $u=$r->user(); $date=$r->date?Carbon::parse($r->date):Carbon::now($u->timezone?:'Asia/Karachi'); $pt=null; try{$pt=$svc->getOrCreateForUser($u,$date);}catch(\Throwable $e){}
  $records=PrayerRecord::where('user_id',$u->id)->whereDate('prayer_date',$date->toDateString())->pluck('status','prayer_name');
  $week=PrayerRecord::where('user_id',$u->id)->whereBetween('prayer_date',[$date->copy()->startOfWeek(),$date->copy()->endOfWeek()])->get();
  $month=PrayerRecord::where('user_id',$u->id)->whereYear('prayer_date',$date->year)->whereMonth('prayer_date',$date->month)->get();
  return view('salah.index',compact('pt','records','date','week','month')); }
 public function updateStatus(Request $r, PrayerTimeService $svc){ $v=$r->validate(['prayer_name'=>'required|in:Fajr,Dhuhr,Asr,Maghrib,Isha','prayer_date'=>'required|date','status'=>'nullable|in:waqt_par,late,qaza,missed','actual_time'=>'nullable','note'=>'nullable|string']);
  $u=$r->user(); $pt=null; try{$pt=$svc->getOrCreateForUser($u,Carbon::parse($v['prayer_date']));}catch(\Throwable $e){}
  $scheduled=$pt? $pt->{strtolower($v['prayer_name'])} : null;
  if(empty($v['status'])){ PrayerRecord::where('user_id',$u->id)->where('prayer_name',$v['prayer_name'])->whereDate('prayer_date',$v['prayer_date'])->delete(); }
  else { PrayerRecord::updateOrCreate(['user_id'=>$u->id,'prayer_name'=>$v['prayer_name'],'prayer_date'=>$v['prayer_date']],['scheduled_time'=>$scheduled,'actual_time'=>$v['actual_time']??null,'status'=>$v['status'],'note'=>$v['note']??null]); }
  return back()->with('success','Prayer status saved'); }
 public function history(Request $r){ $records=PrayerRecord::where('user_id',auth()->id())->orderByDesc('prayer_date')->paginate(50); return view('salah.history',compact('records')); }
}