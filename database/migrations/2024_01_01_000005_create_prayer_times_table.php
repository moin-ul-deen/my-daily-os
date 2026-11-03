<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('prayer_times', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->date('date'); $t->time('fajr'); $t->time('sunrise')->nullable(); $t->time('dhuhr'); $t->time('asr'); $t->time('maghrib'); $t->time('isha'); $t->string('calculation_method')->default('Karachi'); $t->string('source')->default('calculated'); $t->timestamps(); $t->unique(['user_id','date']); $t->index(['user_id','date']); }); }
 public function down(): void { Schema::dropIfExists('prayer_times'); }
};
