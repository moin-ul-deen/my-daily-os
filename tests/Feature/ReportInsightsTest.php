<?php
namespace Tests\Feature;
use App\Models\{User,Task,WalkingLog,WaterLog};
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportInsightsTest extends TestCase { use RefreshDatabase;
 public function test_insights_from_real_data(): void {
   $u=User::factory()->create();
   for($i=0;$i<4;$i++) Task::create(['user_id'=>$u->id,'title'=>"T{$i}",'category'=>'Personal','date'=>now()->subDays($i%3)->toDateString(),'priority'=>'Low','status'=>$i<3?'Completed':'Pending']);
   WalkingLog::create(['user_id'=>$u->id,'date'=>now()->subDays(2)->toDateString(),'distance_km'=>10]);
   WalkingLog::create(['user_id'=>$u->id,'date'=>now()->subDays(9)->toDateString(),'distance_km'=>5]);
   WaterLog::create(['user_id'=>$u->id,'date'=>now()->toDateString(),'quantity_ml'=>2000]);
   $report=app(ReportService::class)->range($u, Carbon::now()->subDays(6), Carbon::now());
   $this->assertArrayHasKey('insights',$report);
   $this->assertContains('You completed 3 of 4 planned tasks in this period.',$report['insights']);
   $this->assertContains('Walking is up 100% compared with the previous period.',$report['insights']);
   $this->assertNotEmpty(array_filter($report['insights'], fn($s)=>str_contains($s,'ml of water per day')));
 }
 public function test_insights_empty_without_data(): void {
   $u=User::factory()->create();
   $report=app(ReportService::class)->range($u, Carbon::now()->subDays(6), Carbon::now());
   $this->assertArrayHasKey('insights',$report);
   $this->assertEmpty(array_filter($report['insights'], fn($s)=>str_starts_with($s,'You completed')));
   $this->assertEquals([], array_values(array_filter($report['insights'], fn($s)=>str_contains($s,'Walking is'))));
 }
 public function test_chart_data_endpoint_includes_insights(): void {
   $u=User::factory()->create();
   Task::create(['user_id'=>$u->id,'title'=>'One','category'=>'Personal','date'=>now()->toDateString(),'priority'=>'Low','status'=>'Completed']);
   $resp=$this->actingAs($u)->get('/reports/chart-data?from='.now()->subDays(6)->toDateString().'&to='.now()->toDateString());
   $resp->assertOk(); $json=$resp->json(); $this->assertArrayHasKey('insights',$json);
   $this->assertContains('You completed 1 of 1 planned tasks in this period.',$json['insights']);
 }
}
