<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
   if (!Schema::hasTable('notification_logs')) return;
   Schema::table('notification_logs', function (Blueprint $t) {
     if (!Schema::hasColumn('notification_logs', 'title')) $t->string('title')->nullable();
     if (!Schema::hasColumn('notification_logs', 'body')) $t->text('body')->nullable();
     if (!Schema::hasColumn('notification_logs', 'read_at')) $t->timestamp('read_at')->nullable();
   });
 }
 public function down(): void {
   if (!Schema::hasTable('notification_logs')) return;
   Schema::table('notification_logs', function (Blueprint $t) {
     foreach (['title', 'body', 'read_at'] as $col) {
       if (Schema::hasColumn('notification_logs', $col)) $t->dropColumn($col);
     }
   });
 }
};
