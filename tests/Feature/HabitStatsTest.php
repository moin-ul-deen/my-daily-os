<?php
namespace Tests\Feature;
use App\Models\{User,Habit,HabitLog};
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HabitStatsTest extends TestCase { use RefreshDatabase;
 private function log(Habit $h, Carbon $d, bool $done=true): void { HabitLog::create(['user_id'=>$h->user_id,'habit_id'=>$h->id,'date'=>$d->toDateString(),'completed'=>$done]); }
 public function test_streak_week_and_grid(): void {
   $u=User::factory()->create();
   $h=Habit::create(['user_id'=>$u->id,'name'=>'Walk','frequency'=>'daily','target'=>'5','start_date'=>now()->subDays(90)->toDateString()]);
   $today=Carbon::today();
   // Streak: today, -1, -2 done; -3 missing (gap); -4 done but behind the gap.
   foreach([0,1,2,4] as $ago) $this->log($h,$today->copy()->subDays($ago));
   $resp=$this->actingAs($u)->get('/habits'); $resp->assertOk();
   $stats=$resp->viewData('habitStats'); $this->assertIsArray($stats); $s=$stats[$h->id];
   $this->assertEquals(3,$s['streak']);
   $this->assertEquals(84,count($s['last_84']));
   $this->assertEquals($today->toDateString(),$s['last_84'][83]['date']);
   $this->assertTrue($s['last_84'][83]['completed']);
   $expectedWeek=0; foreach([0,1,2,4] as $ago){ $d=$today->copy()->subDays($ago); if($d->between($today->copy()->startOfWeek(Carbon::MONDAY),$today->copy()->endOfWeek(Carbon::SUNDAY))) $expectedWeek++; }
   $this->assertEquals($expectedWeek,$s['week_done']);
   $this->assertEquals(5,$s['week_target']); // numeric target parsed
 }
 public function test_streak_counts_from_yesterday_when_today_not_done(): void {
   $u=User::factory()->create();
   $h=Habit::create(['user_id'=>$u->id,'name'=>'Read','frequency'=>'daily','start_date'=>now()->subDays(30)->toDateString()]);
   $today=Carbon::today();
   foreach([1,2] as $ago) $this->log($h,$today->copy()->subDays($ago));
   $stats=$this->actingAs($u)->get('/habits')->viewData('habitStats');
   $this->assertEquals(2,$stats[$h->id]['streak']);
   $this->assertNull($stats[$h->id]['week_target']); // no numeric target/frequency -> null
 }
}
