<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('reminders', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->string('type'); $t->string('title'); $t->time('time')->nullable(); $t->integer('minutes_before')->nullable(); $t->boolean('is_enabled')->default(true); $t->json('days')->nullable(); $t->timestamps(); }); Schema::create('notification_subscriptions', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->text('endpoint'); $t->string('public_key')->nullable(); $t->string('auth_token')->nullable(); $t->string('content_encoding')->nullable(); $t->timestamps(); $t->unique(['user_id','endpoint'], 'notif_sub_unique'); }); Schema::create('notification_logs', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->string('type'); $t->string('reference_key'); $t->string('channel')->default('in_app'); $t->timestamp('sent_at'); $t->timestamps(); $t->unique(['user_id','type','reference_key','channel']); }); }
 public function down(): void { Schema::dropIfExists('reminders'); }
};
