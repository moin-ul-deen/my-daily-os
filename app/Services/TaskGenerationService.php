<?php
namespace App\Services;
use App\Models\{Task,TaskTemplate};
use Carbon\Carbon;

/**
 * Single source of truth for turning a recurring TaskTemplate into dated
 * Task instances. Used by TaskController (one-off form with recurrence)
 * and RoutineController (routine builder) so both behave identically.
 *
 * Semantics:
 * - Instances are separate rows per date; history is never rewritten.
 * - generate() only creates missing instances (firstOrCreate per date).
 * - refreshFuture() deletes FUTURE Pending instances (today onward) and
 *   regenerates them from the template's current values. Past instances
 *   and completed/skipped future instances are never touched.
 */
class TaskGenerationService {
 /** @return Carbon[] occurrence dates >= $from, at most $limit */
 public function datesFor(TaskTemplate $tpl, Carbon $from, int $limit = 30): array {
   $anchor = Carbon::parse($tpl->starts_on)->startOfDay();
   $from = $from->copy()->startOfDay();
   $interval = max(1, (int) ($tpl->recurrence_interval ?: 1));
   $type = $tpl->recurrence_type;
   $days = array_map('intval', (array) ($tpl->recurrence_days ?? []));
   $endsOn = $tpl->ends_on ? Carbon::parse($tpl->ends_on)->startOfDay() : null;
   $out = [];
   if (in_array($type, ['daily','weekly','monthly'], true)) {
     for ($i = 0; $i < $limit + 400 && count($out) < $limit; $i++) {
       $d = $anchor->copy();
       if ($type === 'daily') $d->addDays($i * $interval);
       elseif ($type === 'weekly') $d->addWeeks($i * $interval);
       else $d->addMonths($i * $interval);
       if ($endsOn && $d->gt($endsOn)) break;
       if ($d->lt($from)) continue;
       $out[] = $d;
     }
   } elseif ($type === 'weekdays' || ($type === 'custom' && !empty($days))) {
     $d = $anchor->gt($from) ? $anchor->copy() : $from->copy();
     $guard = 0;
     while (count($out) < $limit && $guard++ < 900) {
       if ($endsOn && $d->gt($endsOn)) break;
       $match = $type === 'weekdays' ? !$d->isWeekend() : in_array($d->dayOfWeek, $days, true);
       if ($match) $out[] = $d->copy();
       $d->addDay();
     }
   } else { // custom without days, or any other type: step by interval days from the anchor
     for ($i = 0; $i < $limit + 400 && count($out) < $limit; $i++) {
       $d = $anchor->copy()->addDays($i * $interval);
       if ($endsOn && $d->gt($endsOn)) break;
       if ($d->lt($from)) continue;
       $out[] = $d;
     }
   }
   return $out;
 }

 /** Create missing instances for the template. Returns number created. */
 public function generate(TaskTemplate $tpl, ?Carbon $from = null, int $occurrences = 30): int {
   $from = $from ?? Carbon::parse($tpl->starts_on);
   $created = 0;
   foreach ($this->datesFor($tpl, $from, $occurrences) as $d) {
     $t = Task::firstOrCreate(
       ['user_id' => $tpl->user_id, 'task_template_id' => $tpl->id, 'date' => $d->toDateString()],
       ['title' => $tpl->title, 'description' => $tpl->description, 'category' => $tpl->category,
        'start_time' => $tpl->start_time, 'end_time' => $tpl->end_time, 'priority' => $tpl->priority,
        'reminder_minutes' => $tpl->reminder_minutes]
     );
     if ($t->wasRecentlyCreated) $created++;
   }
   return $created;
 }

 /** Delete future Pending instances (today onward) and regenerate from the template's current values. */
 public function refreshFuture(TaskTemplate $tpl): void {
   $today = Carbon::today();
   Task::where('task_template_id', $tpl->id)->where('user_id', $tpl->user_id)
     ->whereDate('date', '>=', $today)->where('status', 'Pending')->whereNull('completed_at')->delete();
   $from = Carbon::parse($tpl->starts_on)->gt($today) ? Carbon::parse($tpl->starts_on) : $today;
   $this->generate($tpl, $from, 30);
 }

 /** @return string[] dates (Y-m-d) of occurrences inside the next $days days from $from */
 public function previewDates(TaskTemplate $tpl, Carbon $from, int $days = 7): array {
   $until = $from->copy()->startOfDay()->addDays($days - 1);
   $out = [];
   foreach ($this->datesFor($tpl, $from, 60) as $d) {
     if ($d->gt($until)) break;
     $out[] = $d->toDateString();
   }
   return $out;
 }
}
