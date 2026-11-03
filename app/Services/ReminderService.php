<?php
namespace App\Services;
use App\Models\{PrayerRecord,Reminder,Task,User}; use Carbon\Carbon;
class ReminderService {
 public function __construct(private PrayerTimeService $prayer, private NotificationService $notify){}
 public function checkForUser(User $user): int { $count=0; $tz=$user->timezone?:'Asia/Karachi'; $now=Carbon::now($tz); $today=$now->toDateString();
   if(!($user->settings->notifications_enabled ?? true)) return 0;
   // Prayer reminders
   try{ $pt=$this->prayer->getOrCreateForUser($user,Carbon::parse($today,$tz)); $rem=(array)($user->prayerSetting->reminder_settings??[]);
     foreach(['fajr','dhuhr','asr','maghrib','isha'] as $p){ $cfg=$rem[$p]??['before'=>10,'at_time'=>true,'enabled'=>true]; if(!($cfg['enabled']??true)) continue; $time=Carbon::parse($today.' '.$pt->$p,$tz); $before=$cfg['before']??10;
       if($now->diffInMinutes($time,false)>0 && $now->diffInMinutes($time,false)<=$before){ if($this->notify->sendOnce($user,'prayer',"prayer-{$p}-{$today}-before",'Prayer Reminder',ucfirst($p).' in '.$before.' minutes')) $count++; }
       if(abs($now->diffInMinutes($time,false))<2 && ($cfg['at_time']??true)){ if($this->notify->sendOnce($user,'prayer',"prayer-{$p}-{$today}-at",'Prayer Time',ucfirst($p).' time has started')) $count++; }
     } }catch(\Throwable $e){ \Log::warning($e->getMessage()); }
   foreach(Task::where('user_id',$user->id)->whereDate('date',$today)->where('status','!=','Completed')->whereNotNull('reminder_minutes')->get() as $task){ if($task->start_time){ $t=Carbon::parse($today.' '.$task->start_time,$tz)->subMinutes($task->reminder_minutes); if(abs($now->diffInMinutes($t,false))<2){ if($this->notify->sendOnce($user,'task',"task-{$task->id}-{$today}",'Task Reminder',$task->title)) $count++; } } }
   foreach(Reminder::where('user_id',$user->id)->where('is_enabled',true)->get() as $r){ if($r->time && $now->format('H:i')===Carbon::parse($r->time)->format('H:i')){ if($this->notify->sendOnce($user,$r->type,"reminder-{$r->id}-{$today}-{$r->time}",$r->title,$r->title)) $count++; } }
   return $count;
 }
 public function checkAll(): int { $c=0; User::chunk(100,function($users)use(&$c){ foreach($users as $u) $c+=$this->checkForUser($u); }); return $c; }
}
