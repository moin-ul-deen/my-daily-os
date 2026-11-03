<?php
namespace App\Http\Controllers;
use App\Models\{Task,PrayerRecord,WaterLog,Meal,WalkingLog,ExerciseLog,WeightLog,SleepRecord};
use App\Services\{OnboardingService,PrayerTimeService};
use Carbon\Carbon;
use Illuminate\Http\Request;
class DashboardController extends Controller {
 /** Normalise stored working_days (ints 1-7 ISO, 0=Sunday, or day names) to ISO day numbers (Mon=1..Sun=7). */
 private function workingDayIsos($days): array {
   if (!is_array($days) || empty($days)) return [1,2,3,4,5];
   $names=['sun'=>7,'sunday'=>7,'mon'=>1,'monday'=>1,'tue'=>2,'tues'=>2,'tuesday'=>2,'wed'=>3,'wednesday'=>3,'thu'=>4,'thur'=>4,'thurs'=>4,'thursday'=>4,'fri'=>5,'friday'=>5,'sat'=>6,'saturday'=>6];
   $out=[];
   foreach ($days as $d) {
     if (is_int($d) || (is_string($d) && ctype_digit($d))) { $n=(int)$d; if ($n>=1 && $n<=7) $out[]=$n; elseif ($n===0) $out[]=7; }
     elseif (is_string($d)) { $k=strtolower(trim($d)); if (isset($names[$k])) $out[]=$names[$k]; }
   }
   return $out ?: [1,2,3,4,5];
 }
 public function __invoke(Request $r, PrayerTimeService $svc){ $u=$r->user(); $tz=$u->timezone?:'Asia/Karachi'; $now=Carbon::now($tz); $today=$now->toDateString();
  $settings=$u->settings ?? $u->settings()->create([]);
  $tasks=Task::where('user_id',$u->id)->whereDate('date',$today)->get();
  $pt=null; $next=null; try{$pt=$svc->getOrCreateForUser($u,Carbon::parse($today,$tz)); $next=$svc->nextPrayer($pt,Carbon::now($tz));}catch(\Throwable $e){}
  $prayers=PrayerRecord::where('user_id',$u->id)->whereDate('prayer_date',$today)->pluck('status','prayer_name');
  $sleep=SleepRecord::where('user_id',$u->id)->latest('sleep_start')->first();

  // ---- Today's routine timeline (contract: start/end/time_label/title/type/state/meta/url) ----
  $fmt=(($settings->time_format ?? '12')==='24') ? 'H:i' : 'g:i A';
  $timeline=[];
  $add=function($start,$end,$title,$type,$state,$meta=null,$url=null) use (&$timeline,$fmt){
    $timeline[]=['start'=>$start,'end'=>$end,'time_label'=>$start?$start->format($fmt):'','title'=>$title,'type'=>$type,'state'=>$state,'meta'=>$meta,'url'=>$url];
  };
  foreach ($tasks as $t) {
    if (!$t->start_time) continue;
    $start=Carbon::parse($today.' '.$t->start_time,$tz);
    $end=$t->end_time ? Carbon::parse($today.' '.$t->end_time,$tz) : null;
    if ($end && $end->lt($start)) $end->addDay();
    if ($t->status==='Completed') $state='completed';
    elseif (in_array($t->status,['Skipped','Cancelled'],true)) $state='skipped';
    elseif ($now->gte($start) && ($end ? $now->lte($end) : $now->diffInMinutes($start)<=60)) $state='active';
    else $state='upcoming';
    $add($start,$end,$t->title,'task',$state,$t->category,route('tasks.index'));
  }
  if ($pt) {
    $labels=['waqt_par'=>'On time','late'=>'Late','qaza'=>'Qaza','missed'=>'Missed'];
    // The current waqt is the LAST prayer whose time has passed; only it may show as "Now".
    // Earlier unmarked prayers are 'past' (never auto-labelled missed — that stays a manual status only).
    $currentKey=null;
    foreach (['fajr','dhuhr','asr','maghrib','isha'] as $k2) {
      $ts=$pt->{$k2}; if (!$ts) continue;
      if ($now->gte(Carbon::parse($today.' '.$ts,$tz))) $currentKey=$k2;
    }
    foreach (['fajr','dhuhr','asr','maghrib','isha'] as $key) {
      $timeStr=$pt->{$key}; if (!$timeStr) continue;
      $start=Carbon::parse($today.' '.$timeStr,$tz);
      $status=$prayers->get(ucfirst($key));
      if (in_array($status,['waqt_par','late','qaza'],true)) $state='completed';
      elseif ($status==='missed') $state='missed';
      elseif ($now->lt($start)) $state='upcoming';
      elseif ($key===$currentKey) $state='active';
      else $state='past';
      $add($start,null,ucfirst($key),'prayer',$state,$status ? $labels[$status] : 'Not marked yet',route('salah.index'));
    }
  }
  if (in_array($now->dayOfWeekIso, $this->workingDayIsos($settings->working_days), true) && $settings->office_start && $settings->office_end) {
    $oStart=Carbon::parse($today.' '.$settings->office_start,$tz);
    $oEnd=Carbon::parse($today.' '.$settings->office_end,$tz);
    if ($oEnd->lte($oStart)) $oEnd->addDay(); // overnight shift crosses midnight
    $oState=$now->lt($oStart) ? 'upcoming' : ($now->lte($oEnd) ? 'active' : 'completed');
    $add($oStart,$oEnd,'Office','office',$oState,'Work',route('office.index'));
  }
  foreach (Meal::where('user_id',$u->id)->whereDate('date',$today)->whereNotNull('time')->get() as $m) {
    $add(Carbon::parse($today.' '.$m->time,$tz),null,ucfirst($m->meal_type),'meal','completed','Logged meal',route('meals.index'));
  }
  $woke=SleepRecord::where('user_id',$u->id)->whereNotNull('wake_time')->whereDate('wake_time',$today)->latest('wake_time')->first();
  if ($woke) {
    $meta=$woke->duration_minutes ? sprintf('Slept %dh %02dm', intdiv($woke->duration_minutes,60), $woke->duration_minutes%60) : 'Sleep ended';
    $add(Carbon::parse($woke->wake_time)->setTimezone($tz),null,'Wake up','sleep','completed',$meta,route('sleep.index'));
  }
  usort($timeline, fn($a,$b)=>($a['start']?->getTimestamp() ?? PHP_INT_MAX) <=> ($b['start']?->getTimestamp() ?? PHP_INT_MAX));
  $nextUp=null; $threshold=$now->copy()->subHour();
  foreach ($timeline as $item) { if (in_array($item['state'],['active','upcoming'],true) && $item['start'] && $item['start']->gte($threshold)) { $nextUp=$item; break; } }

  $showOnboarding=!(bool)($settings->onboarding_dismissed ?? false) && app(OnboardingService::class)->hasIncomplete($u);

  return view('dashboard.index',[
   'date'=>Carbon::now($tz),'tasks'=>$tasks,'totalTasks'=>$tasks->count(),'completedTasks'=>$tasks->where('status','Completed')->count(),
   'prayerTimes'=>$pt,'nextPrayer'=>$next,'prayerRecords'=>$prayers,
   'waterToday'=>WaterLog::where('user_id',$u->id)->whereDate('date',$today)->sum('quantity_ml'),
   'mealsToday'=>Meal::where('user_id',$u->id)->whereDate('date',$today)->count(),
   'walkingToday'=>WalkingLog::where('user_id',$u->id)->whereDate('date',$today)->sum('distance_km'),
   'exerciseToday'=>ExerciseLog::where('user_id',$u->id)->whereDate('date',$today)->sum('duration_minutes'),
   'latestWeight'=>WeightLog::where('user_id',$u->id)->latest('date')->first(),
   'latestSleep'=>$sleep,'upcoming'=>Task::where('user_id',$u->id)->whereDate('date','>=',$today)->where('status','!=','Completed')->orderBy('date')->limit(5)->get(),
   'timeline'=>$timeline,'nextUp'=>$nextUp,'showOnboarding'=>$showOnboarding,
  ]); }
}
