<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('learning_goals', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->string('title'); $t->text('description')->nullable(); $t->string('skill')->nullable(); $t->string('type')->default('Course'); $t->date('target_date')->nullable(); $t->integer('progress_percentage')->default(0); $t->string('status')->default('Active'); $t->string('resource_link')->nullable(); $t->timestamps(); $t->softDeletes(); }); Schema::create('learning_sessions', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->foreignId('learning_goal_id')->nullable()->constrained()->nullOnDelete(); $t->string('topic'); $t->date('date'); $t->integer('duration_minutes'); $t->string('skill')->nullable(); $t->text('notes')->nullable(); $t->string('resource_link')->nullable(); $t->timestamps(); $t->index(['user_id','date']); }); }
 public function down(): void { Schema::dropIfExists('learning_goals'); }
};
