<?php
namespace Tests\Feature;
use App\Models\{User,Task};
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTimelineTest extends TestCase { use RefreshDatabase;
 public function test_timeline_structure_states_and_next_up(): void {
   $u=User::factory()->create(); // timezone Asia/Karachi by factory default
   $tz='Asia/Karachi'; $now=Carbon::now($tz);
   Task::create(['user_id'=>$u->id,'title'=>'Active thing','category'=>'Work','date'=>$now->toDateString(),'start_time'=>$now->copy()->subMinutes(30)->format('H:i:s'),'end_time'=>$now->copy()->addMinutes(30)->format('H:i:s'),'priority'=>'High','status'=>'Pending']);
   Task::create(['user_id'=>$u->id,'title'=>'Done thing','category'=>'Work','date'=>$now->toDateString(),'start_time'=>$now->copy()->subHours(3)->format('H:i:s'),'end_time'=>$now->copy()->subHours(2)->format('H:i:s'),'priority'=>'Low','status'=>'Completed']);
   Task::create(['user_id'=>$u->id,'title'=>'No time task','category'=>'Work','date'=>$now->toDateString(),'priority'=>'Low','status'=>'Pending']); // no start_time -> excluded from timeline
   $resp=$this->actingAs($u)->get('/dashboard'); $resp->assertOk();
   $timeline=$resp->viewData('timeline'); $this->assertIsArray($timeline);
   foreach($timeline as $item){ foreach(['start','end','time_label','title','type','state','meta','url'] as $k) $this->assertArrayHasKey($k,$item); }
   $byTitle=collect($timeline)->keyBy('title');
   $this->assertEquals('active',$byTitle['Active thing']['state']);
   $this->assertEquals('completed',$byTitle['Done thing']['state']);
   $this->assertFalse($byTitle->has('No time task'));
   $this->assertEquals(5, collect($timeline)->where('type','prayer')->count()); // five prayers always present
   $this->assertNotContains('missed', collect($timeline)->where('type','task')->pluck('state')->all()); // tasks are never labelled missed
   $nextUp=$resp->viewData('nextUp'); $this->assertNotNull($nextUp);
   // sorted by start time
   $starts=collect($timeline)->map(fn($i)=>$i['start']?->getTimestamp())->filter()->values()->all();
   $sorted=$starts; sort($sorted); $this->assertEquals($sorted,$starts);
 }
 public function test_only_current_prayer_is_active_at_night(): void {
   // Regression: at 11 PM every prayer's time has passed; only Isha (the current waqt)
   // may be labelled 'active'/'Now' — earlier unmarked prayers are 'past', never 'missed'.
   Carbon::setTestNow(Carbon::parse('2026-11-03 23:00:00','Asia/Karachi'));
   try {
     $u=User::factory()->create();
     $resp=$this->actingAs($u)->get('/dashboard'); $resp->assertOk();
     $prayers=collect($resp->viewData('timeline'))->where('type','prayer')->keyBy('title');
     $this->assertEquals('active',$prayers['Isha']['state']);
     foreach(['Fajr','Dhuhr','Asr','Maghrib'] as $p) $this->assertEquals('past',$prayers[$p]['state'], $p.' should be past at 23:00');
     $this->assertNotContains('missed', $prayers->pluck('state')->all()); // unmarked is never auto-missed
   } finally { Carbon::setTestNow(); }
 }
}
