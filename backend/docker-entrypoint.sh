#!/bin/sh
set -e

php artisan config:clear
php artisan storage:link || true
php artisan migrate --force

# Scheduled jobs (stations:sync every 30 min, etc. — see app/Console/Kernel.php) run in the
# background; the web server is the container's main foreground process.
php artisan schedule:work &

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8001}"
