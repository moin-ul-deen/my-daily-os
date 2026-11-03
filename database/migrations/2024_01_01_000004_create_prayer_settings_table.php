<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('prayer_settings', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); $t->string('city')->default('Faisalabad'); $t->string('country')->default('Pakistan'); $t->decimal('latitude',10,6)->default(31.450400); $t->decimal('longitude',10,6)->default(73.135000); $t->string('timezone')->default('Asia/Karachi'); $t->string('calculation_method')->default('Karachi'); $t->decimal('fajr_angle',5,2)->default(18); $t->decimal('isha_angle',5,2)->default(18); $t->string('asr_juristic')->default('Hanafi'); $t->integer('asr_shadow_factor')->default(2); $t->string('high_latitude_rule')->default('AngleBased'); $t->json('adjustments')->nullable(); $t->json('reminder_settings')->nullable(); $t->timestamps(); }); }
 public function down(): void { Schema::dropIfExists('prayer_settings'); }
};
