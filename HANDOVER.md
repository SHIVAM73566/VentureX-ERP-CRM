# VentureX ERP & CRM — Layers (recurring revenue) Handover

> Branch: `layers-v1` (work in progress). Do **not** commit secrets. Never put Stripe keys/webhook secrets into the app repo or any distributable zip.

## PHASE 0 audit (summary, read-only)
- **Stack**: Laravel 13.x (^13.0), PHP ^8.4, Sanctum, Spatie Permission, Dompdf, Vite/Tailwind/Alpine (see `composer.json`, `package.json`).
- **No existing billing/licensing**: No `laravel/cashier`, no Stripe SDK in Composer; no `config/layers.php`, no Stripe/webhook/license routes/controllers/models/migrations found in repo search. `config/services.php` has postmark/resend/ses/slack only. `.env.example` has no `STRIPE_*` / license server vars.
- **Auth/roles**: `App\Models\User` (Sanctum + Spatie roles, company scoping via `CompanyContext`). Roles seeded in `database/seeders/RoleSeeder.php` (super_admin, company_admin, CEO/CFO/COO, sales/purchase/logistics/warehouse/finance/HR roles, etc.). MFA/step-up patterns in routes/middleware (`two_factor`, `step_up`).
- **Settings (admin)**: `Admin\SettingController` + views under `resources/views/admin/settings` (tabs: AI, Company, Branding & Invoices). AI provider keys via `AiKeyController` (step-up + throttles). `SettingService`/`BrandingService` used.
- **Queues/scheduler**: `QUEUE_CONNECTION=database` default; `routes/console.php` schedules `SelfTrainingPipeline` (daily 02:00) and `agents:run` (every 15m).
- **Domains/modules (high level)**: CRM, Sales, Inventory, Procurement, Logistics, Finance, HR, Support, AI/RAG (84 pipelines referenced in docs), Admin, API (`routes/api.php`).
- **Release zips (in `release/`)**: `VentureX-ERP-CRM-Acquire-Release-v2.6.zip`, `VentureX-ERP-CRM-Codester-v2.6.zip`, `VentureX-ERP-CRM-Payhip-v2.6.zip`, plus `venturex-erp-crm-production-v2.6-final.zip` and related docs/folders. No `release.sh` in repo. No `license-server/` directory in repo.

## PHASE 1 security commitments (non-negotiable) — must hold for all later phases
1. **No Stripe secret keys and no Stripe webhook signing secret** in the distributed application, in any committed file, and in **any zip** produced for buyers. Secrets live **only** in the **license-server** (separate Laravel app) `.env` (and secure host secret storage). Client apps must never bundle Stripe secrets.
2. **All Stripe webhooks** (on license server): signature verification required; handlers **idempotent** (store event IDs / processed markers before side effects as appropriate).
3. **Stripe mode**: implement and test in **test mode first**; document switch to live mode as an operator checklist item (keys only in license-server env).
4. **Subscription lapse behavior**: **never lock or delete customer data** when a subscription lapses. Only **disable premium/layer-gated features** per entitlement rules; **exports must remain available** (or explicitly documented exception — default: keep export available). Confirm UX copy matches this.
5. **PII/logging**: do not log full card data, raw webhook payloads containing secrets unnecessarily, or license private keys. Audit logs should record business events without leaking secrets.

## Open questions / unknowns (you must decide)
- **License server**: new separate Laravel app location/repo strategy (not present). Define domains, DB, deployment, and who holds Stripe account.
- **Entitlement source of truth**: license server issues **ed25519-signed license tokens** (Phase 3) consumed by client with embedded/public key verification + 14-day grace (offline). Client must store verification public key securely (config/env/artifact strategy TBC).
- **“Stripe payout structure for users in every ZIP file”** (from user): needs precise definition. **Risk**: putting payout account details or secrets into buyer zips is dangerous and forbidden by Phase 1. Clarify whether this means (a) documentation/marketing copy only, (b) checkout URLs configured per deployment, or (c) something else. **Do not embed secrets.**
- **Which ZIPs get “10–25 layers of payouts” content?** User said “add 10–25 different layers of payouts in THIS - VentureX-ERP-CRM-Codester-v2.6 / VentureX-ERP-CRM-Payhip-v2.6…” and “do not add 10–25 layers in every zip file”. This conflicts with “add Stripe payout structure in EVERY ZIP FILE”. Requires written confirmation before changing any files under `release/*.zip` (treat zips as artifacts; changing them may be destructive). Prefer generating via `release.sh` (Phase 9) rather than manual zip surgery.
- **Tax/merchant of record**: Phase 3 requires a note (Stripe Tax vs MoR) — decision pending.
- **Plan naming/prices**: PRICING.md is draft/TODO; Phase 6 references Core $499 one-time, Care monthly, Pro bundle — align with business decision before public assets.
- **Keys/decisions still needed (checklist seed)**: Stripe account + test/live keys (license server only), webhook endpoint URL(s), ed25519 keypair for license signing, public key distribution method to clients, price IDs / lookup keys for all layers, email/SaaS provider for waitlists and onboarding emails, legal review for terms.

## What was done in this branch so far
- Created branch `layers-v1` from `sale-ready`.
- Added this `HANDOVER.md` (Phase 0 + Phase 1 notes).

## Next steps (pending your “go” per phase)
- PHASE 1: enforce rules in code review/process; add guards where applicable (no secrets committed). 
- PHASE 2: implement entitlement engine in **this Laravel app (client)** first? (config/layers.php, models, middleware, Blade directive, locked-preview UI, feature tests) — confirm scope matches “client app” vs “license server”.
