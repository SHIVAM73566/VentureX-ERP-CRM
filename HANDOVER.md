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

## Decisions recorded (user-confirmed, 2026-11-21)
- **ZIP billing docs (in-repo sources, no secrets):** `release/BILLING-STRIPE-NOTES.md` explains Stripe/checkout/payouts at a high level with **no API keys, no webhook secrets, no credentials**. This file is intended to be included in **ALL** buyer release ZIPs (`release.sh` / packaging step must add it to every ZIP).
- **15-layer model doc (source only in repo):** `release/LAYERS-PRICING-15.md` documents the full 15-layer model (honest statuses/pricing notes). Intended for **Codester and Payhip** ZIP packages **only** (not Acquire, not production-final, etc.). Do **not** add “10–25 different payout/billing systems”; the model is the **15 layers** defined in this doc/config plan.
- **Packaging approach:** implement in-repo first; **`release.sh` (Phase 9)** should generate clean ZIPs and place the above docs correctly. Do **not** hand-edit existing binary ZIPs in `release/` unless explicitly approved later.
- **License server remains separate**; Stripe secrets only there.

## Open questions / unknowns
- **License server**: new separate Laravel app location/repo strategy (not present). Define domains, DB, deployment, and who holds Stripe account.
- **Entitlement source of truth**: license server issues **ed25519-signed license tokens** (Phase 3) consumed by client with embedded/public key verification + 14-day grace (offline). Client must store verification public key securely (config/env/artifact strategy TBC).
- **Tax/merchant of record**: Phase 3 requires a note (Stripe Tax vs MoR) — decision pending.
- **Plan naming/prices**: PRICING.md is draft/TODO; Phase 6 references Core $499 one-time, Care monthly, Pro bundle — align with business decision before public assets.
- **Keys/decisions still needed (checklist seed)**: Stripe account + test/live keys (license server only), webhook endpoint URL(s), ed25519 keypair for license signing, public key distribution method to clients, price IDs / lookup keys for all layers, email/SaaS provider for waitlists and onboarding emails, legal review for terms.

## What was done in this branch so far
- Created branch `layers-v1` from `sale-ready`.
- PHASE 0/1: `HANDOVER.md`, `release/BILLING-STRIPE-NOTES.md`, `release/LAYERS-PRICING-15.md` (sources for packaging per decisions above).
- PHASE 2 (client): layers config, entitlement model/migration, service, middleware+directive, locked UI component, tests (LayerEntitlementTest passing).
- PHASE 3 (scaffold): `license-server/` directory with README, env example, routes, stub controllers/services (Cashier/webhook/idempotency notes, ed25519 token flow, verify endpoint stub, Stripe CLI steps, Tax/MoR note).
- PHASE 4 (partial): `config/license.php`, `license_tokens` table/model, `ClientLicenseService` (apply verified Ed25519 token `payload.sig` using `LICENSE_ED25519_PUBLIC_KEY`, optional sync from `LICENSE_SERVER_URL`), `EntitlementService` also checks cached verified token (with grace), Settings → Plan & Layers tab + apply-token/sync routes (step-up) + UI, `PlanLayersSettingsTest` passing. **BYOK/local fallback:** AI provider keys remain user-managed (BYOK); `ai_credits` layer is an optional metered add-on hook (no enforcement change yet) — do not store Stripe/payment secrets in client or ZIPs. Remaining PHASE 4 items (usage meters UI, contextual upgrade prompts, trial/cancel/renewal/invoices pages) are **not fully implemented** yet (documented placeholders/notes only).
- PHASE 5 (L1–L3 UX): Plan & Layers UI shows L1 updates/support active note, L2 managed-hosting service note, L3 BYOK+optional metered note; billing/trial/cancel/renewal/invoices described as managed via publisher/license server flow in UI copy. No secrets added.

## Next steps (pending your “go” per phase)
- PHASE 1: enforce rules in code review/process; add guards where applicable (no secrets committed). 
- PHASE 2: implement entitlement engine in **this Laravel app (client)** first? (config/layers.php, models, middleware, Blade directive, locked-preview UI, feature tests) — confirm scope matches “client app” vs “license server”.
