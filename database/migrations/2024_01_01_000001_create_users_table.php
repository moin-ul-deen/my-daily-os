<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('users', function(Blueprint $t){ $t->id(); $t->string('name'); $t->string('email')->unique(); $t->timestamp('email_verified_at')->nullable(); $t->string('password'); $t->string('profile_picture')->nullable(); $t->date('date_of_birth')->nullable(); $t->string('gender',20)->nullable(); $t->string('city')->default('Faisalabad'); $t->string('country')->default('Pakistan'); $t->string('timezone')->default('Asia/Karachi'); $t->string('preferred_language')->default('en'); $t->rememberToken(); $t->timestamps(); $t->softDeletes(); }); Schema::create('password_reset_tokens', function(Blueprint $t){ $t->string('email')->primary(); $t->string('token'); $t->timestamp('created_at')->nullable(); }); Schema::create('sessions', function(Blueprint $t){ $t->string('id')->primary(); $t->foreignId('user_id')->nullable()->index(); $t->string('ip_address',45)->nullable(); $t->text('user_agent')->nullable(); $t->longText('payload'); $t->integer('last_activity')->index(); }); }
 public function down(): void { Schema::dropIfExists('users'); }
};
