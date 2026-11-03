<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('habits', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->string('name'); $t->text('description')->nullable(); $t->string('category')->default('Personal'); $t->string('frequency')->default('daily'); $t->string('target')->nullable(); $t->date('start_date'); $t->date('end_date')->nullable(); $t->boolean('is_active')->default(true); $t->timestamps(); $t->softDeletes(); }); Schema::create('habit_logs', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->foreignId('habit_id')->constrained()->cascadeOnDelete(); $t->date('date'); $t->boolean('completed')->default(false); $t->text('note')->nullable(); $t->timestamps(); $t->unique(['habit_id','date']); }); }
 public function down(): void { Schema::dropIfExists('habits'); }
};
