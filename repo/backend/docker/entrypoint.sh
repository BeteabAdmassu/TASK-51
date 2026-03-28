#!/bin/sh
set -e

if [ ! -f ".env" ] && [ -f ".env.example" ]; then
  cp .env.example .env
fi

if [ ! -d "vendor" ]; then
  composer install --no-interaction --prefer-dist
fi

if ! grep -q "^APP_KEY=base64:" .env; then
  php artisan key:generate --force
fi

if [ "$DB_CONNECTION" = "mysql" ]; then
  echo "Waiting for MySQL..."
  for i in $(seq 1 60); do
    php -r "try { new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); echo 'ok'; } catch (Throwable $e) { exit(1); }" >/dev/null 2>&1 && break
    sleep 2
  done
fi

php artisan migrate --force

USER_COUNT=$(php artisan tinker --execute="echo Illuminate\\Support\\Facades\\Schema::hasTable('users') ? App\\Models\\User::count() : 0;" --no-interaction)

if [ "${USER_COUNT:-0}" -eq 0 ]; then
  php artisan db:seed --force
fi

exec "$@"
