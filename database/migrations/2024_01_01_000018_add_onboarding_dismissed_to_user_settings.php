<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
   if (!Schema::hasColumn('user_settings','onboarding_dismissed')) {
     Schema::table('user_settings', function (Blueprint $t) { $t->boolean('onboarding_dismissed')->default(false); });
   }
 }
 public function down(): void {
   if (Schema::hasColumn('user_settings','onboarding_dismissed')) {
     Schema::table('user_settings', function (Blueprint $t) { $t->dropColumn('onboarding_dismissed'); });
   }
 }
};
