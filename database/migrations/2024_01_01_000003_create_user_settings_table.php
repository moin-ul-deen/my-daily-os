<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('user_settings', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); $t->string('currency',10)->default('PKR'); $t->string('weight_unit',10)->default('kg'); $t->string('distance_unit',10)->default('km'); $t->string('time_format',10)->default('12'); $t->string('date_format')->default('Y-m-d'); $t->string('theme')->default('dark'); $t->string('accent_color')->default('#8BCB9B'); $t->decimal('target_weight',6,2)->nullable(); $t->integer('water_target_ml')->default(2500); $t->decimal('walking_target_km',6,2)->default(5); $t->integer('weekly_exercise_sessions')->default(3); $t->time('office_start')->default('17:00:00'); $t->time('office_end')->default('02:00:00'); $t->json('working_days')->nullable(); $t->decimal('monthly_salary',12,2)->default(50000); $t->boolean('notifications_enabled')->default(true); $t->time('quiet_start')->nullable(); $t->time('quiet_end')->nullable(); $t->timestamps(); }); }
 public function down(): void { Schema::dropIfExists('user_settings'); }
};
