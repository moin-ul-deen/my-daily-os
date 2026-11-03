# Notifications — How They Work (Simple Explanation First)

My Daily OS has **two kinds** of notifications. One of them always works. The other needs a little setup.

## 1. In-app notifications (always work)

Every time a reminder fires, a message is saved in your **Notifications inbox** (the bell at the top of the app, and "Notifications" in the menu).

- You see it even if you open the app hours later — unread ones are highlighted with a count badge on the bell.
- Tap **Mark read** on one, or **Mark all read** to clear them.
- The same reminder is **never sent twice** — the app remembers what it already sent.

**How a reminder fires:** the server checks every minute (`reminders:check`). For a reminder to fire, the Laravel scheduler must be running — on your computer the `schedule:work` command (or your OS task), and on Hostinger one cron job (see Going Live below). When its time matches — a prayer time, a task reminder, or one of your custom reminders — the notification is created.

Your custom reminders live in **Settings → Notifications**. Prayer reminders are controlled in **Salah Settings**, task reminders on each task.

## 2. Push notifications on your phone (needs setup)

Push means the message pops up on your phone/desktop even when the app is closed. For this to work, **all** of these must be true:

1. You tapped **"Enable push on this device"** (Settings → Notifications) and allowed permission. You must do this **once on each device** (phone, laptop…).
2. The server has **VAPID keys** set (see below) — these prove the messages really come from your app.
3. The site runs on **HTTPS** (true on Hostinger with its free SSL; on plain `http://localhost` push works in Chrome/Firefox only in some cases — the inbox always works).
4. **iPhone only:** use Safari to open the site, tap Share → **Add to Home Screen**, then open the app from the new icon and enable push *inside it*. This needs iOS **16.4 or newer**. Push on iPhone is added by Apple rules, not by us — without the Home Screen install, iPhones cannot receive push at all.

If push cannot be delivered (permission blocked, expired subscription), nothing breaks — the message is still in your inbox, and expired subscriptions are cleaned up automatically.

## How to test (30 seconds)

1. Go to **Settings → Notifications**.
2. Tap **Enable push on this device** and allow permission (on iPhone, do this from the Home Screen app).
3. Tap **Send test notification**.
4. Result: a test entry appears in your **Notifications inbox** immediately. If push is set up, the same test also pops up on the device. The page tells you honestly what happened: *push sent*, *skipped — device not subscribed*, or *skipped — VAPID keys not set*.

## Server setup (one time)

Generate the keys:

```
php artisan notifications:vapid
```

Copy the three lines it prints into your `.env`:

```
VAPID_PUBLIC_KEY=...
VAPID_PRIVATE_KEY=...
VAPID_SUBJECT=mailto:you@yourdomain.com
```

Then clear the cache so the app picks them up:

```
php artisan config:clear
```

Notes:

- Keep `VAPID_PRIVATE_KEY` secret — never share it or commit it to git.
- If the command says OpenSSL could not create the key (rare, minimal servers), run it as `OPENSSL_CONF=/etc/ssl/openssl.cnf php artisan notifications:vapid`, or generate keys with `npx web-push generate-vapid-keys` and paste the values in the same `.env` lines.
- After changing the VAPID keys, every user must tap **Enable push on this device** once again (old subscriptions belong to the old keys).

## Going live on Hostinger (cron — required)

Reminders only fire while the scheduler runs. On Hostinger add this cron job (hPanel → Advanced → Cron Jobs → every minute):

```
* * * * * cd /home/youruser/domains/yourdomain/public_html && php artisan schedule:run >> /dev/null 2>&1
```

(Ask Hostinger support for your exact `php` path if the job does not run — some accounts need `php8.3` instead of `php`.)

On your own computer while testing, just leave this running in a terminal:

```
php artisan schedule:work
```

## Quick troubleshooting

| Problem | Likely cause | Fix |
|---|---|---|
| Nothing in the inbox, ever | Scheduler/cron not running | Start `php artisan schedule:work` locally, or fix the Hostinger cron |
| Inbox works, no pop-up on phone | Push not enabled on that device, or VAPID keys missing | Settings → Notifications → Enable push; check `.env` keys + `config:clear` |
| Test page says "VAPID keys not set" | Keys not in `.env` (or config cached) | Run `php artisan notifications:vapid`, paste keys, `php artisan config:clear` |
| iPhone gets no push | Not installed to Home Screen, or iOS older than 16.4 | Add to Home Screen, open from the icon, enable push there |
| Same reminder twice | Should not happen — dedupe is by user + reminder + day | Check `notification_logs` for the reference key; report it as a bug |
