#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if ! command -v php >/dev/null 2>&1; then
  echo "[verify-local] ERROR: php not found in PATH"
  exit 1
fi

if ! command -v composer >/dev/null 2>&1; then
  echo "[verify-local] ERROR: composer not found in PATH"
  exit 1
fi

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

composer install --no-interaction --prefer-dist
php artisan key:generate --force
php artisan migrate:fresh --seed --force
php artisan test
