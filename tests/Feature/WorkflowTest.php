<?php
namespace Tests\Feature;
use App\Models\{User,Task,TaskCompletion,TaskTemplate,WaterLog,Meal,MealItem,Project,FreelanceIncome,FinancialRecord,Habit,HabitLog,SleepRecord,OfficeRecord,LearningGoal,LearningSession,WeightLog,WalkingLog,ExerciseLog};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Real user workflows over HTTP: create -> edit -> module action -> delete,
 * including the delete hardening regression suite from the v3 audit.
 */
class WorkflowTest extends TestCase { use RefreshDatabase;

 // ---------- Task delete hardening (v3 regression suite) ----------
 public function test_delete_pending_task(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/tasks',['title'=>'Delete me','category'=>'Personal','date'=>now()->toDateString(),'priority'=>'Low'])->assertRedirect();
   $t=Task::first();
   $this->actingAs($u)->delete("/tasks/{$t->id}")->assertRedirect(route('tasks.index'))->assertSessionHas('success','Task deleted');
   $this->assertSoftDeleted('tasks',['id'=>$t->id]);
   $this->actingAs($u)->get('/tasks')->assertOk()->assertDontSee('Delete me');
 }
 public function test_delete_completed_task_keeps_completion_history(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/tasks',['title'=>'Done task','category'=>'Work','date'=>now()->toDateString(),'priority'=>'Medium']);
   $t=Task::first();
   $this->actingAs($u)->post("/tasks/{$t->id}/complete");
   $this->assertEquals(1, TaskCompletion::count());
   $this->actingAs($u)->delete("/tasks/{$t->id}")->assertRedirect(route('tasks.index'))->assertSessionHas('success','Task deleted');
   $this->assertSoftDeleted('tasks',['id'=>$t->id]);
   $this->assertDatabaseHas('task_completions',['task_id'=>$t->id]); // history row survives
 }
 public function test_delete_recurring_instance_keeps_template_and_siblings(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/tasks',['title'=>'Daily thing','category'=>'Personal','date'=>now()->toDateString(),'priority'=>'Low','recurrence_type'=>'daily']);
   $this->assertNotNull(TaskTemplate::first());
   $before=Task::count(); $this->assertGreaterThan(2,$before);
   $one=Task::orderBy('date')->first();
   $this->actingAs($u)->delete("/tasks/{$one->id}")->assertRedirect(route('tasks.index'));
   $this->assertEquals($before-1, Task::count());
   $this->assertNotNull(TaskTemplate::first());
 }
 public function test_delete_other_users_task_is_forbidden(): void {
   $owner=User::factory()->create(); $other=User::factory()->create();
   $t=Task::create(['user_id'=>$owner->id,'title'=>'Not yours','category'=>'Personal','date'=>now()->toDateString(),'priority'=>'Low','status'=>'Pending']);
   $this->actingAs($other)->delete("/tasks/{$t->id}")->assertForbidden();
   $this->assertDatabaseHas('tasks',['id'=>$t->id,'deleted_at'=>null]);
 }
 public function test_dashboard_counts_change_after_delete(): void {
   $u=User::factory()->create();
   foreach(['One','Two'] as $title) Task::create(['user_id'=>$u->id,'title'=>$title,'category'=>'Personal','date'=>now()->toDateString(),'priority'=>'Low','status'=>'Pending']);
   $this->actingAs($u)->get('/dashboard')->assertOk()->assertViewHas('totalTasks',2);
   $t=Task::where('title','One')->first();
   $this->actingAs($u)->delete("/tasks/{$t->id}");
   $this->actingAs($u)->get('/dashboard')->assertOk()->assertViewHas('totalTasks',1);
 }

 // ---------- Module workflows: create -> edit -> delete ----------
 public function test_water_workflow(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/health/water',['date'=>now()->toDateString(),'quantity_ml'=>250])->assertRedirect();
   $w=WaterLog::first(); $this->assertNotNull($w);
   $this->actingAs($u)->put("/health/water/{$w->id}",['date'=>now()->toDateString(),'quantity_ml'=>500])->assertRedirect();
   $this->assertEquals(500, WaterLog::find($w->id)->quantity_ml);
   $this->actingAs($u)->delete("/health/water/{$w->id}")->assertRedirect(route('health.water.index'))->assertSessionHas('success');
   $this->assertEquals(0, WaterLog::count());
 }
 public function test_meal_with_items_workflow_and_delete_removes_items(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/meals',['meal_type'=>'Lunch','date'=>now()->toDateString(),'items'=>[['food_name'=>'Biryani','quantity'=>'1 plate','calories'=>600]]])->assertRedirect();
   $m=Meal::first(); $this->assertNotNull($m); $this->assertEquals(1, MealItem::count());
   $this->actingAs($u)->delete("/meals/{$m->id}")->assertRedirect(route('meals.index'))->assertSessionHas('success','Meal deleted');
   $this->assertEquals(0, Meal::count()); $this->assertEquals(0, MealItem::count());
 }
 public function test_project_workflow(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/freelancing/projects',['name'=>'Site build','status'=>'Idea'])->assertRedirect();
   $p=Project::first(); $this->assertNotNull($p);
   $this->actingAs($u)->get('/freelancing/projects')->assertOk(); // regression: index ordered by a non-existent 'date' column before the v3 fix
   $this->actingAs($u)->put("/freelancing/projects/{$p->id}",['name'=>'Site build v2','status'=>'In progress'])->assertRedirect();
   $this->assertEquals('Site build v2', Project::find($p->id)->name);
   $this->actingAs($u)->delete("/freelancing/projects/{$p->id}")->assertRedirect(route('freelancing.projects.index'))->assertSessionHas('success','Project deleted');
   $this->assertEquals(0, Project::count());
 }
 public function test_income_workflow(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/freelancing/income',['date'=>now()->toDateString(),'gross_income'=>1000,'fees'=>50,'payment_status'=>'Paid'])->assertRedirect();
   $i=FreelanceIncome::first(); $this->assertNotNull($i); $this->assertEquals(950.0, (float)$i->net_income);
   $this->actingAs($u)->delete("/freelancing/income/{$i->id}")->assertRedirect(route('freelancing.income.index'));
   $this->assertEquals(0, FreelanceIncome::count());
 }
 public function test_finance_workflow(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/finance',['type'=>'expense','category'=>'Food','amount'=>500,'date'=>now()->toDateString()])->assertRedirect();
   $f=FinancialRecord::first(); $this->assertNotNull($f);
   $this->actingAs($u)->put("/finance/{$f->id}",['type'=>'expense','category'=>'Food','amount'=>750,'date'=>now()->toDateString()])->assertRedirect();
   $this->assertEquals(750.0, (float)FinancialRecord::find($f->id)->amount);
   $this->actingAs($u)->delete("/finance/{$f->id}")->assertRedirect(route('finance.index'));
   $this->assertEquals(0, FinancialRecord::count());
 }
 public function test_habit_workflow_with_toggle(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/habits',['name'=>'Morning walk','frequency'=>'daily','start_date'=>now()->toDateString()])->assertRedirect();
   $h=Habit::first(); $this->assertNotNull($h);
   $this->actingAs($u)->post("/habits/{$h->id}/toggle",['date'=>now()->toDateString()]);
   $this->assertTrue((bool)HabitLog::first()->completed);
   $this->actingAs($u)->delete("/habits/{$h->id}")->assertRedirect(route('habits.index'))->assertSessionHas('success','Habit deleted');
   $this->assertEquals(0, Habit::count());
   $this->assertEquals(1, HabitLog::count()); // habit history is preserved in the database
 }
 public function test_sleep_workflow_cross_midnight(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/sleep',['sleep_start'=>now()->subDay()->setTime(23,0),'wake_time'=>now()->setTime(7,0),'quality_rating'=>4])->assertRedirect();
   $s=SleepRecord::first(); $this->assertNotNull($s); $this->assertEquals(480, (int)$s->duration_minutes);
   $this->actingAs($u)->delete("/sleep/{$s->id}")->assertRedirect(route('sleep.index'));
   $this->assertEquals(0, SleepRecord::count());
 }
 public function test_office_workflow(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/office',['date'=>now()->toDateString()])->assertRedirect();
   $o=OfficeRecord::first(); $this->assertNotNull($o);
   $this->actingAs($u)->delete("/office/{$o->id}")->assertRedirect(route('office.index'));
   $this->assertEquals(0, OfficeRecord::count());
 }
 public function test_learning_goal_and_session_workflow(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/learning/goal',['title'=>'Laravel deep dive'])->assertRedirect();
   $g=LearningGoal::first(); $this->assertNotNull($g);
   $this->actingAs($u)->post('/learning/session',['topic'=>'Queues','date'=>now()->toDateString(),'duration_minutes'=>45])->assertRedirect();
   $s=LearningSession::first(); $this->assertNotNull($s);
   $this->actingAs($u)->delete("/learning/session/{$s->id}")->assertRedirect(route('learning.index'));
   $this->actingAs($u)->delete("/learning/goal/{$g->id}")->assertRedirect(route('learning.index'));
   $this->assertEquals(0, LearningSession::count()); $this->assertEquals(0, LearningGoal::count());
 }
 public function test_weight_walking_exercise_workflows(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/health/weight',['date'=>now()->toDateString(),'weight_kg'=>72.5]); $w=WeightLog::first(); $this->assertNotNull($w);
   $this->actingAs($u)->delete("/health/weight/{$w->id}")->assertRedirect(route('health.weight.index')); $this->assertEquals(0, WeightLog::count());
   $this->actingAs($u)->post('/health/walking',['date'=>now()->toDateString(),'distance_km'=>3.2]); $wk=WalkingLog::first(); $this->assertNotNull($wk);
   $this->actingAs($u)->delete("/health/walking/{$wk->id}")->assertRedirect(route('health.walking.index')); $this->assertEquals(0, WalkingLog::count());
   $this->actingAs($u)->post('/health/exercise',['exercise_type'=>'Push-ups','date'=>now()->toDateString(),'duration_minutes'=>15]); $e=ExerciseLog::first(); $this->assertNotNull($e);
   $this->actingAs($u)->delete("/health/exercise/{$e->id}")->assertRedirect(route('health.exercise.index')); $this->assertEquals(0, ExerciseLog::count());
 }
}
