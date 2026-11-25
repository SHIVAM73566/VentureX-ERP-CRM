# VentureX ERP & CRM — Known Issues

Status: **Phase 0 baseline**. These issues were present at the current HEAD (`b92e5b0`) before any new implementation under the master prompt. They are not claimed to have been caused by Phase 0 documentation work.

## 1. Pint style debt

Command:

```cmd
php vendor/bin/pint --test
```

Result: **FAIL** (exit 1). 16 files were flagged:

```text
app/Http/Controllers/Admin/SettingController.php
app/Http/Controllers/DashboardController.php
app/Http/Middleware/EnsureLayerEntitled.php
app/Providers/AppServiceProvider.php
app/Services/Billing/StripeCheckoutService.php
app/Services/ClientLicenseService.php
app/Services/EntitlementService.php
app/Support/helpers.php
bootstrap/app.php
database/migrations/2026_11_25_000001_harden_billing_integrity.php
license-server/routes/api.php
license-server/routes/web.php
routes/web.php
tests/Feature/PlanLayersSettingsTest.php
tests/Feature/SmokeTest.php
tests/Feature/StripeBillingSecurityTest.php
```

Fixers reported include `fully_qualified_strict_types`, `unary_operator_spaces`, `braces_position`, `not_operator_with_successor_space`, `single_line_empty_body`, `ordered_imports`, `concat_space`, `blank_line_before_statement`, `class_definition`, and `no_unused_imports`.

Impact: style-only. Do not auto-format broadly without approval because it creates broad diff noise. Some flagged files were touched in the recent billing hardening commits, so this debt is current at HEAD but not necessarily older than that release.

## 2. No CI workflows

`.github/` exists but `.github/workflows/` contains no workflow files.

Impact: no automated lint/test/secret-scan/build evidence on push or pull request. CI execution cannot be claimed.

## 3. Composer CLI unavailable on PATH

Command `composer --version` failed with `CommandNotFoundException`.

Impact: `composer validate` and dependency audit were not run in this baseline. `composer.json` and lockfile are present; installed `vendor/` exists.

## 4. No static type checker configured

`composer.json` contains PHPUnit, Pint, Pail, Faker, Mockery, Collision, Laravel, Sanctum, Spatie Permission, and DomPDF. It does not include PHPStan or Psalm.

Impact: PHP syntax check (468 files, 0 failures) is the current baseline substitute. Do not claim zero type errors.

## 5. Stripe webhook secret missing

At audit time:

```text
STRIPE_WEBHOOK_SECRET=missing
```

Impact: webhook processing is disabled until the signing secret is saved locally. Save it only through `release/SET-WEBHOOK-SECRET.bat` or direct local `.env` edit. Never paste it into chat or commit it.

## 6. Stripe mode mismatch

- App `STRIPE_SECRET` is in live mode.
- Stripe CLI was observed in live mode.
- Local helper recommends sandbox/test mode.

Impact: sandbox listener events will not match a live-mode checkout created from the app. For practice, use test data only or switch the app to a test key first. For real events, an explicit live-mode listener is required and must not be treated as a practice test.

## 7. No production HTTPS domain finalized

Live webhook endpoint and Billing Portal return URL cannot be finalized without the production domain.

Expected production webhook:

```text
https://<live-domain>/billing/stripe/webhook
```

Expected portal return URL:

```text
https://<live-domain>/admin/settings?tab=plan
```

## 8. CSP unsafe-eval default

`config/security.php` references `VENTUREX_CSP_ALLOW_UNSAFE_EVAL` with default `true`.

Impact: production CSP should be reviewed. Do not claim strict CSP hardening until this is explicitly configured and verified.

## 9. No database Row-Level Security observed

Tenant isolation is application-layer and documented as company-scoped. No database RLS policy was observed.

Impact: continue automated cross-tenant tests; do not claim database-level tenant isolation.

## 10. Local demo credentials are documented

`PRICING.md` documents local reviewer credentials for `LOCAL DEMO ONLY`.

Impact: these must never be deployed to a public/production instance. Use Clean Production Mode during installation and remove/disable demo seeding for production.

## 11. No load/performance measurements

No load-test harness or measured throughput/latency results were found.

Impact: do not claim scalability. Add measured tests before making performance claims.

## 12. Compliance claims not independently validated

SOC 2, HIPAA, SOX readiness, and end-to-end encryption require policies, agreements, controls, and independent assessment. Code alone does not establish compliance.

Impact: report readiness controls only, never certification or absolute compliance.

## 13. Full Phase 0 test rerun pending

Verified full-suite result at current test code:

```text
597 passed / 2276 assertions / 0 failed / 0 errors
```

Fresh Phase 0 rerun completed successfully:

```text
Tests: 597 passed (2276 assertions)
Duration: 519.62s
TEST_EXIT=0
```

Recorded in `docs/TEST_REPORT.md`.
