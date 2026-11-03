<?php
namespace App\Services;
use App\Models\Task; use App\Models\PrayerRecord; use App\Models\WaterLog; use App\Models\WalkingLog; use App\Models\ExerciseLog; use App\Models\SleepRecord; use App\Models\Meal; use App\Models\LearningSession; use App\Models\FinancialRecord; use App\Models\FreelanceIncome; use App\Models\WeightLog; use Carbon\Carbon; use Illuminate\Support\Facades\DB;
class ReportService {
 public function range($user, Carbon $from, Carbon $to): array {
   $uid=$user->id;
   // Per-day series for charts (zero-filled), capped at 92 days.
   $dFrom=$from->copy()->startOfDay(); $dTo=$to->copy()->startOfDay(); if($dTo->gt($dFrom->copy()->addDays(91))) $dTo=$dFrom->copy()->addDays(91);
   $taskRows=Task::where('user_id',$uid)->whereBetween('date',[$dFrom->toDateString(),$dTo->toDateString()])->select(DB::raw('DATE(date) as d'),DB::raw('count(*) as total'),DB::raw("sum(case when status='Completed' then 1 else 0 end) as completed"))->groupBy('d')->get()->keyBy('d');
   $waterRows=WaterLog::where('user_id',$uid)->whereBetween('date',[$dFrom->toDateString(),$dTo->toDateString()])->select(DB::raw('DATE(date) as d'),DB::raw('sum(quantity_ml) as ml'))->groupBy('d')->get()->keyBy('d');
   $walkRows=WalkingLog::where('user_id',$uid)->whereBetween('date',[$dFrom->toDateString(),$dTo->toDateString()])->select(DB::raw('DATE(date) as d'),DB::raw('sum(distance_km) as km'))->groupBy('d')->get()->keyBy('d');
   $daily=[]; for($d=$dFrom->copy(); $d->lte($dTo); $d->addDay()){ $k=$d->toDateString(); $daily[]=['date'=>$k,'tasks_total'=>(int)($taskRows[$k]->total??0),'tasks_completed'=>(int)($taskRows[$k]->completed??0),'water_ml'=>(int)($waterRows[$k]->ml??0),'walking_km'=>(float)($walkRows[$k]->km??0)]; }
   // Plain-language insights, computed only from real records in (and before) this period.
   $insights=[];
   $insT=Task::where('user_id',$uid)->whereBetween('date',[$from,$to])->count(); $insC=Task::where('user_id',$uid)->whereBetween('date',[$from,$to])->where('status','Completed')->count();
   if($insT>0) $insights[]="You completed {$insC} of {$insT} planned tasks in this period.";
   $daysCount=max(1,$from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay())+1);
   $walkCur=(float)WalkingLog::where('user_id',$uid)->whereBetween('date',[$from,$to])->sum('distance_km');
   $walkPrev=(float)WalkingLog::where('user_id',$uid)->whereBetween('date',[$from->copy()->subDays($daysCount),$from->copy()->subDay()])->sum('distance_km');
   if($walkCur>0 && $walkPrev>0){ $pct=(int)round(abs($walkCur-$walkPrev)/$walkPrev*100); if($walkCur>$walkPrev) $insights[]="Walking is up {$pct}% compared with the previous period."; elseif($walkCur<$walkPrev) $insights[]="Walking is down {$pct}% compared with the previous period."; else $insights[]="Walking is the same as the previous period."; }
   $waterTot=(int)WaterLog::where('user_id',$uid)->whereBetween('date',[$from,$to])->sum('quantity_ml');
   if($waterTot>0) $insights[]="You drank an average of ".((int)round($waterTot/$daysCount))." ml of water per day.";
   $sleepAvgR=SleepRecord::where('user_id',$uid)->whereBetween('sleep_start',[$from,$to->copy()->endOfDay()])->avg('duration_minutes');
   if($sleepAvgR){ $minsR=(int)round($sleepAvgR); $insights[]=sprintf('Your average sleep was %dh %02dm.', intdiv($minsR,60), $minsR%60); }
   return [
    'insights'=>$insights,
    'daily'=>$daily,
    'tasks'=>['total'=>Task::where('user_id',$uid)->whereBetween('date',[$from,$to])->count(),'completed'=>Task::where('user_id',$uid)->whereBetween('date',[$from,$to])->where('status','Completed')->count()],
    'prayers'=>PrayerRecord::where('user_id',$uid)->whereBetween('prayer_date',[$from,$to])->select('status',DB::raw('count(*) as c'))->groupBy('status')->pluck('c','status')->toArray(),
    'water_ml'=>WaterLog::where('user_id',$uid)->whereBetween('date',[$from,$to])->sum('quantity_ml'),
    'walking_km'=>WalkingLog::where('user_id',$uid)->whereBetween('date',[$from,$to])->sum('distance_km'),
    'exercise_sessions'=>ExerciseLog::where('user_id',$uid)->whereBetween('date',[$from,$to])->count(),
    'exercise_minutes'=>ExerciseLog::where('user_id',$uid)->whereBetween('date',[$from,$to])->sum('duration_minutes'),
    'sleep_minutes_avg'=>SleepRecord::where('user_id',$uid)->whereBetween('sleep_start',[$from,$to->endOfDay()])->avg('duration_minutes'),
    'meals'=>Meal::where('user_id',$uid)->whereBetween('date',[$from,$to])->count(),
    'learning_minutes'=>LearningSession::where('user_id',$uid)->whereBetween('date',[$from,$to])->sum('duration_minutes'),
    'income'=>FinancialRecord::where('user_id',$uid)->whereBetween('date',[$from,$to])->where('type','income')->sum('amount'),
    'expense'=>FinancialRecord::where('user_id',$uid)->whereBetween('date',[$from,$to])->where('type','expense')->sum('amount'),
    'freelance_income'=>FreelanceIncome::where('user_id',$uid)->whereBetween('date',[$from,$to])->sum('net_income'),
    'weights'=>WeightLog::where('user_id',$uid)->whereBetween('date',[$from,$to])->orderBy('date')->get(['date','weight_kg']),
    'expense_by_category'=>FinancialRecord::where('user_id',$uid)->whereBetween('date',[$from,$to])->where('type','expense')->select('category',DB::raw('sum(amount) as total'))->groupBy('category')->pluck('total','category')->toArray(),
   ];
 }
}
