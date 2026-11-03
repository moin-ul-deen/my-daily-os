<?php
namespace Tests\Feature;
use App\Models\{User,Task,TaskTemplate};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutineTest extends TestCase { use RefreshDatabase;
 private function payload(array $over=[]): array { return array_merge(['title'=>'Read Quran','category'=>'Salah','start_time'=>'05:30','end_time'=>'06:00','priority'=>'Medium','recurrence_type'=>'daily','starts_on'=>now()->toDateString()],$over); }
 public function test_create_daily_routine_generates_instances(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/routine',$this->payload())->assertRedirect(route('routine.index'))->assertSessionHas('success');
   $this->assertNotNull(TaskTemplate::first());
   $this->assertGreaterThanOrEqual(7, Task::count());
   $this->assertTrue(Task::whereDate('date',now()->addDays(6)->toDateString())->exists());
 }
 public function test_weekly_routine_only_on_anchor_weekday(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/routine',$this->payload(['recurrence_type'=>'weekly']));
   $this->assertTrue(Task::whereDate('date',now()->addDays(7)->toDateString())->exists());
   $this->assertFalse(Task::whereDate('date',now()->addDay()->toDateString())->exists());
 }
 public function test_edit_updates_future_instances_but_not_past(): void {
   $u=User::factory()->create();
   $yesterday=now()->subDay()->toDateString();
   $this->actingAs($u)->post('/routine',$this->payload(['title'=>'Old Name','starts_on'=>$yesterday]));
   $tpl=TaskTemplate::first();
   $past=Task::whereDate('date',$yesterday)->first(); $this->assertNotNull($past); $this->assertEquals('Old Name',$past->title);
   $this->actingAs($u)->put("/routine/{$tpl->id}",$this->payload(['title'=>'New Name','starts_on'=>$yesterday]))->assertRedirect(route('routine.index'));
   $this->assertEquals('Old Name', Task::find($past->id)->title); // history untouched
   $tomorrow=Task::whereDate('date',now()->addDay()->toDateString())->first();
   $this->assertNotNull($tomorrow); $this->assertEquals('New Name',$tomorrow->title); // future follows the new plan
 }
 public function test_delete_template_keeps_instances_detached(): void {
   $u=User::factory()->create();
   $yesterday=now()->subDay()->toDateString();
   $this->actingAs($u)->post('/routine',$this->payload(['starts_on'=>$yesterday]));
   $tpl=TaskTemplate::first(); $before=Task::count(); $this->assertGreaterThan(0,$before);
   $this->actingAs($u)->delete("/routine/{$tpl->id}")->assertRedirect(route('routine.index'))->assertSessionHas('success');
   $this->assertSoftDeleted('task_templates',['id'=>$tpl->id]);
   $this->assertEquals($before, Task::count()); // nothing was deleted
   $past=Task::whereDate('date',$yesterday)->first(); $this->assertNotNull($past);
   $this->assertNull($past->task_template_id); // detached, kept as history
 }
 public function test_other_user_cannot_touch_template(): void {
   $owner=User::factory()->create(); $other=User::factory()->create();
   $this->actingAs($owner)->post('/routine',$this->payload());
   $tpl=TaskTemplate::first();
   $this->actingAs($other)->put("/routine/{$tpl->id}",$this->payload(['title'=>'Hijack']))->assertForbidden();
   $this->actingAs($other)->delete("/routine/{$tpl->id}")->assertForbidden();
   $this->assertEquals('Read Quran', TaskTemplate::find($tpl->id)->title);
 }
 public function test_routine_index_renders_when_view_exists(): void {
   if (!view()->exists('routine.index')) $this->markTestSkipped('routine.index view is built by the design pass');
   $u=User::factory()->create();
   $this->actingAs($u)->post('/routine',$this->payload());
   $this->actingAs($u)->get('/routine')->assertOk()->assertViewHas('previews');
 }
}
