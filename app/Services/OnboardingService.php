<?php
namespace App\Services;
use App\Models\{Habit,HabitLog,LearningGoal,NotificationLog,NotificationSubscription,PrayerRecord,Task,TaskTemplate,User,WalkingLog,WaterLog,WeightLog};

/**
 * Computes the first-run "welcome" checklist from real database state.
 * A step is done only when the user's own records prove it - nothing is
 * pre-ticked. Settings-based steps use updated_at > created_at, i.e. the
 * user has actually saved that settings screen at least once.
 */
class OnboardingService {
 /** @return array<int,array{key:string,title:string,done:bool,url:string}> */
 public function stepsFor(User $u): array {
   $s = $u->settings;
   $p = $u->prayerSetting;
   $savedOnce = fn($m) => $m && $m->created_at && $m->updated_at && $m->updated_at->gt($m->created_at);
   return [
     ['key'=>'profile','title'=>'Set up your profile','done'=>$savedOnce($s),'url'=>route('settings.index')],
     ['key'=>'work','title'=>'Set your work schedule','done'=>!empty($s?->working_days),'url'=>route('settings.index')],
     ['key'=>'task','title'=>'Add your first task','done'=>Task::where('user_id',$u->id)->exists(),'url'=>route('tasks.create')],
     ['key'=>'routine','title'=>'Build your daily routine','done'=>TaskTemplate::where('user_id',$u->id)->exists(),'url'=>route('routine.index')],
     ['key'=>'salah','title'=>'Check your Salah settings','done'=>$savedOnce($p) || PrayerRecord::where('user_id',$u->id)->exists(),'url'=>route('salah.settings')],
     ['key'=>'health','title'=>'Log water, weight or a walk','done'=>WaterLog::where('user_id',$u->id)->exists() || WeightLog::where('user_id',$u->id)->exists() || WalkingLog::where('user_id',$u->id)->exists(),'url'=>route('health.index')],
     ['key'=>'habits','title'=>'Create your first habit','done'=>Habit::where('user_id',$u->id)->exists(),'url'=>route('habits.index')],
     ['key'=>'notifications','title'=>'Turn on notifications','done'=>NotificationSubscription::where('user_id',$u->id)->exists() || NotificationLog::where('user_id',$u->id)->exists(),'url'=>route('notifications.index')],
   ];
 }
 public function hasIncomplete(User $u): bool {
   foreach ($this->stepsFor($u) as $step) if (!$step['done']) return true;
   return false;
 }
}
