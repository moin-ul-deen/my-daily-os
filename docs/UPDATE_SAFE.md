# Safe Update Guide — My Daily OS (Local XAMPP Install)

This procedure updates your existing local installation **without losing your
account, your records, your app key, or your settings**. Follow it in order.
Do NOT skip the backups, and never delete your database.

Your setup: Windows, XAMPP, PHP 8.2.12, project at `Desktop\my-daily-os`,
app running at http://127.0.0.1:8000.

---

## The one rule that protects everything

Your **`.env` file** (in the project folder) holds your database name/password
and your **APP_KEY**. The APP_KEY encrypts your sessions and data. If you lose
it or regenerate it, you will be logged out everywhere and encrypted values
become unreadable.

**Never delete your `.env`, never run `php artisan key:generate` on an
existing install, and never let a new ZIP overwrite your `.env`.**
The update ZIP intentionally does not contain an `.env` file.

---

## Step 1 — Back up your .env

1. Go to `Desktop\my-daily-os`.
2. Copy the file named `.env` to a safe place outside the project,
   for example `Desktop\my-daily-os-backup\.env`.

## Step 2 — Back up your database (phpMyAdmin)

1. Start XAMPP (Apache + MySQL) and open http://localhost/phpmyadmin.
2. Click your database in the left sidebar (`my_daily_os`).
3. Click the **Export** tab at the top.
4. Method: **Quick**, Format: **SQL**, then click **Go / Export**.
5. Save the downloaded `.sql` file next to your `.env` backup,
   for example `Desktop\my-daily-os-backup\my_daily_os-backup.sql`.

This file contains your account and every record you have entered.

## Step 3 — Back up uploaded files

Copy the folder `my-daily-os\storage\app` into your backup folder too
(it holds profile pictures and any uploaded attachments).

## Step 4 — Replace the code (keep YOUR .env)

1. Stop the app server (in the terminal running `php artisan serve`, press Ctrl+C).
2. Rename the old project folder to `my-daily-os-old` (do not delete it yet).
3. Extract the new ZIP so the new project sits at `Desktop\my-daily-os`.
4. Copy **your** backed-up `.env` into the new `my-daily-os` folder.
   (The ZIP has only `.env.example` - that is a template, do not use it
   for an update.)
5. If you had files in the old `storage\app`, copy them back into the new
   `storage\app`.

## Step 5 — Install dependencies

Open a terminal in `Desktop\my-daily-os` and run:

    composer install

(Your composer files are pinned to PHP 8.2, matching your XAMPP - the earlier
"pint requires PHP 8.3" problem does not come back.)

## Step 6 — Update the database (safe migrations only)

    php artisan migrate

This release adds only new things; nothing is dropped or renamed:

- `2024_01_01_000017_add_inbox_fields_to_notification_logs` (if not already run)
  adds `title`, `body`, `read_at` to `notification_logs`.
- `2024_01_01_000018_add_onboarding_dismissed_to_user_settings`
  adds one `onboarding_dismissed` yes/no column to `user_settings`.

All other changes in this release are code and screens - no other migration
touches your tables, and **no migration deletes data**.

## Step 7 — Clear caches and restart

    php artisan config:clear
    php artisan view:clear
    php artisan serve

## Step 8 — Verify checklist

Open http://127.0.0.1:8000 and confirm:

- [ ] You can log in with your existing account (same email/password).
- [ ] The dashboard opens and shows your existing records.
- [ ] Your tasks, prayers, habits and logs from before the update are still there.
- [ ] Create a throwaway task, then delete it: you return to the task list
      with a "Task deleted" message (clicking delete twice must not error -
      the second click just says "Already deleted").
- [ ] The bell icon still shows your notifications.

If every box ticks, delete the `my-daily-os-old` folder whenever you are ready
(keep the `.env` + `.sql` backups).

## Rollback (if anything looks wrong)

1. Stop the server (Ctrl+C).
2. Delete the new `my-daily-os` folder and rename `my-daily-os-old` back
   to `my-daily-os`.
3. Only if the database itself looks damaged: open phpMyAdmin, select the
   `my_daily_os` database, use the **Import** tab, and import your backed-up
   `.sql` file.
4. Run `composer install`, `php artisan config:clear`, `php artisan serve`.

Because migrations in this release only **add** columns, the old code also runs
fine against the updated database - in practice, restoring the old folder
(Step 2 of rollback) is enough on its own.
