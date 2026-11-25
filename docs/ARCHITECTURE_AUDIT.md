# VentureX ERP & CRM — Architecture Audit

Status: **Phase 0 baseline**. This document records the repository as inspected on 2026-11-25. No application UI, layout, design, or workflow was changed during this audit.

## Repository facts

| Item | Observed value |
|---|---|
| Git root | `C:/MY_ERP` |
| Branch | `layers-v1` |
| HEAD at audit start | `b92e5b0 feat(billing): add Stripe webhook setup helpers and event coverage` |
| Git status at audit start | Clean |
| Framework | Laravel 13 / PHP 8.4 |
| PHP runtime | `PHP 8.4.24 (cli)` |
| Frontend | Blade + Alpine.js (`@alpinejs/csp`) + Tailwind CSS 4 + Vite 8 |
| Node runtime | `v26.7.0` |
| npm runtime | `10.9.9` |
| Test framework | PHPUnit 12 via `php artisan test` |
| Test files | 39 |
| PHP source files checked | 468 (app, tests, config, database) |
| Authentication | Laravel session auth + Sanctum config present |
| Permissions | Spatie Laravel Permission |
| Tenancy | Company-scoped data with fail-closed shared services (per existing `ARCHITECTURE.md`) |
| Local DB in tests | SQLite in-memory (`phpunit.xml`) |
| Configured production DB drivers | MySQL, PostgreSQL, SQL Server, SQLite in `config/database.php`; MySQL in Docker |
| Containers | `docker/Dockerfile`, `docker/docker-compose.yml`, `docker/docker-compose.prod.yml`, nginx config |
| CI workflows | `.github/workflows/` contains no workflow files |

## Directory structure

Top-level application areas observed:

```text
app/                 342 files
app/Http/Controllers 104 files
app/Services          72 files
app/Models            91 files
config                22 files
database/migrations   52 files
routes                 4 files
resources/views      190 files
tests                 40 files (39 *Test.php + support files)
```

Additional delivery/documentation areas: `docs/`, `documentation/`, `release/`, `screenshots/`, `videos/`, `integrations/`, `license-server/`.

## Request flow

Observed from `ARCHITECTURE.md` and the route/controller structure:

1. Browser request enters through `routes/web.php`.
2. Controllers in `app/Http/Controllers` handle the request.
3. Authorization is enforced through policies/gates and `app/Support` helpers.
4. Business logic lives in `app/Services`.
5. Eloquent models in `app/Models` access company-scoped data.
6. Views in `resources/views` render Blade + Alpine + Tailwind.
7. Built assets are served from `public/build`.

## Core modules already present

Per repository documentation and test files:

- CRM: customers, leads, activities, follow-ups.
- Sales: quotations, sales orders, invoices, payments.
- Procurement: suppliers, purchase orders, RFQs/offers.
- Inventory: products, stock movements, warehouses/locations.
- Logistics: shipments, landed costs.
- Finance: chart of accounts, journals, payables/receivables.
- HR / Support / Admin: users, roles, audit logs, imports/exports.
- AI/RAG workspace: 84 company-scoped pipelines registered in `app/Services/Rag/RagPipelineRegistry.php`.
- Billing: Stripe checkout, webhook verification, entitlements, Billing Portal.
- Security: login throttling config, MFA config, export limits, upload limits, CSP config, secret redaction, self-healing error capture.

## Stripe architecture observed

Configured in `config/services.php`:

- Server-side `STRIPE_SECRET` and `STRIPE_WEBHOOK_SECRET`.
- Explicit API base allowlist: `https://api.stripe.com`.
- Redirect host allowlist: `checkout.stripe.com`, `billing.stripe.com`.
- Webhook timestamp tolerance: default 300 seconds.
- Stale processing claim lease: default 300 seconds.
- Entitlement grace period: default 7 days.
- 18 layer-to-Price-ID mappings, all sourced from environment variables.
- Success, cancel, and Billing Portal return URLs sourced from environment variables.

Webhook endpoint observed:

```text
POST /billing/stripe/webhook
```

Controller: `Billing\StripeWebhookController@handle`.

Handled events observed in source:

```text
checkout.session.completed
checkout.session.async_payment_succeeded
invoice.paid
invoice.payment_failed
customer.subscription.updated
customer.subscription.deleted
```

Signature verification lives in `app/Services/Billing/StripeSignatureVerifier.php` using raw payload, HMAC-SHA256, timestamp tolerance, and `hash_equals`.

## Environment and secret posture

- `.env` is ignored by Git.
- `.env.bak-*` backups are ignored by Git.
- `.env` is not tracked.
- Git secret scan returned 0 hits for live/test/restricted Stripe keys, webhook secrets, account IDs, and real Price IDs.
- At audit time: `STRIPE_SECRET=configured`, `STRIPE_WEBHOOK_SECRET=missing`, `STRIPE_PRICE_*=18/18 configured`.
- Only status labels are reported; no secret values were printed.

## Baseline verification

| Check | Result | Evidence |
|---|---|---|
| Git status | PASS | Clean at audit start; no staged secret files |
| PHP syntax | PASS | 468 PHP files checked, 0 syntax failures |
| Linter (`php vendor/bin/pint --test`) | FAIL — pre-existing style debt | 16 files flagged; see `docs/KNOWN_ISSUES.md` |
| Static type checker | NOT CONFIGURED | No PHPStan/Psalm in `composer.json` |
| Production build (`npm run build`) | PASS | Exit 0; built in ~5s; no tracked asset changes |
| Full tests (`php artisan test`) | PASS | 597 passed / 2276 assertions / 0 failed / 0 errors; Phase 0 rerun exit 0 in 519.62s |
| Composer validate | NOT RUN | `composer` executable is not on PATH in this environment |
| CI workflow execution | NOT AVAILABLE | No `.github/workflows` files exist |
| Secret scan | PASS | 0 secret-shaped hits in tracked Git content |
| Load/performance test | NOT RUN | No load-test harness configured |

## Key observations

1. The application is a mature Laravel monolith, not a greenfield project. Preservation rules are appropriate.
2. Billing hardening is already committed (`e8a0c23`, `b92e5b0`).
3. CI is absent despite `.github/` existing; this is the largest DevSecOps gap.
4. Pint fails on pre-existing formatting debt. Auto-formatting all files would create broad diff noise and is intentionally deferred.
5. No static type checker is configured; PHP syntax checking is the current baseline substitute.
6. Stripe CLI was observed in live mode. Local webhook practice requires sandbox mode or explicit `--live`.
7. The app key is live mode while the local helper recommends sandbox mode. This mismatch is documented and must be resolved before real checkout end-to-end testing.

## Change policy for Phase 0

No UI files were modified. Documentation files and `AGENTS.md` were added only to establish preservation and baseline rules.
