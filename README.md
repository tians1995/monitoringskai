# Audit Finding Monitoring

Laravel 10 + React/Inertia + MySQL application for monitoring audit findings.

## Default login
- admin@audit.local / password
- auditor@audit.local / password

Administrator dapat menambahkan atau mengubah pilihan Audit melalui menu **Audit** setelah login. No LHA diisi dengan nomor saja, misalnya `1`, `2`, atau `3`.

## Local install
1. `cp .env.example .env`
2. Create MySQL database `audit_monitoring` or import `audit_monitoring.sql`.
3. `composer install`
4. `php artisan key:generate`
5. `npm install && npm run build`
6. `php artisan storage:link`
7. `php artisan serve`

Alternative database setup: `php artisan migrate --seed`.

## InfinityFree
Upload the project contents. Point the domain/subdomain document root to `/public` if supported. If the hosting account does not permit a public document root, use the provided `public/.htaccess`/hosting PHP setup and keep application files outside the web root where possible. Set `.env` with the MySQL credentials from InfinityFree. Import `audit_monitoring.sql` if Composer/Artisan access is unavailable.

The application calculates due/overdue status at request time, so the core reminder logic does not require a cron job.

## Wasmer.io with Anybuild
The root `Anybuild` file configures the Laravel/PHP and Vite build and runs migrations plus the idempotent seeder after deploy. It prepares Laravel's public storage link. For persistent uploaded evidence, attach a Wasmer volume at `/app/storage/app/public`. Set these values as Wasmer secrets/environment variables before deploying:

- `APP_KEY` — generate with `php artisan key:generate --show` in a trusted local environment.
- `APP_URL` — the public Wasmer app URL.
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` — credentials for a reachable MySQL database.
- `ADMIN_EMAIL`, `ADMIN_PASSWORD` — optional additional production administrator. By default the seeder creates the same demo accounts as local (`admin@audit.local` and `auditor@audit.local`, both with password `password`); sample findings are only created in local development.
- `SEED_DEMO_USERS=false` — set after removing the demo accounts to prevent a later deployment from recreating them.

Do not commit production secrets or `.env` to Git. The production seeder is safe to run on each deployment and does not duplicate accounts. If you delete the demo accounts, set `SEED_DEMO_USERS=false` in Wasmer so a later deployment does not recreate them.
Deploy from the Git repository with secrets configured in Wasmer; do not package a workstation `.env` when publishing from a local checkout.
