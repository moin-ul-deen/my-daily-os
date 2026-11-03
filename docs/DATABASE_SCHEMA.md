# Database Schema (MySQL 8, utf8mb4)

All personal tables have `user_id` FK → `users.id` ON DELETE CASCADE, indexes on (user_id, date), timestamps, and soft deletes where history matters (tasks, templates, projects, learning goals, habits, financial records, users).

| Table | Key fields / constraints |
|---|---|
| users | name, email UNIQUE, password (hashed), profile fields, city=Faisalabad, country=Pakistan, timezone=Asia/Karachi |
| password_reset_tokens | email PK |
| sessions / cache / cache_locks / jobs / job_batches / failed_jobs | Laravel framework tables |
| user_settings | user_id UNIQUE, currency PKR, kg/km, targets, office 17:00-02:00, salary 50000, theme |
| prayer_settings | user_id UNIQUE, lat 31.4504 lng 73.1350, method Karachi, fajr/isha angles, asr_juristic Hanafi, shadow factor 2, adjustments JSON, reminder_settings JSON |
| prayer_times | UNIQUE(user_id,date), fajr/sunrise/dhuhr/asr/maghrib/isha, method, source |
| prayer_records | UNIQUE(user_id,prayer_name,prayer_date), status ENUM(waqt_par,late,qaza,missed) NULL=unmarked |
| task_templates / tasks / task_completions | recurrence on template; tasks are dated instances so history is never overwritten |
| water_logs / weight_logs / walking_logs / exercise_logs | dated health entries, never overwritten |
| meals / meal_items | meal has many items, optional nutrition labelled estimates |
| sleep_records | sleep_start/wake_time datetimes, duration_minutes - supports cross-midnight & daytime sleep |
| office_records / salary_records | UNIQUE(user_id,date) for office; overnight via datetimes |
| projects / project_tasks / freelance_income | net = gross - fees computed in controller, currency + exchange_rate, no hardcoded conversion |
| learning_goals / learning_sessions | progress %, sessions with duration |
| habits / habit_logs | UNIQUE(habit_id,date) |
| financial_records | type income/expense, category, amount decimal(12,2) |
| reminders / notification_subscriptions / notification_logs | notification_logs UNIQUE(user,type,reference,channel) prevents duplicates |

Migrations: `database/migrations/2024_01_01_000001` … `000016`.
