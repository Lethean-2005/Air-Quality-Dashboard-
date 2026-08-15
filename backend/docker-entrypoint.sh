#!/bin/sh
set -e

php artisan config:clear
php artisan storage:link || true
php artisan migrate --force

# schedule:work used to run here in the background, but on Render's free tier it starves
# the web server of CPU whenever a sync job is active (iqair:sync-country in particular
# waits 15.5s between cities and can run for minutes) — every page load would crawl to
# 20-30s regardless of what it actually needed. Scheduled jobs (see app/Console/Kernel.php)
# now need a separate scheduling mechanism (a dedicated Render Cron Job service, or an
# external trigger hitting a scheduled-command endpoint) instead of running in-process here.

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8001}"
