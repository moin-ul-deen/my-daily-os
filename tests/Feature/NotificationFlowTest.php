<?php
namespace Tests\Feature;
use App\Models\{User, Reminder, NotificationLog, WaterLog, Task};
use App\Services\ReminderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationFlowTest extends TestCase { use RefreshDatabase;

 public function test_due_reminder_creates_one_unread_inbox_notification_and_never_duplicates(): void {
   $u = User::factory()->create();
   Carbon::setTestNow(Carbon::parse(now()->toDateString().' 10:30', 'Asia/Karachi'));
   $r = Reminder::create(['user_id'=>$u->id,'type'=>'custom','title'=>'Drink a glass of water','time'=>'10:30','is_enabled'=>true]);
   $svc = app(ReminderService::class);
   $svc->checkForUser($u);
   $log = NotificationLog::where('user_id',$u->id)->where('channel','in_app')->where('reference_key','like','reminder-'.$r->id.'-%')->first();
   $this->assertNotNull($log, 'Due reminder should create an inbox notification');
   $this->assertSame('Drink a glass of water', $log->title);
   $this->assertNull($log->read_at, 'New notification must be unread');
   $countAfterFirst = NotificationLog::where('user_id',$u->id)->count();
   $svc->checkForUser($u);
   $this->assertSame($countAfterFirst, NotificationLog::where('user_id',$u->id)->count(), 'Second check must not create duplicates');
   Carbon::setTestNow();
 }

 public function test_test_notification_route_and_inbox_isolation(): void {
   $u = User::factory()->create();
   $other = User::factory()->create();
   $this->actingAs($u)->post('/settings/notifications/test')->assertRedirect();
   $this->assertDatabaseHas('notification_logs', ['user_id'=>$u->id,'type'=>'test','channel'=>'in_app','title'=>'Test notification']);
   $log = NotificationLog::where('user_id',$u->id)->first();
   $this->actingAs($u)->get('/notifications')->assertOk()->assertSee('Test notification');
   $this->actingAs($other)->get('/notifications')->assertOk()->assertDontSee('Test notification — notifications are working');
   $this->assertSame(0, NotificationLog::where('user_id',$other->id)->count());
   // Cross-user mark-read must be forbidden
   $this->actingAs($other)->post('/notifications/'.$log->id.'/read')->assertForbidden();
   // Owner can mark read and mark all read
   $this->actingAs($u)->post('/notifications/'.$log->id.'/read')->assertRedirect();
   $this->assertNotNull($log->fresh()->read_at);
   $this->actingAs($u)->post('/notifications/read-all')->assertRedirect();
 }

 public function test_chart_data_contains_zero_filled_daily_series(): void {
   $u = User::factory()->create();
   $day1 = now()->subDays(2)->toDateString(); $day3 = now()->toDateString();
   WaterLog::create(['user_id'=>$u->id,'date'=>$day1,'quantity_ml'=>500]);
   Task::create(['user_id'=>$u->id,'title'=>'Chart task','category'=>'Personal','date'=>$day1,'priority'=>'Medium','status'=>'Completed']);
   $res = $this->actingAs($u)->getJson('/reports/chart-data?from='.$day1.'&to='.$day3)->assertOk();
   $daily = $res->json('daily');
   $this->assertIsArray($daily);
   $this->assertCount(3, $daily, 'daily must have one zero-filled row per day in range');
   $this->assertSame($day1, $daily[0]['date']);
   $this->assertSame(500, $daily[0]['water_ml']);
   $this->assertSame(1, $daily[0]['tasks_total']);
   $this->assertSame(1, $daily[0]['tasks_completed']);
   $this->assertSame(0, $daily[1]['water_ml'], 'Middle day with no records must be zero-filled');
   $this->assertSame(0, $daily[1]['tasks_total']);
   $this->assertSame(0.0, (float)$daily[1]['walking_km']);
 }
}
