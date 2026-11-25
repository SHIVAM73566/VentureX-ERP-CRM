# VentureX ERP & CRM — Known Issues

Status: **Phase 0 baseline**. These issues were present at the current HEAD (`b92e5b0`) before any new implementation under the master prompt. They are not claimed to have been caused by Phase 0 documentation work.

## 1. Pint style debt — RESOLVED

Command:

```cmd
php vendor/bin/pint --test
```

Baseline result: **FAIL** (exit 1). 16 files were flagged:

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

Impact: style-only. Some flagged files were touched in the recent billing hardening commits, so this debt was current at HEAD but not necessarily older than that release.

Resolution: the 16 flagged files were formatted with `php vendor/bin/pint <files>`. Re-check result:

```text
PINT_TEST_EXIT=0
{"tool":"pint","result":"passed"}
```

No UI, Blade, CSS, JavaScript, or view files were changed by this fix.

## 2. CI coverage — RESOLVED

Correction to the baseline note: `.github/workflows/` is a hidden directory. It does contain tracked workflows: `ci.yml`, `security.yml`, and `pages.yml`. An initial replacement of `ci.yml` was reverted; the final change is a minimal patch to the existing files.

Resolution applied:

- `ci.yml`: added `layers-v1` and `phase0-baseline-audit` to push/PR triggers, added least-privilege `permissions: contents: read`, and added an explicit PHP syntax-check step.
- `security.yml`: added the same branches and made the tracked-file secret scan blocking with Stripe-specific patterns added.

Local validation:

```text
WORKFLOWS_YAML_OK
PINT_TEST_EXIT=0
NPM_CI_EXIT=0 (0 vulnerabilities)
BUILD_EXIT=0
Secret scan local exit=1 with empty output (no matches)
UI files changed=0
```

GitHub Actions execution itself is not claimed here; it must be observed on GitHub after push.

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

## 13. GitHub Actions failures — root causes identified and patched

Public API evidence showed 29 failed runs in the latest 100: 15 CI, 3 Deploy GitHub Pages, 11 legacy `pages build and deployment`.

Root causes:

- CI: Pint style check failure — fixed locally; Pint now exits 0.
- Legacy Pages checkout: gitlink `release/venturex-deploy-action` existed without `.gitmodules` — `.gitmodules` added.
- Deploy Pages: no `github-pages` artifact found and duplicate `pages` concurrency group — workflow group isolated to `venturex-pages-workflow`; build/upload and deploy split into separate jobs to remove the same-job artifact race.

Remote green status was observed for latest `main` commit `d36fc7b`: CI #65, Deploy GitHub Pages #46, Security Audit #32, and pages-build-deployment #48 all completed successfully. See `docs/GITHUB_ACTIONS_MARKETPLACE.md`.

## 14. GitHub Marketplace version drift — tag prepared, release publish blocked

The Marketplace listing loads with HTTP 200 and action metadata validates. The concrete issue is that the listing shows `v1.0.2` while the action repository documents `v1.3.0`. A `v1.3.0` tag was pushed, but Marketplace requires a published GitHub Release with the Marketplace checkbox and 2FA. `gh` is unauthenticated locally, so this final publish step requires the owner.

No secret or token was printed or requested.

## 15. Full Phase 0 test rerun pending

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
