<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('prayer_records', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->string('prayer_name',20); $t->date('prayer_date'); $t->time('scheduled_time')->nullable(); $t->time('actual_time')->nullable(); $t->enum('status',['waqt_par','late','qaza','missed'])->nullable(); $t->text('note')->nullable(); $t->timestamps(); $t->unique(['user_id','prayer_name','prayer_date']); $t->index(['user_id','prayer_date']); }); }
 public function down(): void { Schema::dropIfExists('prayer_records'); }
};
