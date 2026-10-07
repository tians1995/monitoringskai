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
