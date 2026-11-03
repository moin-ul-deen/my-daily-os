<?php
namespace App\Services;
use App\Models\PrayerSetting;
use App\Models\PrayerTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Pure-PHP prayer time calculation based on solar position (PrayTimes algorithm).
 * - Hanafi Asr: shadow factor 2 (Shafi/Maliki/Hanbali = 1)
 * - Fajr/Isha angles configurable via method presets.
 * Presets: Karachi 18/18, MWL 18/17, ISNA 15/15, Egypt 19.5/17.5, Makkah 18.5 / Isha = Maghrib+90min
 * No hardcoded prayer times - all computed from date, lat/lng, timezone.
 */
class PrayerTimeService {
 public const METHODS = [
   'Karachi'=>['fajr'=>18,'isha'=>18,'label'=>'University of Islamic Sciences, Karachi'],
   'MWL'=>['fajr'=>18,'isha'=>17,'label'=>'Muslim World League'],
   'ISNA'=>['fajr'=>15,'isha'=>15,'label'=>'ISNA (North America)'],
   'Egypt'=>['fajr'=>19.5,'isha'=>17.5,'label'=>'Egyptian General Authority of Survey'],
   'Makkah'=>['fajr'=>18.5,'isha'=>90,'label'=>'Umm al-Qura, Makkah (Isha = Maghrib + 90 min)'],
   'Custom'=>['fajr'=>18,'isha'=>18,'label'=>'Custom angles'],
 ];
 private float $lat; private float $lng; private float $tz; private float $jDate;
 private function dtr($d){ return $d*M_PI/180; } private function rtd($r){ return $r*180/M_PI; }
 private function sin($d){ return sin($this->dtr($d)); } private function cos($d){ return cos($this->dtr($d)); } private function tan($d){ return tan($this->dtr($d)); }
 private function arcsin($x){ return $this->rtd(asin($x)); } private function arccos($x){ return $this->rtd(acos($x)); } private function arctan2($y,$x){ return $this->rtd(atan2($y,$x)); } private function arccot($x){ return $this->rtd(atan(1/$x)); }
 private function fixAngle($a){ $a=$a-360*floor($a/360); return $a<0?$a+360:$a; } private function fixHour($a){ $a=$a-24*floor($a/24); return $a<0?$a+24:$a; }
 private function julian(int $y,int $m,int $d): float { if($m<=2){$y--;$m+=12;} $A=floor($y/100); $B=2-$A+floor($A/4); return floor(365.25*($y+4716))+floor(30.6001*($m+1))+$d+$B-1524.5; }
 private function sunPosition(float $jd): array { $D=$jd-2451545.0; $g=$this->fixAngle(357.529+0.98560028*$D); $q=$this->fixAngle(280.459+0.98564736*$D); $L=$this->fixAngle($q+1.915*$this->sin($g)+0.020*$this->sin(2*$g)); $e=23.439-0.00000036*$D; $RA=$this->fixHour($this->arctan2($this->cos($e)*$this->sin($L),$this->cos($L))/15); $decl=$this->arcsin($this->sin($e)*$this->sin($L)); $EqT=$q/15-$RA; return [$decl,$EqT]; }
 private function midDay(float $t): float { [, $EqT]=$this->sunPosition($this->jDate+$t); return $this->fixHour(12-$EqT); }
 private function sunAngleTime(float $angle,float $t,bool $ccw=false): float { [$decl,]=$this->sunPosition($this->jDate+$t); $noon=$this->midDay($t); $cosT=(-$this->sin($angle)-$this->sin($decl)*$this->sin($this->lat))/($this->cos($decl)*$this->cos($this->lat)); if($cosT<-1||$cosT>1) return NAN; $T=$this->arccos($cosT)/15; return $noon+($ccw?-$T:$T); }
 private function asrTime(int $factor,float $t): float { [$decl,]=$this->sunPosition($this->jDate+$t); $angle=-$this->arccot($factor+$this->tan(abs($this->lat-$decl))); return $this->sunAngleTime($angle,$t); }
 public function computeForDate(PrayerSetting $s, Carbon $date): array {
   $this->lat=(float)$s->latitude; $this->lng=(float)$s->longitude;
   // timezone offset for date in user's tz (handles DST if any)
   $tzObj=new \DateTimeZone($s->timezone ?: 'Asia/Karachi'); $this->tz=$tzObj->getOffset($date->toDateTime())/3600;
   $this->jDate=$this->julian($date->year,$date->month,$date->day)-$this->lng/(15*24);
   $method=self::METHODS[$s->calculation_method] ?? self::METHODS['Karachi'];
   $fajrAngle=(float)($s->fajr_angle ?: $method['fajr']); $ishaVal=$method['isha']; $ishaAngle=(float)($s->isha_angle ?: (is_numeric($ishaVal)?$ishaVal:18));
   $factor=((int)$s->asr_shadow_factor===2 || $s->asr_juristic==='Hanafi')?2:1;
   $times=[];
   $times['fajr']=$this->sunAngleTime($fajrAngle,0,true);
   $times['sunrise']=$this->sunAngleTime(0.833,0,true);
   $times['dhuhr']=$this->midDay(0.5);
   $times['asr']=$this->asrTime($factor,0.5);
   $times['maghrib']=$this->sunAngleTime(0.833,0.5);
   if($s->calculation_method==='Makkah'){ $times['isha']=$times['maghrib']+1.5; } else { $times['isha']=$this->sunAngleTime($ishaAngle,0.5); }
   // High latitude AngleBased fallback when NAN
   foreach(['fajr','isha'] as $k){ if(is_nan($times[$k])){ $angle=$k==='fajr'?$fajrAngle:$ishaAngle; $portion=$angle/60; $night=$this->fixHour($times['sunrise']-$times['maghrib']+24); // approx
     $times[$k]=$k==='fajr'? $times['sunrise']-$portion*( $this->fixHour(24-$times['maghrib']+$times['sunrise']) ) : $times['maghrib']+$portion*( $this->fixHour(24-$times['maghrib']+$times['sunrise']) ); } }
   $adj=is_array($s->adjustments)?$s->adjustments:[];
   $out=[];
   foreach($times as $k=>$v){ $mins=($adj[$k]??0); $total=$this->fixHour($v+$this->tz-$this->lng/15 + $mins/60); $h=intval(floor($total)); $m=intval(floor(($total-$h)*60)); $out[$k]=sprintf('%02d:%02d:00',$h,$m); }
   return $out;
 }
 public function getOrCreateForUser($user, Carbon $date): PrayerTime {
   $existing=PrayerTime::where('user_id',$user->id)->whereDate('date',$date)->first(); if($existing) return $existing;
   $s=$user->prayerSetting ?? PrayerSetting::create(['user_id'=>$user->id]);
   try{ $t=$this->computeForDate($s,$date->copy()); } catch(\Throwable $e){ \Log::warning('Prayer calc failed: '.$e->getMessage()); $last=PrayerTime::where('user_id',$user->id)->latest('date')->first(); if($last){ return $last; } throw $e; }
   return PrayerTime::create(['user_id'=>$user->id,'date'=>$date->toDateString(),'fajr'=>$t['fajr'],'sunrise'=>$t['sunrise'],'dhuhr'=>$t['dhuhr'],'asr'=>$t['asr'],'maghrib'=>$t['maghrib'],'isha'=>$t['isha'],'calculation_method'=>$s->calculation_method,'source'=>'calculated']);
 }
 public function nextPrayer(PrayerTime $pt, Carbon $now): array {
   $prayers=['Fajr'=>$pt->fajr,'Dhuhr'=>$pt->dhuhr,'Asr'=>$pt->asr,'Maghrib'=>$pt->maghrib,'Isha'=>$pt->isha];
   foreach($prayers as $name=>$time){ $dt=Carbon::parse($pt->date->toDateString().' '.$time, $now->timezone); if($dt->gt($now)) return ['name'=>$name,'time'=>$dt,'remaining'=>$now->diffForHumans($dt,true)]; }
   return ['name'=>'Fajr (tomorrow)','time'=>Carbon::parse($pt->date->addDay()->toDateString().' '.$pt->fajr),'remaining'=>'tomorrow'];
 }
}
