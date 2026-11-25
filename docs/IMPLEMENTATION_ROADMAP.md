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

Status: **NOT STARTED**

Known baseline findings:

- Pint reports pre-existing formatting debt in 16 files.
- `composer` executable is not available on PATH in this environment.
- No static type checker configured.
- No CI workflows exist.

Planned actions:

1. Triage Pint findings; do not auto-format broadly without approval.
2. Decide whether style fixes are necessary or should remain documented debt.
3. Add CI workflow only after test baseline is re-confirmed.
4. Avoid any change that alters rendered output.

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

Status: **PARTIALLY IMPLEMENTED**

Already present:

- `.env` ignore rules.
- Git secret scan clean.
- Secret redaction in self-healing capture.
- Fail-closed Stripe configuration.

Planned actions:

1. Save webhook signing secret locally.
2. Add `docs/SECURITY_OPERATIONS.md`.
3. Review CSP unsafe-eval default.
4. Document secret rotation and backup restore procedures.
5. Add CI secret scanning.

## Stage E — Stripe billing and entitlements

Status: **PARTIALLY IMPLEMENTED**

Already committed before this prompt:

- `e8a0c23` webhook integrity and URL validation hardening.
- `b92e5b0` webhook setup helpers and event coverage tests.

Planned actions:

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
