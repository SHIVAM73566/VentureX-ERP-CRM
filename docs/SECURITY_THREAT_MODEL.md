# VentureX ERP & CRM — Security Threat Model

Status: **Phase 0 baseline**. This is a threat model based on observed repository evidence, not a claim of absolute security.

## Assets to protect

- Customer, CRM, sales, finance, HR, and ERP records.
- Tenant identity and company-scoped data boundaries.
- Stripe secret key, webhook signing secret, and Price ID configuration.
- Database credentials, API tokens, OAuth secrets, and AI provider keys.
- Audit trails and administrative actions.
- Session cookies and privileged user accounts.
- Uploaded files and exported data.

## Trust boundaries

1. Browser / unauthenticated internet → Laravel web routes.
2. Authenticated tenant user → company-scoped application data.
3. Stripe → `POST /billing/stripe/webhook`.
4. Laravel server → Stripe API and Stripe-hosted checkout/portal.
5. Application → email, AI providers, file storage, integrations.
6. CI/deployment environment → production runtime.

## Seven-layer control assessment

| Layer | Observed controls | Threats addressed | Remaining gaps |
|---|---|---|---|
| 1. Attack/bot defense | Configured login attempt limits, lockout thresholds, AI rate limits, export limits, CSRF exemption only for signed webhook, HMAC-SHA256 signature verification, timestamp tolerance, constant-time comparison | Credential stuffing, replayed webhooks, forged webhooks, CSRF on normal POST routes, basic API abuse | No observed honeypot system; no known WAF; no measured bot-detection efficacy |
| 2. Secrets/environment | `.env` and backups ignored; no secret values in tracked Git; server-only config; redaction in self-healing; fail-closed blank Stripe secret | Secret leakage in Git, logs, Blade, JavaScript | `STRIPE_WEBHOOK_SECRET` missing at audit time; live key/test CLI mismatch; no external vault integration observed |
| 3. Encryption/data protection | Session encryption configurable; HTTPS force middleware observed in bootstrap; Laravel encryption available; outbound TLS defaults | Session tampering, plaintext transport risks | `VENTUREX_CSP_ALLOW_UNSAFE_EVAL` defaults true; no field-level AES-256-GCM envelope observed; no true client-side E2EE claimed |
| 4. Access control/tenant isolation | Spatie permissions, super_admin/setup protections, company-scoped services, cross-tenant tests, fail-closed behavior | Privilege escalation, IDOR/BOLA, cross-tenant reads/writes | No database Row-Level Security observed; tenant isolation depends on application-layer scoping |
| 5. Runtime protection | CSRF protection, CSP config, secure cookie flags configurable, HttpOnly default true, SameSite lax default | XSS, CSRF, cookie theft | CSP unsafe-eval default needs production review; external script SRI not confirmed |
| 6. Database/infrastructure | Docker compose files, nginx config, migrations, upload validation service, file size limits | SQL injection via Eloquent parameterization, unsafe uploads, dependency drift | Least-privilege DB account and backup restore test not verified; no CI workflow |
| 7. Monitoring/incident response | Self-healing error capture with redaction, audit log tests, failed webhook tests, billing event records | Secret exposure in errors, silent webhook failure, missing audit trail | No observed centralized alerting or structured security dashboard; no incident runbook yet |

## Stripe-specific threats and current defenses

| Threat | Defense observed |
|---|---|
| Unverified browser success unlocks paid features | Entitlements activate only through verified server-side webhook events |
| Forged webhook payload | Raw-body HMAC-SHA256 verification with `hash_equals` |
| Replayed old webhook | Timestamp tolerance and stale-event protection |
| Duplicate Stripe delivery | Database-backed event ID idempotency |
| Stuck `processing` claim | Lease-based retry instead of permanent drop |
| Conflicting tenant metadata | Metadata conflict rejected; tenant cannot be moved by webhook |
| Empty customer ID wiping stored customer | Ignored; stored customer retained |
| Unsafe API base or redirect host | Allowlist enforced in `StripeUrlGuard` |
| Cancellation destroying ERP data | Cancellation only updates entitlement status; no delete/truncate calls in billing webhook/activation path |
| Unpaid/incomplete checkout unlocking access | Unpaid/incomplete activation blocked |

## Threats still requiring action

1. Save the webhook signing secret locally before production webhook processing.
2. Align app Stripe mode with CLI/listener mode before end-to-end checkout testing.
3. Add CI workflows for tests, linting, secret scanning, and production build.
4. Review `VENTUREX_CSP_ALLOW_UNSAFE_EVAL` for production.
5. Add an external secret-manager path for production secrets.
6. Add or document database backup/restore verification.
7. Add load tests before making scalability claims.
8. Complete formal incident-response runbook (`docs/SECURITY_OPERATIONS.md`, planned).
9. Do not represent SOC 2, HIPAA, or SOX compliance as certified without independent assessment.
10. Do not describe the system as unhackable or absolutely secure.

## Verification boundary

Verified in this baseline:

- No secret-shaped values in tracked Git.
- 468 PHP files pass syntax check.
- 597 tests / 2276 assertions passed in the prior verified full run.
- Production frontend build exits 0.
- Stripe billing security tests include invalid signature, stale signature, idempotency, cancellation data preservation, unsafe URL rejection, and cross-tenant conflict cases.

Not verified in this baseline:

- Runtime behavior against a live production domain.
- CI workflow execution (none exists).
- Load-test throughput.
- Backup restoration.
- Independent compliance certification.
