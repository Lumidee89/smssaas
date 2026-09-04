# SchoolOS implementation status

Updated September 2026. This maps the PRD and Enterprise Blueprint to the codebase. Automated tests do not replace staging, security, load, backup/restore, provider, DNS, and app-store acceptance testing.

## Delivered expansion

1. Distinct principal, vice principal, academic admin, and bursar roles with route permissions and role-aware navigation.
2. Tenant subdomains, custom-domain DNS verification, host resolution, and cross-tenant rejection. The current MySQL deployment uses `TENANCY_MODE=shared`, with every tenant-owned query constrained by `school_id`. PostgreSQL deployments can optionally use `TENANCY_MODE=schema`; MySQL physical database-per-tenant isolation requires a separate connection/migration architecture and is not represented as schema isolation.
3. Parent selection of ordered invoice installments, exact-amount checkout, and settlement from verified Paystack callbacks.
4. Parent conversations addressed to a selected teacher, principal, academic officer, or bursar.
5. Revenue/attendance trends, fee aging, grade distribution, forecasts, and explainable dropout-risk ranking.
6. Published homework and upcoming events on the parent dashboard.
7. Embedded QR codes on transcripts linking to public verification.
8. Parent mobile read caches, offline status, queued supported mutations, and automatic retry.
9. Attendance, announcement, and payment domain events with queued notification listeners.
10. A separate `student_app` with student accounts, Sanctum auth, dashboard, results, attendance, homework, events, CBT discovery, refresh, and offline cached reads.
11. Tenant-scoped hostel allocation, library circulation, transport assignments, wallets, vendors/products, and atomic wallet-funded marketplace ordering.

## Operational checks before release

- Back up, apply migrations, and provision schemas in a maintenance window.
- Configure wildcard/custom-domain DNS and TLS, Paystack secrets, push/SMS/mail providers, workers, scheduler, cache, and observability.
- Run `php artisan test`, `flutter analyze`, and `flutter test` for both mobile apps in CI.
- Test tenant boundaries against production MySQL. Keep `TENANCY_MODE=shared`; MySQL has no PostgreSQL-style `search_path` schemas.
- Perform tenant-escape, authorization, webhook replay, wallet concurrency, accessibility, device/network-loss, load, and disaster-recovery tests in staging.

## Boundaries

Forecasts and dropout risk are decision-support indicators, not autonomous decisions. Marketplace settlement currently uses the school wallet; external vendor payouts need a selected provider. Offline support preserves reads and queues supported parent mutations, while login and payment authorization remain online-only.
