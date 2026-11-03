<?php
namespace Tests\Feature;
use App\Models\{User,Task,TaskCompletion,TaskTemplate,WaterLog,Habit};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Repeat-click safety (v3 follow-up): the first click deletes; a second or
 * third rapid click must land on a friendly redirect - never a 404/500 page.
 */
class IdempotentDeleteTest extends TestCase { use RefreshDatabase;
 public function test_delete_same_task_twice(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/tasks',['title'=>'Clicky','category'=>'Personal','date'=>now()->toDateString(),'priority'=>'Low']);
   $t=Task::first();
   $this->actingAs($u)->delete("/tasks/{$t->id}")->assertRedirect(route('tasks.index'))->assertSessionHas('success','Task deleted');
   $second=$this->actingAs($u)->delete("/tasks/{$t->id}");
   $second->assertRedirect(route('tasks.index'))->assertSessionHas('success','Already deleted'); // not 404, not 500
   $this->actingAs($u)->delete("/tasks/{$t->id}")->assertRedirect(route('tasks.index')); // third click too
 }
 public function test_delete_nonexistent_task_redirects_instead_of_404(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->delete('/tasks/999999')->assertRedirect(route('tasks.index'))->assertSessionHas('success','Already deleted');
 }
 public function test_delete_same_water_log_twice(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/health/water',['date'=>now()->toDateString(),'quantity_ml'=>250]);
   $w=WaterLog::first();
   $this->actingAs($u)->delete("/health/water/{$w->id}")->assertRedirect(route('health.water.index'))->assertSessionHas('success','Water log deleted');
   $this->actingAs($u)->delete("/health/water/{$w->id}")->assertRedirect(route('health.water.index'))->assertSessionHas('success','Already deleted');
 }
 public function test_delete_same_habit_twice(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/habits',['name'=>'Walk','frequency'=>'daily','start_date'=>now()->toDateString()]);
   $h=Habit::first();
   $this->actingAs($u)->delete("/habits/{$h->id}")->assertRedirect(route('habits.index'))->assertSessionHas('success','Habit deleted');
   $this->actingAs($u)->delete("/habits/{$h->id}")->assertRedirect(route('habits.index'))->assertSessionHas('success','Already deleted');
 }
 public function test_delete_same_routine_template_twice(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/routine',['title'=>'Read','category'=>'Learning','recurrence_type'=>'daily','starts_on'=>now()->toDateString()]);
   $tpl=TaskTemplate::first(); $this->assertNotNull($tpl);
   $this->actingAs($u)->delete("/routine/{$tpl->id}")->assertRedirect(route('routine.index'));
   $this->actingAs($u)->delete("/routine/{$tpl->id}")->assertRedirect(route('routine.index'))->assertSessionHas('success','Already deleted');
 }
 public function test_double_complete_creates_one_completion_row(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/tasks',['title'=>'Twice','category'=>'Personal','date'=>now()->toDateString(),'priority'=>'Low']);
   $t=Task::first();
   $this->actingAs($u)->post("/tasks/{$t->id}/complete")->assertRedirect();
   $this->actingAs($u)->post("/tasks/{$t->id}/complete")->assertRedirect(); // repeat click: no error, no duplicate history
   $this->assertEquals('Completed', Task::find($t->id)->status);
   $this->assertEquals(1, TaskCompletion::where('task_id',$t->id)->count());
   $this->actingAs($u)->post("/tasks/{$t->id}/reopen")->assertRedirect();
   $this->actingAs($u)->post("/tasks/{$t->id}/reopen")->assertRedirect(); // reopen is also a safe no-op when already pending
   $this->assertEquals('Pending', Task::find($t->id)->status);
 }
}
