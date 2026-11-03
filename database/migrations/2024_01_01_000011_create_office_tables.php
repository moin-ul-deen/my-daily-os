<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('office_records', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->date('date'); $t->dateTime('work_start')->nullable(); $t->dateTime('work_end')->nullable(); $t->integer('break_minutes')->default(0); $t->decimal('total_hours',6,2)->nullable(); $t->boolean('is_late')->default(false); $t->boolean('is_leave')->default(false); $t->string('leave_type')->nullable(); $t->text('notes')->nullable(); $t->timestamps(); $t->unique(['user_id','date']); }); Schema::create('salary_records', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->date('month'); $t->decimal('amount',12,2); $t->string('currency',10)->default('PKR'); $t->decimal('overtime_amount',12,2)->default(0); $t->text('notes')->nullable(); $t->timestamps(); }); }
 public function down(): void { Schema::dropIfExists('office_records'); }
};
