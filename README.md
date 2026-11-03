# My Daily OS

Personal Life Management, Productivity & Accountability System. Laravel 12 / PHP 8.2+ / MySQL 8 / Blade / Bootstrap 5 / Chart.js / PWA.

Manages: daily tasks & routines, five daily Salah with calculated prayer times (Hanafi Asr), health (weight, water, walking, exercise), meals, sleep (cross-midnight), office routine (overnight shifts), freelancing & income, learning, habits, finance, calendar, reports, reminders, exports.

## Local Setup
Requirements: PHP 8.2+, Composer 2, MySQL 8 (MariaDB 10.6+ works), Node not required (CDN assets).

```bash
cp .env.example .env
# edit DB_* in .env to your MySQL credentials
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
# open http://127.0.0.1:8000 - register your account
```
First user: settings auto-created with defaults - Faisalabad, Pakistan, Asia/Karachi, PKR, kg/km, Hanafi Asr, Karachi calculation convention (Fajr 18°, Isha 18°), office 17:00-02:00, salary PKR 50,000, water target 2500 ml.

## Tests
```bash
php artisan test
# phpunit.xml uses SQLite in-memory ONLY for tests. Production must use MySQL.
```

## Prayer Times
Calculated in `app/Services/PrayerTimeService.php` using solar position math (PrayTimes algorithm) - never hardcoded. Presets: Karachi, MWL, ISNA, Egypt, Makkah, Custom. Asr juristic (Hanafi = shadow factor 2) is separate from the Fajr/Isha convention. Per-prayer minute adjustments in Salah Settings. Daily results cached in `prayer_times` table. If calculation fails, the last valid cached times are retained and a warning is logged.

Statuses are exactly: Waqt par (`waqt_par`), Late, Qaza, Missed, or Unmarked (no record). The app never auto-marks a prayer missed.

## Cron (required for reminders)
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```
Scheduler runs `reminders:check` every minute with duplicate prevention via `notification_logs`.

## Web Push (optional)
Generate VAPID keys with `php artisan notifications:vapid`, set `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT` in `.env`, then `php artisan config:clear`. Real push delivery is implemented via `minishlink/web-push`. How notifications work, how to test them, and iOS rules: `docs/NOTIFICATIONS.md`. New to the app? Start with the non-technical `docs/USER_GUIDE.md`.

## Deployment
See `docs/HOSTINGER_DEPLOYMENT.md`. Schema: `docs/DATABASE_SCHEMA.md`. Checklist: `docs/FEATURE_CHECKLIST.md`. Limitations: `docs/KNOWN_LIMITATIONS.md`.

## How to use each module

- **Dashboard** — Your home page: today's date and greeting, task progress, next prayer, health snapshot, quick-action buttons and progress graphs. Start and end your day here.
- **My Day / Tasks** — Plan your day: add tasks with a date, category and priority, set reminders, and repeat routine tasks (daily/weekly/monthly) — each day gets its own copy, so history never changes. Complete, postpone or duplicate tasks from the list.
- **Salah** — See the five calculated prayer times for your city and mark each prayer after you pray: Waqt par, Late, Qaza or Missed (or leave Unmarked — nothing is auto-marked). Calculation convention, Hanafi Asr and minute adjustments are in Salah Settings.
- **Health (Water, Weight, Walking, Exercise)** — Log water (quick-add buttons: +250 ml to +1 litre), record weight as dated history entries, and log walks and exercise sessions. Set your daily targets in Settings.
- **Meals** — A simple food diary: choose Breakfast/Lunch/Dinner/Snack, write the food name, tick home-cooked or outside. Exact calories are not required.
- **Sleep** — Tap Start Sleep when you go to bed and Wake Up when you rise, or add a manual entry. Sleep crossing midnight (e.g. 6:00 AM to 2:00 PM) is recorded correctly.
- **Office** — One record per working day with start/end times (overnight shifts supported), breaks, leaves, plus a salary record each month. Office timings and salary live in Settings.
- **Freelancing** — Track projects (client, platform, status, deadline, value) and log every payment in Freelance Income; net income is gross minus fees, calculated for you.
- **Learning** — Add goals/courses, then log each study session with topic and minutes; update the goal's progress % as you learn.
- **Habits** — Create habits with a clear target and tap Mark Done each day you do them; every date is stored separately for streaks and history.
- **Finance** — Record income (Salary, Freelancing) and expenses (Food, Transport, Bills and more) with amounts and dates; this is personal tracking only, not connected to any bank.
- **Calendar** — Pick any date to see everything recorded that day — tasks, prayers, meals, sleep, exercise, finance, learning and habits — plus a month overview.
- **Reports** — Daily, weekly, monthly, yearly and custom-date summaries with charts, calculated only from your real records. Weekly compares with the previous week.
- **Settings** — Your profile and password, city/timezone, health targets, office timings and salary, prayer settings, notifications/reminders, and full data export (Settings → Export & Backup). See also the in-app Help page for a guided walkthrough.
