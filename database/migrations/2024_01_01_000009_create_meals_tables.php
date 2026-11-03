<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('meals', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->string('meal_type'); $t->date('date'); $t->time('time')->nullable(); $t->boolean('is_home_cooked')->default(true); $t->text('notes')->nullable(); $t->timestamps(); $t->index(['user_id','date']); }); Schema::create('meal_items', function(Blueprint $t){ $t->id(); $t->foreignId('meal_id')->constrained()->cascadeOnDelete(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->string('food_name'); $t->string('quantity')->nullable(); $t->integer('calories')->nullable(); $t->decimal('protein_g',6,2)->nullable(); $t->decimal('carbs_g',6,2)->nullable(); $t->decimal('fats_g',6,2)->nullable(); $t->timestamps(); }); }
 public function down(): void { Schema::dropIfExists('meals'); }
};
