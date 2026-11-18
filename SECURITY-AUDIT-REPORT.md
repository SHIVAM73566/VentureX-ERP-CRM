# VentureX ERP & CRM — Security & Release Audit Report (v1.3.1)

**Version:** 1.3.1  
**Report date:** 2026-11-18 (UTC)  
**Application:** VentureX ERP & CRM (Laravel 13, PHP 8.4)  
**Audited repository:** `C:\MY_ERP` (canonical)  
**Primary repository:** <https://github.com/SHIVAM73566/VentureX-ERP-CRM>  
**Deploy action repository:** <https://github.com/SHIVAM73566/venturex-deploy-action>

---

## Scope

This report summarizes the v1.3.1 hardening, performance, installer/import, and documentation work performed in this repository, including:

- **Phase 1** — Six-step `/install` (Clean Production vs Demo modes), company + buyer Super Admin setup, installer lockout, transactional demo-data wipe (Admin UI + AERO X manual confirmation flow), validated CSV/XLSX import with templates and opening balances guidance.
- **Phase 2** — Enterprise indexing/migrations, atomic numbering via `document_sequences` with locking, pessimistic locking patterns in critical flows, dashboard caching, API pagination clamps, queued heavy jobs behind `VENTUREX_QUEUE_HEAVY_TASKS` (default off), `enterprise:load-test` command.
- **Phase 3** — Tenant fail-closed behavior, invitation-bound registration OTP flow, Sanctum ability enforcement middleware, API AI authorization, RAG company/role ACL and untrusted-snippet framing, tighter upload rules (no SVG logos; private raster validation for support screenshots where applicable), CSP without `unsafe-eval`, security headers on API paths, removal of AI key tail/substring leakage from logs/flash/UI copy.
- **Phase 4** — Buyer-facing docs, `.htaccess` / routing notes, deploy-action metadata alignment, WordPress connector text/path fixes, git ignore/export hygiene.

**Out of scope / caveats:** No third-party penetration test or external code audit was performed as part of this report. “Zero vulnerabilities” is **not** claimed; this report records **confirmed findings addressed in executed scope** and residual operational notes. Git history prior to rotations may still contain historical secrets in old commits (see **Residual risk**).

---

## Environment (audit context)

| Item | Value |
|---|---|
| Local preview (developer machine) | `http://localhost:8000/login`, `http://127.0.0.1:8000/login` |
| Production / deployment | Must use a proper web server document root pointing to `public/`, HTTPS at the edge, and real secrets in `.env` |

**Demo credentials (LOCAL DEMO ONLY):** When Demo Mode / demo seeders are used on a throwaway local install only:  
`demo_admin@example.com` / `Demo_Admin_2026!`, `demo_manager@example.com` / `Demo_Manager_2026!`, `demo_sales@example.com` / `Demo_Sales_2026!` — remove or change before any non-local exposure.

---

## Verification performed (this audit cycle)

All commands executed from repository root (`C:\MY_ERP`) unless noted.

| Check | Result |
|---|---|
| `php artisan test --colors=never` | **Passed**: **556 passed**, **1990 assertions**, **0 failed** (duration reported by test runner in that run: ~890.83s) |
| `npm run build` (Vite) | **Passed** (production build completed) |
| `php artisan route:list` | **Succeeded** (route table generated; see application logs/output for full listing) |
| Enterprise load simulation | `php artisan enterprise:load-test --users=30|100|300|500` — **0 errors** in each run (see **Enterprise load-test results** below) |

> Note: A full `php artisan test` run is the primary automated regression gate recorded here. Additional manual browser testing is recommended for payment gateways, real mail/SMS providers, and production web server rewrites.

---

## Enterprise load-test results (30–500 simulated staff)

Command: `php artisan enterprise:load-test --users=<N>` (deterministic query/path simulation across Sales, CRM, Procurement, Inventory, Logistics, Finance, HR batches as implemented in the command).

| Simulated staff | Batches | Queries (reported) | Time (ms, reported) | Errors |
|---:|---:|---:|---:|---:|
| 30 | 2 | 83 | 189.38 | 0 |
| 100 | 4 | 273 | 271.01 | 0 |
| 300 | 12 | 815 | 473.26 | 0 |
| 500 | 20 | 1359 | 651.93 | 0 |

**Optimizations / mitigations in place (high level):** grouped stock balance reads to reduce N+1 patterns, enterprise-focused indexes and `document_sequences` locking for numbering, pessimistic locks in critical financial/PO/stock paths where implemented, dashboard aggregate caching, API `per_page` clamping, and optional queued heavy work via `VENTUREX_QUEUE_HEAVY_TASKS` (default `false` to preserve synchronous test/local behavior). Continue to use MySQL/PostgreSQL in production (not SQLite) with appropriate connection pooling and queue workers when enabling heavy queues.

---

## Security hardening summary (v1.3.1)

Representative areas addressed (non-exhaustive):

- **Installer:** routes protected by web middleware stack with lockout (`InstallerGuard` + install state), throttling on installer routes, hardened env writing (no control/newline injection patterns as validated by tests), clear failure messaging patterns for missing extensions/DB privileges.
- **Authentication & API:** Sanctum ability middleware (`EnsureTokenAbility`) applied to API groups; AI API methods authorized via policies/gates; session/regression coverage retained by existing test suite.
- **Tenant isolation:** company scoping fail-closed behavior reinforced in shared concerns where applicable; registration/invitation OTP flow hardened (no auto-login before verification in the corrected flow).
- **RAG:** company/role-aware retrieval filtering, untrusted snippet framing in prompts, removal of request-path pipeline writes in favor of controlled sync paths.
- **Uploads:** SVG removed from logo allowlist; support attachments stored via private disk/raster validation patterns where implemented; importer validation uses security service checks.
- **Secrets & keys:** AI provider keys encrypted at rest; **no key substrings/tails** persisted in audit payloads or flashed to users in the updated flows; UI copy updated to reflect non-display of key material.
- **Headers:** security headers middleware behavior tightened (including API coverage as implemented); CSP adjusted away from `unsafe-eval` while preserving app functionality per test/build verification.
- **Mass assignment:** sensitive fields protected on key models per Phase 3 changes (validated by `SecurityHardeningTest`).

---

## Buyer operations: wipe demo data and import your own data

### A) Wipe demo data (Admin UI)

1. Sign in as the **buyer Super Admin** (created during `/install` in Clean Production Mode, or your promoted admin account).
2. Navigate to **Admin → Demo Data** (route: `admin.demo-data.index`, gated by super admin + step-up + throttle).
3. Enter your **current admin password** and type the exact confirmation phrase: **`WIPE DEMO DATA`**.
4. Submit. The operation runs in a **database transaction** and removes seeded/demo records while preserving system settings, roles/permissions, chart of accounts structure, and the active buyer Super Admin account (per `DemoDataWipeService` behavior).

### B) Wipe demo data via AERO X (manual action)

1. Use the AERO X / AI action entrypoint that exposes **`wipe_demo_data`** (manual confirmation required).
2. Provide **current admin password** and the exact phrase **`WIPE DEMO DATA`** when prompted; the action only proceeds after confirmation payload/flow succeeds.
3. The same transactional wipe service is used as the Admin UI path.

### C) Import your own Excel/CSV data

1. Go to **Admin → Imports** (`admin.imports.*`).
2. Download the provided **sample templates** (Customers, Suppliers, Leads, Products/Inventory, Opening Balances) from the import UI/templates area.
3. Prepare files as **CSV or XLSX** (JSON/TXT are not accepted by the current importer). Keep files within size limits enforced by the application.
4. Upload, map columns, validate, then execute. Opening balances and inventory-related imports follow the rules implemented in `app/Services/Import/*` (balanced journals / stock movements as designed).
5. Review any error reports produced by the import workflow and correct source files before re-importing.

---

## Release artifacts (v1.3.1) — to be finalized at publish time

The following should be produced from the final tagged commit and recorded in `SHA256SUMS.txt` (standard `sha256sum` format):

- 9 application channel ZIPs (same channel set as prior releases), plus  
- `VentureX-ERP-CRM-v1.3.1-full-source.zip`

**Do not** publish hashes from intermediate builds. After rebuilding from the release commit, regenerate checksums and update GitHub Release assets and any mirrored copies under `C:\Users\chatu\OneDrive\Documents\Default Project` as applicable.

---

## Residual risk and recommendations

- **Git history:** If `.env` or secrets ever appeared in historical commits, rotate credentials and consider history rewrite + force-push coordination (high impact). This report does not assert history is clean beyond current working-tree practices.
- **HSTS:** Not enforced on plain HTTP local previews; enforce HSTS and TLS at the production reverse proxy/load balancer.
- **Dependencies:** Run `composer audit` / `npm audit` periodically and apply updates in a tested branch.
- **Backups:** Take database and `storage/` backups before running wipe or bulk imports in production.
- **Marketplace/trending:** GitHub Marketplace publication and search ranking depend on GitHub review and platform signals; they cannot be guaranteed by repository changes alone.

---

## Document history

- **v1.3.1 (this file):** Updated for Phase 1–4 work, full automated test pass recorded above, load-test metrics recorded above, demo/LAN guidance corrected.
- Prior v1.3.0 report content is superseded for v1.3.1 release communication; retain Git history for audit trail.

---

*Prepared as an internal engineering audit artifact for the VentureX ERP & CRM v1.3.1 release process.*