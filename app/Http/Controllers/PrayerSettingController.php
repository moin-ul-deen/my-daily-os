<?php
namespace App\Http\Controllers; use App\Models\PrayerTime; use App\Services\PrayerTimeService; use Illuminate\Http\Request;
class PrayerSettingController extends Controller {
 public function edit(Request $r){ return view('salah.settings',['setting'=>$r->user()->prayerSetting,'methods'=>PrayerTimeService::METHODS]); }
 public function update(Request $r){ $v=$r->validate(['city'=>'required|string','country'=>'required|string','latitude'=>'required|numeric|between:-90,90','longitude'=>'required|numeric|between:-180,180','timezone'=>'required|string','calculation_method'=>'required|in:Karachi,MWL,ISNA,Egypt,Makkah,Custom','fajr_angle'=>'nullable|numeric','isha_angle'=>'nullable|numeric','asr_juristic'=>'required|in:Hanafi,Standard','high_latitude_rule'=>'nullable|string','adj_fajr'=>'nullable|integer','adj_dhuhr'=>'nullable|integer','adj_asr'=>'nullable|integer','adj_maghrib'=>'nullable|integer','adj_isha'=>'nullable|integer']);
  $adj=['fajr'=>$v['adj_fajr']??0,'dhuhr'=>$v['adj_dhuhr']??0,'asr'=>$v['adj_asr']??0,'maghrib'=>$v['adj_maghrib']??0,'isha'=>$v['adj_isha']??0];
  $factor=$v['asr_juristic']==='Hanafi'?2:1;
  $preset=PrayerTimeService::METHODS[$v['calculation_method']]??null; if($preset && empty($v['fajr_angle'])) $v['fajr_angle']=$preset['fajr']; if($preset && empty($v['isha_angle']) && is_numeric($preset['isha'])) $v['isha_angle']=$preset['isha'];
  $r->user()->prayerSetting->update(array_merge($v,['adjustments'=>$adj,'asr_shadow_factor'=>$factor]));
  PrayerTime::where('user_id',$r->user()->id)->whereDate('date','>=',now()->toDateString())->delete(); // recalc future
  return back()->with('success','Prayer settings saved. Future times will be recalculated.'); }
}