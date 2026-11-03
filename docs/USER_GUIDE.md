# My Daily OS — User Guide

*Written for daily use, no technical knowledge needed. For setup and server topics, see `README.md`, `docs/HOSTINGER_DEPLOYMENT.md` and `docs/NOTIFICATIONS.md`.*

## What is this app?

My Daily OS is your personal daily system in one place: your tasks, your five prayers with correct times for your city, your health (water, weight, walking, exercise, food, sleep), your office routine, your freelancing work and income, your learning, your habits, and your money — plus reports that show you honestly how your days, weeks and months are going.

Everything you enter is **saved permanently in the database**, day by day. Yesterday's records never change when you edit today's. Nothing is fake: every number in the app comes from records you entered.

---

## First-day setup (do this once)

1. **Register** your account and log in. Your settings are created automatically with defaults: Faisalabad, Pakistan, timezone Asia/Karachi, currency PKR, weight in kg, distance in km.
2. Open **Settings** and check: your city, timezone, water target (ml per day), walking target, office start/end time, working days, and monthly salary. Change anything that is different for you — everything is editable.
3. Open **Salah → Settings** and confirm the prayer settings (explained in the Salah section below).
4. Open **Settings → Notifications**: add any custom reminders you want (for example "Drink a glass of water — 11:00"), and tap **Send test notification** to prove notifications reach you. Details: `docs/NOTIFICATIONS.md`.
5. Optional: add your regular tasks as **recurring tasks**, and create your **habits** (below).

That's it. From tomorrow, just follow the daily routine.

---

## Your daily routine

### Morning
- Open the **Dashboard**. It shows today's date, your day's progress, today's five prayer times with the **next prayer and the time left**, and a health summary.
- Check **My Day** (tasks): see today's tasks. Mark yesterday's leftovers done, skipped, or postpone them to tomorrow.
- After each prayer, open **Salah** and set its status (below). It takes two seconds.

### During the day
- **Water:** on the Health → Water page, tap a quick-add button (+250 ml / +500 ml…) each time you drink, or add an exact amount. The page shows how much of your daily target is left.
- **Meals:** log what you ate under **Meals** (breakfast/lunch/dinner/snack). Calories and nutrients are optional — fill them only if you know them.
- **Walking / Exercise:** log walks and workouts under **Health**. Multiple exercise sessions per day are fine.
- **Tasks:** tick tasks off as you finish them; add new ones as they come up.
- **Work:** during office hours, mark attendance in **Office**. Freelance work goes under **Freelancing**; money in or out goes under **Finance** the moment it happens, so nothing is forgotten.

### Night
- Record **weight** (if you weigh daily) under Health → Weight.
- When you go to sleep, either tap **Start sleep** in **Sleep**, or add the record next morning manually — both work.
- Open **Reports → Daily** for an honest picture of your day: tasks, prayers, water, meals, exercise, sleep, learning, money.
- Glance at the **bell (Notifications)** — anything you missed during the day is waiting there.

---

## Every module, explained

### Dashboard
Your home screen. Greeting by time of day, today's task progress (total / done / percentage), Salah overview with next prayer and countdown, health overview (water, meals, walking, exercise, latest weight, sleep), and quick-action buttons to add a task, log a meal or water, record weight or exercise, update a prayer, or start/end sleep. If a card is empty, it means nothing is recorded yet — it never invents numbers.

### My Day / Tasks
Your to-do list, one record per day. Each task has a title, category (Health, Salah, Job, Freelancing, Learning, Personal, Family, Finance, Other), date, optional start/end time, priority (Low → Urgent), status, and an optional reminder (minutes before start time).
- **Complete / Reopen:** finishing a task stamps the exact completion time.
- **Postpone** moves it one day forward. **Duplicate** copies it.
- **Recurring tasks** (daily, weekly, weekdays, monthly, custom): the app creates a separate copy for each day. Editing tomorrow's copy never touches yesterday's history.
- Filter by date or category, or search by title.

### Salah
The five daily prayers: Fajr, Dhuhr, Asr, Maghrib, Isha.
- **Times are calculated**, not typed in: from your city's coordinates, the sun's position, your chosen Fajr/Isha calculation convention, and the **Hanafi** rule for Asr (shadow factor 2 — this is only about Asr; it does not change Fajr/Isha). Defaults: Faisalabad (31.4504, 73.1350), Karachi convention (Fajr 18°, Isha 18°). Other conventions (MWL, ISNA, Egypt, Makkah) and per-prayer minute adjustments (± minutes) are in **Salah → Settings**. The convention used is always shown.
- **Statuses are exactly four, plus unmarked:** *Waqt par* (on time), *Late*, *Qaza*, *Missed*. The app **never marks a prayer missed by itself** — you choose, or leave it unmarked.
- Each day has its own permanent record. History and weekly/monthly performance are on the Salah pages.
- **Reminders:** in Salah Settings you can enable a reminder before each prayer, at prayer time, and a follow-up — each prayer separately.

### Health — Weight, Water, Walking, Exercise
- **Weight:** add a dated entry in kg whenever you weigh. Old entries are never overwritten. You get a trend graph, highest/lowest, and change over any period.
- **Water:** every glass is its own record with date, time and ml. Set your daily target in Settings; the page shows total and what's left. Quick-add buttons make it one tap.
- **Walking:** date, distance (km), duration, optional steps and calories (calories are an estimate — treat them as rough).
- **Exercise:** walking, running, cycling, gym, push-ups, squats, stretching, or your own — with duration, sets, reps, and weight/resistance where they apply.

### Meals
Your food diary. For each meal choose Breakfast, Lunch, Dinner, Snack or Custom, add one or more foods with quantity/portion, time, whether it was home-cooked or outside food, and notes. Calories, protein, carbs and fats are **optional** — only enter values you actually know; they are your estimates, the app never invents nutrition data. Reports show home-cooked vs outside food balance.

### Sleep
Tap **Start sleep** when you go to bed and **End** when you wake, or enter a record manually afterwards. Sleep that **crosses midnight is handled correctly** — sleeping 6:00 AM to 2:00 PM, or 11 PM to 5 AM, both record the true duration. The app warns you if a new record overlaps an existing one, and shows daily duration, weekly average and consistency.

### Office
Your job routine. Defaults: **5:00 PM to 2:00 AM** (an overnight shift — the end is next calendar day, handled automatically), salary **PKR 50,000/month** — all editable in Settings.
Per day you record: present/leave, actual start and end, break minutes; the app calculates total hours and flags late arrivals. Monthly summary and salary history are on the Office page. Overtime is simply any time beyond your set office hours.

### Freelancing
- **Projects:** name, client, platform (Fiverr, Upwork, Direct, Personal website, Other), dates and deadline, status (Idea → Planning → In progress → Waiting for client → Completed / Cancelled), priority, estimated value and actual revenue.
- **Income:** date, project/client, platform, gross income, platform fees, net (gross − fees), currency (PKR or USD), payment status. Currencies are **never converted with a made-up rate** — if you want a combined figure, type the exchange rate yourself on the record.
- Reports show monthly freelance income, totals, pending payments and average project value.

### Learning
Set **learning goals** (a course, a skill, a book) with a target date and progress percentage, and log **sessions**: topic, date, minutes spent, related skill, notes, optional resource link. Your reports add up learning time per week and month, so you can see whether the habit is real.

### Habits
Create your own habits (the list is yours — waking on time, reading, walking, water, anything) with a category, frequency and target. Each day you tick the habit for **that date only**; history is kept per day, so you can see weekly consistency, monthly completion rate and your current streak. Switching a habit off never deletes its past records.

### Finance
One list for money in and out.
- **Income categories:** Salary, Freelancing, Business, Other.
- **Expense categories:** Food, Transport, Shopping, Bills, Family, Health, Education, Entertainment, Other.
- Every record: type, category, amount, currency, date, payment method, description, optional attachment.
- Monthly pages show income, expenses, net savings and a category breakdown. Salary and regular expenses can be recurring. Nothing is ever created automatically without you entering it — this is a personal tracker, not a bank connection.

### Calendar
A month view of everything. Pick any date to see that day's tasks, prayers, meals, sleep, exercise, money and learning together. Use it to review or fix an old day — editing keeps the original creation time, so your history stays trustworthy.

---

## Reports & graphs — how to read them

Open **Reports**. Every chart is drawn from your own records only.

- **Daily:** one day in full — tasks, prayer statuses, water, meals, exercise, walking, sleep, learning, income/expenses.
- **Weekly:** this week side by side with last week, so you can see if you improved.
- **Monthly:** task and prayer consistency, weight trend (line), water and walking (lines), exercise sessions, average sleep, freelance hours and income, expenses vs savings.
- **Yearly & Custom:** pick any date range for the same summaries.
- **Chart types:** doughnut = prayer status mix (how many Waqt par / Late / Qaza / Missed); bars = money per category or per day; lines = trends over time (weight, sleep, water, walking, tasks per day).

**How to read a graph honestly:** a line going up for weight means your logged weights went up — check you log at the same time of day for fair comparisons. A prayer doughnut that is mostly unmarked usually means "I forgot to mark", not "I missed" — marking takes two seconds after each prayer. If a week looks empty, the data wasn't entered; the app never fills gaps with guesses.

---

## Notifications — turn them on and prove they work

Short version (full guide: `docs/NOTIFICATIONS.md`):

1. Reminders fire from the server every minute — prayer reminders, task reminders, and your custom reminders (Settings → Notifications).
2. Every fired reminder lands in your **Notifications inbox** (bell icon). This always works, even if you open the app later.
3. For pop-ups on your phone: Settings → Notifications → **Enable push on this device** → allow. iPhone: install to Home Screen first (iOS 16.4+).
4. **Test it:** tap **Send test notification** on the same page. If it appears in the inbox (and pops up), you are done. The page tells you if push was skipped and why.

---

## Backup & export

Your data is yours.
- **Settings → Export** (Export page): download any table as **CSV**, or a **complete personal export** (all your records in one file).
- Print any report page (browser Print → Save as PDF) for a paper/PDF copy.
- Server backup: copy the MySQL database regularly (Hostinger: phpMyAdmin → Export). Steps are in `docs/HOSTINGER_DEPLOYMENT.md`.
- You can delete individual records anywhere, and delete your whole account from Profile — the app never deletes your data on its own.

---

## Going-live checklist (Hostinger)

Full instructions: `docs/HOSTINGER_DEPLOYMENT.md`. The short list:

1. Upload the project, point the domain's document root at the `public` folder (or use the documented subdirectory method).
2. Create a MySQL database in hPanel; put its name/user/password in `.env` (copy `.env.example`).
3. Run `composer install`, `php artisan key:generate`, `php artisan migrate --force`, `php artisan storage:link` (Terminal in hPanel, or the no-SSH method in the guide).
4. Turn on the free SSL (HTTPS) in hPanel — push notifications and PWA install need it.
5. Add the **cron job every minute**: `php artisan schedule:run` (exact line in `docs/NOTIFICATIONS.md`). Without this, reminders never fire.
6. Generate **VAPID keys**: `php artisan notifications:vapid`, paste into `.env`, `php artisan config:clear`.
7. Register your account, set your city/settings, enable push on your phone, and send a test notification.

---

## FAQ

**The prayer time looks a few minutes off my masjid's timetable.**
Masjids often add safety minutes. In Salah → Settings, add a per-prayer adjustment (e.g. Fajr +3) — or check that the calculation convention matches your area. Asr follows the Hanafi rule by default, as configured.

**I forgot to mark a prayer / log a meal yesterday. Can I fix it?**
Yes. Open Calendar, pick the date, and edit — or use the module's page for that date. Your edit is saved with its original creation time preserved.

**Why is a graph empty?**
No records for that period yet. The app shows real data only — it never displays sample numbers.

**Do notifications work if I deny permission?**
Yes — the in-app inbox keeps working. Only phone pop-ups need permission.

**I use the app on my phone and laptop. Do they sync?**
Yes. Everything is stored on the server; log in anywhere and you see the same records. (Offline typing is not queued — enter records while connected.)

**I entered a wrong amount / weight. Do I lose history if I edit?**
No. Editing fixes that one record; every other day's history stays exactly as it was.

**What if I stop using a habit?**
Set it inactive. Its past daily records remain in your history and reports.
