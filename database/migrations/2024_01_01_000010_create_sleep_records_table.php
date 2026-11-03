<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('sleep_records', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->dateTime('sleep_start'); $t->dateTime('wake_time')->nullable(); $t->integer('duration_minutes')->nullable(); $t->tinyInteger('quality_rating')->nullable(); $t->text('notes')->nullable(); $t->timestamps(); $t->index(['user_id','sleep_start']); }); }
 public function down(): void { Schema::dropIfExists('sleep_records'); }
};
