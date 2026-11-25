# VentureX ERP & CRM — Implementation Roadmap

Status: **Phase 0 complete; Phase 1 onward not yet executed under this master prompt.**

Governing rule: preserve existing UI, layout, design, navigation, and working functionality. Backend and internal logic changes first. Frontend changes only when technically necessary and explicitly approved if visually disruptive.

## Stage A — Repository discovery and baseline

Status: **COMPLETE**

Delivered:

- `AGENTS.md` permanent project rules.
- `docs/ARCHITECTURE_AUDIT.md`.
- `docs/SECURITY_THREAT_MODEL.md`.
- `docs/IMPLEMENTATION_ROADMAP.md`.
- `docs/KNOWN_ISSUES.md`.
- Git status checked: clean at start.
- Runtime versions recorded.
- Build, syntax, lint, test, and secret posture baselined.

No UI, layout, design, or workflow was changed.

## Stage B — Critical bugs and broken builds

Status: **COMPLETE**

Findings and actions:

- No broken imports, syntax errors, route boot failures, or runtime config errors found.
- `php artisan config:cache`, `config:clear`, `view:cache`, and `view:clear` all exited 0.
- Pint style debt in 16 files was fixed surgically; Pint now passes with 0 findings.
- Existing CI workflows were preserved and minimally patched instead of replaced.
- `composer` CLI remains unavailable locally; no composer changes were attempted.
- No static type checker is configured; no type-check claim is made.
- No rendered output was altered.

## Stage C — Authentication, authorization, tenant isolation

Status: **NOT STARTED**

Existing evidence:

- Spatie permission tests present.
- Cross-tenant tests present in `StripeBillingSecurityTest.php`.
- Company-scoped services documented in `ARCHITECTURE.md`.

Planned actions:

1. Review representative tenant-owned controllers and services.
2. Add targeted cross-tenant regression tests only where gaps are proven.
3. Preserve login screens and account workflows visually.

## Stage D — Secrets, encryption, security monitoring

Status: **PARTIALLY IMPLEMENTED — CI secret scan strengthened**

Already present:

- `.env` ignore rules.
- Git secret scan clean.
- Secret redaction in self-healing capture.
- Fail-closed Stripe configuration.
- Least-privilege workflow permission and blocking Stripe-aware secret scan added to CI/security workflows.

Remaining actions:

1. Save webhook signing secret locally.
2. Add `docs/SECURITY_OPERATIONS.md`.
3. Review CSP unsafe-eval default.
4. Document secret rotation and backup restore procedures.
5. Add CI secret scanning.

## Stage E — Stripe billing and entitlements

Status: **PARTIALLY IMPLEMENTED — backend checks VERIFIED**

Already committed before this prompt:

- `e8a0c23` webhook integrity and URL validation hardening.
- `b92e5b0` webhook setup helpers and event coverage tests.

Verified in this stage:

- 6/6 required Stripe events present in the webhook controller.
- HMAC signature verification, timestamp tolerance, and raw payload usage present.
- Database idempotency and stale-event protection present.
- Billing Portal return URL used in portal service.
- No delete/truncate/forceDelete calls in billing webhook or activation service.
- Targeted billing/security test run: 118 passed / 356 assertions / 0 failed / 0 errors.

Remaining actions:

1. Resolve live-app vs sandbox-listener mode mismatch.
2. Confirm webhook secret configured.
3. Complete `docs/STRIPE_PLAN_AUDIT.md` and `docs/FEATURE_ENTITLEMENTS.md`.
4. Verify end-to-end checkout and Billing Portal without touching UI design.

## Stage F — Enterprise ERP/CRM modules

Status: **NOT STARTED UNDER THIS PROMPT**

Existing modules already present per repository docs: CRM, sales, procurement, inventory, logistics, finance, HR/support/admin, RAG workspace.

No new dashboards or pages may be introduced without explicit authorization.

Planned actions:

1. Audit current implementation status of command palette, industry configurator, cash-flow copilot, audit ledger, multi-entity, multi-currency, and workflow automation.
2. Report actual status before implementing anything.
3. Prefer extending existing screens and services.

## Stage G — UX/UI improvements

Status: **BLOCKED BY PRESERVATION RULE**

No visual changes authorized. Any unavoidable visual change requires: file, reason, possible visual impact, and user approval.

## Stage H — Performance and scalability

Status: **NOT STARTED**

Planned actions:

1. Add query/index review only after baseline.
2. Add load tests before any scalability claim.
3. Measure latency, throughput, and webhook delivery.

## Stage I — Complete verification and documentation

Status: **IN PROGRESS**

Completed documents:

- `docs/TEST_REPORT.md` — baseline full suite 597 passed / 2276 assertions / 0 failed / 0 errors.

Remaining documents:

- `docs/STRIPE_PLAN_AUDIT.md`
- `docs/FEATURE_ENTITLEMENTS.md`
- `docs/DATABASE_MIGRATIONS.md`
- `docs/SECURITY_OPERATIONS.md`
- `docs/DEPLOYMENT_GUIDE.md`

Final report must use VERIFIED / IMPLEMENTED / PARTIALLY IMPLEMENTED / BLOCKED / NOT IMPLEMENTED / FAILED statuses with actual command evidence.
