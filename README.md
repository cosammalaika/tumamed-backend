# TumaMed Backend (Laravel 12)

## Local Runbook

1. Copy env and install dependencies:
   - `cp .env.example .env`
   - `composer install`
   - `npm install`
2. Configure `.env` for PostgreSQL and Redis.
3. Generate key and run migrations + seed:
   - `php artisan key:generate`
   - `php artisan migrate --force`
   - `php artisan db:seed --class=Database\\Seeders\\RolesAndPermissionsSeeder`
4. Link public storage:
   - `php artisan storage:link`
5. Start services:
   - `php artisan serve`
   - `php artisan queue:work --tries=3`
   - `npm run dev` (or `npm run build` for production assets)

## Required Services

- PostgreSQL
- Redis
- Queue worker (`php artisan queue:work --tries=3`)
# tumamed-backend
