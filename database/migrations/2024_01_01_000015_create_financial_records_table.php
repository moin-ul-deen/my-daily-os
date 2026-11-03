<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('financial_records', function(Blueprint $t){ $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->enum('type',['income','expense']); $t->string('category'); $t->decimal('amount',12,2); $t->string('currency',10)->default('PKR'); $t->date('date'); $t->string('payment_method')->nullable(); $t->text('description')->nullable(); $t->string('attachment')->nullable(); $t->boolean('is_recurring')->default(false); $t->string('recurrence')->nullable(); $t->timestamps(); $t->softDeletes(); $t->index(['user_id','date','type']); }); }
 public function down(): void { Schema::dropIfExists('financial_records'); }
};
