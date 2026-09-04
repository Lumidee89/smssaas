# SchoolOS production implementation roadmap

This document is the delivery contract for the Enterprise Blueprint and PRD/TRD. A module is complete only when its schema, tenant authorization, services, web/API interfaces, tests, observability, and deployment configuration are complete.

## Delivery order

1. **Platform foundation** — tenant context, roles, policies, academic years/terms, guardians, audit log, API conventions.
2. **Parent core** — OTP authentication, linked children, dashboard, attendance, announcements, fees, Sanctum tokens.
3. **Flutter parent app** — Riverpod architecture, secure token storage, five-tab navigation, offline cache, push registration.
4. **Academic operations** — attendance workflows, assessments, grading policies, GPA/CGPA, approval, report cards, transcripts.
5. **Finance operations** — invoices, line items, installments, scholarships, expenses, payroll, receipts, Paystack webhooks, ledger and audit trail.
6. **Communication** — conversations, messages, announcements, attachments, notification preferences, FCM/BulkSMSLive/WhatsApp adapters.
7. **CBT and analytics** — question banks, attempts, auto-grading, KPI warehouse queries, risk interventions.
8. **AI and SaaS administration** — provider-neutral AI gateway, guarded copilots, success score, tenant provisioning, plans, domains and feature entitlements.
9. **Production hardening** — authorization matrix, tenancy penetration tests, queues/Horizon, Redis, backups, monitoring, rate limits, privacy/retention and deployment runbooks.

## Non-negotiable architecture rules

- Every institution-owned row has a `school_id`; access is denied unless tenant context matches. Platform admins must select an explicit tenant for tenant operations.
- Controllers delegate business transactions to services/actions. Payment and messaging providers are accessed through contracts and verified webhooks.
- Mobile endpoints are versioned under `/api/v1`; responses use stable resource envelopes and machine-readable error codes.
- Money uses decimal minor-safe values and an institution currency. Academic calculations store inputs and policy versions so reports remain reproducible.
- Every privileged mutation is auditable. Sensitive values, OTPs, tokens, provider secrets, and student health data are never logged.
- Placeholder success responses do not count as implementations.

## External production gates

Live Paystack, Firebase, BulkSMSLive, WhatsApp, email-domain, AI-provider and object-storage verification requires credentials and approved callback domains. Adapters and automated contract tests are delivered before credentialed smoke tests.

## Delivery status

All nine implementation stages are represented in the application, automated test suite, Flutter parent app, CI workflow, and production runbook. Credentialed provider smoke tests, infrastructure provisioning, backup restore drills, and app-store submission remain environment-specific release activities.
