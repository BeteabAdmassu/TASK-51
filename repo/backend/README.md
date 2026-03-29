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

## Report Export Destination Semantics

- `destination` in `POST /api/v1/reports/export` is a **safe logical key**, not a raw filesystem path.
- Allowed characters: `A-Z`, `a-z`, `0-9`, `_`, `-`.
- Stored path is always rooted under local storage: `storage/app/exports/<destination>/...`.
- Path traversal and unsafe characters are rejected by validation.

## Security Notes

- Report downloads require: valid signed URL + `auth:sanctum` + non-expired token + role (`admin`/`fleet_manager`) + ownership authorization.
- Media downloads require signed URL and object-level owner/admin checks.
