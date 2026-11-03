# Hostinger Deployment Guide

## 1. Create MySQL database
hPanel → Databases → MySQL: create database + user, note credentials (never commit them).

## 2. Upload project
Upload the ZIP via File Manager or Git deployment, extract OUTSIDE public_html if possible (e.g. `~/my-daily-os`), or into a subfolder.

## 3. Document root (safest)
Best: point the domain/subdomain document root to the project's `public/` folder (hPanel → Websites → Dashboard → Document Root, or create a subdomain whose root you can set).
Alternative if docroot cannot change: move `public/*` contents into `public_html`, and edit `public_html/index.php` to require `../my-daily-os/vendor/autoload.php` and `../my-daily-os/bootstrap/app.php` with correct relative paths. Keep `.env`, `vendor`, `storage` OUTSIDE public_html in both cases.

## 4. Environment
Copy `.env.example` to `.env`, set: APP_URL=https://yourdomain.com, APP_ENV=production, APP_DEBUG=false, DB_HOST (usually localhost), DB_DATABASE/USERNAME/PASSWORD from step 1, VAPID keys if using push.

## 5. Install (SSH if available)
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force --seed
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
No-SSH alternative: run `composer install` locally, upload `vendor/` with the project, create `.env` via File Manager, then trigger migrations by temporarily visiting a route is NOT recommended - use hPanel's Terminal if available, or Hostinger's PHP/ cron one-off: create a cron job that runs once: `php artisan migrate --force` and delete it after it runs. Check `storage/logs/laravel.log` for errors.

## 6. Permissions
`storage/` and `bootstrap/cache/` must be writable by PHP (755 folders / 644 files, 775 if group-writable is required by your plan).

## 7. Cron (Scheduler) - REQUIRED
hPanel → Advanced → Cron Jobs, every minute:
```
* * * * * php /home/USER/domains/DOMAIN/my-daily-os/artisan schedule:run >> /dev/null 2>&1
```
Adjust path to your install.

## 8. HTTPS
Enable free SSL (Lifetime SSL / Let's Encrypt) in hPanel and force HTTPS. PWA install + service worker + push require HTTPS.

## 9. Backup
hPanel → Files → Backups for files; phpMyAdmin → Export for database. Also use in-app Settings → Export for personal data JSON/CSV. Restore: import SQL in phpMyAdmin, restore files, re-run cache commands above.
