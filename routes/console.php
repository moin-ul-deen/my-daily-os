<?php
use Illuminate\Support\Facades\Schedule;
Schedule::command('reminders:check')->everyMinute()->withoutOverlapping();
// Prayer times are calculated on demand and cached in prayer_times; no external API cron required.
