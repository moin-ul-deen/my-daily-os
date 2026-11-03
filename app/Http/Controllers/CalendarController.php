<?php
namespace App\Http\Controllers; use App\Models\{Task,PrayerRecord,Meal,SleepRecord,ExerciseLog,FinancialRecord,LearningSession,HabitLog}; use Carbon\Carbon; use Illuminate\Http\Request;
class CalendarController extends Controller { public function index(Request $r){ $date=$r->date?Carbon::parse($r->date):now(); $d=$date->toDateString(); $uid=auth()->id();
 $data=['date'=>$date,'tasks'=>Task::where('user_id',$uid)->whereDate('date',$d)->get(),'prayers'=>PrayerRecord::where('user_id',$uid)->whereDate('prayer_date',$d)->get(),'meals'=>Meal::where('user_id',$uid)->whereDate('date',$d)->get(),'sleep'=>SleepRecord::where('user_id',$uid)->whereDate('sleep_start',$d)->get(),'exercise'=>ExerciseLog::where('user_id',$uid)->whereDate('date',$d)->get(),'finance'=>FinancialRecord::where('user_id',$uid)->whereDate('date',$d)->get(),'learning'=>LearningSession::where('user_id',$uid)->whereDate('date',$d)->get(),'habits'=>HabitLog::where('user_id',$uid)->whereDate('date',$d)->get(),
 'monthTasks'=>Task::where('user_id',$uid)->whereYear('date',$date->year)->whereMonth('date',$date->month)->get()->groupBy(fn($t)=>$t->date->format('Y-m-d'))];
 return view('calendar.index',$data); } }
