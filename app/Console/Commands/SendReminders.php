<?php
namespace App\Console\Commands; use Illuminate\Console\Command; use App\Services\ReminderService;
class SendReminders extends Command { protected $signature='reminders:check'; protected $description='Check and dispatch due reminders without duplicates'; public function handle(ReminderService $s): int { $n=$s->checkAll(); $this->info("Reminders dispatched: $n"); return self::SUCCESS; } }
