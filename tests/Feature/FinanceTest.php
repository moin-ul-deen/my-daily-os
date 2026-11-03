<?php namespace Tests\Feature; use App\Models\{User,FinancialRecord}; use Illuminate\Foundation\Testing\RefreshDatabase; use Tests\TestCase;
class FinanceTest extends TestCase { use RefreshDatabase;
 public function test_income_expense_totals(): void { $u=User::factory()->create(); FinancialRecord::create(['user_id'=>$u->id,'type'=>'income','category'=>'Salary','amount'=>50000,'currency'=>'PKR','date'=>now()->toDateString()]); FinancialRecord::create(['user_id'=>$u->id,'type'=>'expense','category'=>'Food','amount'=>5000,'currency'=>'PKR','date'=>now()->toDateString()]); $this->assertEquals(45000, FinancialRecord::where('type','income')->sum('amount')-FinancialRecord::where('type','expense')->sum('amount')); }
}
