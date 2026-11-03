<?php
namespace Tests\Feature;
use App\Models\{User,Task,Habit};
use App\Services\OnboardingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase { use RefreshDatabase;
 private function step(array $steps, string $key): array { foreach($steps as $s) if($s['key']===$key) return $s; $this->fail("step {$key} missing"); }
 public function test_fresh_user_has_incomplete_steps(): void {
   $u=User::factory()->create();
   $steps=app(OnboardingService::class)->stepsFor($u);
   $this->assertFalse($this->step($steps,'task')['done']);
   $this->assertFalse($this->step($steps,'habits')['done']);
   $this->assertFalse($this->step($steps,'routine')['done']);
   $this->assertTrue(app(OnboardingService::class)->hasIncomplete($u));
   $this->actingAs($u)->get('/dashboard')->assertOk()->assertViewHas('showOnboarding',true);
 }
 public function test_steps_flip_when_records_exist(): void {
   $u=User::factory()->create();
   Task::create(['user_id'=>$u->id,'title'=>'First','category'=>'Personal','date'=>now()->toDateString(),'priority'=>'Low','status'=>'Pending']);
   Habit::create(['user_id'=>$u->id,'name'=>'Walk','frequency'=>'daily','start_date'=>now()->toDateString()]);
   $steps=app(OnboardingService::class)->stepsFor($u);
   $this->assertTrue($this->step($steps,'task')['done']);
   $this->assertTrue($this->step($steps,'habits')['done']);
 }
 public function test_dismiss_persists_and_hides_dashboard_card(): void {
   $u=User::factory()->create();
   $this->actingAs($u)->post('/welcome/dismiss')->assertRedirect(route('dashboard'));
   $this->assertTrue((bool)$u->settings()->first()->onboarding_dismissed);
   $this->actingAs($u)->get('/dashboard')->assertOk()->assertViewHas('showOnboarding',false);
 }
 public function test_welcome_page_renders_when_view_exists(): void {
   if (!view()->exists('onboarding.index')) $this->markTestSkipped('onboarding.index view is built by the design pass');
   $u=User::factory()->create();
   $this->actingAs($u)->get('/welcome')->assertOk()->assertViewHas('steps');
 }
}
