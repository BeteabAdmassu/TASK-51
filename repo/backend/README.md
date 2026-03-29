# RoadLink Backend (Laravel API)

## Local Non-Docker Verification

### Prerequisites

- PHP 8.2+
- Composer 2+
- MySQL 8+
- PHP extensions: `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`

### Bootstrap + migrate + test

From `repo/backend`:

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan test
```

Or run the bundled verification script:

```bash
bash scripts/verify-local.sh
```

### Verification Checklist (Expected Pass Indicators)

1. `composer install` completes without dependency errors.
2. `php artisan key:generate` returns success and sets `APP_KEY`.
3. `php artisan migrate:fresh --seed` completes with all migrations/seeders applied.
4. `php artisan test` finishes with all tests passing.
5. Quick smoke endpoint checks:
   - `GET /api/v1/auth/me` without token returns `401` JSON.
   - `GET /api/v1/reports/trends` without token returns `401` JSON.

If any step fails, confirm DB connectivity (`DB_*` values in `.env`) and required PHP extensions.

## Report Export Destination Semantics

- `destination` in `POST /api/v1/reports/export` is a **safe logical key**, not a raw filesystem path.
- Allowed characters: `A-Z`, `a-z`, `0-9`, `_`, `-`.
- Stored path is always rooted under local storage: `storage/app/exports/<destination>/...`.
- Path traversal and unsafe characters are rejected by validation.

## Security Notes

- Report downloads require: valid signed URL + `auth:sanctum` + non-expired token + role (`admin`/`fleet_manager`) + ownership authorization.
- Media downloads require signed URL and object-level owner/admin checks.

## Session + Notification Config

- `SESSION_LIFETIME` defaults to `720` minutes (12-hour web session window).
- Notification channels are selected via `ROADLINK_NOTIFICATION_CHANNELS` (default `in_app`).
- SMS delivery adapter can be listed as a channel, but is disabled by default unless `ROADLINK_SMS_ENABLED=true`.

## Readiness + Schema Drift Recovery

- `GET /api/v1/readiness` reports whether runtime schema is ready for ride completion notifications.
- If `notification_frequency_type_column` is `false`, ride completion still degrades safely, but you should migrate immediately.
- Application startup logs a warning when `notification_frequency_logs.type` is missing.

Local non-Docker recovery steps from `repo/backend`:

```bash
php artisan migrate
php artisan optimize:clear
php artisan test
```

For SQLite local verification (no MySQL driver):

```bash
DB_CONNECTION=sqlite DB_DATABASE="$(pwd)/database/database.sqlite" php artisan migrate
```
