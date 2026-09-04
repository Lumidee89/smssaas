# SchoolOS Production Runbook

## Required infrastructure

- PHP 8.3+, Nginx, MySQL 8+, Redis 7+, Node 22 for builds, and Supervisor.
- TLS termination with HTTPS forced at the load balancer or Nginx.
- Separate production credentials for BulkSMSLive, Paystack, Firebase, mail, and the AI provider.
- Automated encrypted database and uploaded-file backups stored outside the application host.

## Environment

Start from `.env.example`, store secrets in the hosting platform's secret manager, and never commit `.env`. Production must set `APP_ENV=production`, `APP_DEBUG=false`, an HTTPS `APP_URL`, `QUEUE_CONNECTION=redis`, `CACHE_STORE=redis`, `SESSION_DRIVER=redis`, `SESSION_ENCRYPT=true`, and `SESSION_SECURE_COOKIE=true`.

Set a unique `HORIZON_PREFIX` per environment. Configure `CONTENT_SECURITY_POLICY` after confirming any additional analytics or asset hosts. Rotate the previously exposed BulkSMSLive credential before deployment.

Validate configuration before accepting traffic:

```bash
php artisan schoolos:production-check
```

## Deployment

```bash
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
npm ci
npm run build
php artisan down --retry=60
php artisan migrate --force
php artisan optimize
php artisan storage:link
php artisan horizon:terminate
php artisan up
```

Use `deploy/nginx-schoolos.conf` and `deploy/supervisor-schoolos.conf` as host templates. Keep at least one prior release and its matching database backup available for rollback.

## Health and operations

- `GET /up` is the process liveness probe.
- `GET /health/ready` checks database and cache readiness and returns HTTP 503 on failure.
- `/horizon` is restricted to authenticated `super_admin` users outside local environments.
- Every response includes `X-Request-ID`; include it when investigating logs or support cases.
- Alert on readiness failures, HTTP 5xx rate, queue wait time, failed jobs, payment webhook failures, disk usage, and backup age.

Run one scheduler process and one Horizon master per application cluster. Review failed jobs daily and retry only after correcting the underlying cause.

## Backup and recovery

Back up MySQL and user uploads at least daily, retain daily/weekly/monthly copies according to policy, encrypt them, and test restore quarterly. Before a schema-changing deployment, take an on-demand backup. A rollback consists of restoring the prior release, applying a compatible database restoration or forward fix, running `php artisan optimize`, and terminating Horizon so workers reload code.

## Mobile release

Set the parent app API base URL to the production HTTPS endpoint, then run `flutter analyze` and `flutter test`. Build signed Android/iOS releases through protected CI secrets; do not store signing material in the repository.
