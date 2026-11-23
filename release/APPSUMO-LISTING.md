# VentureX ERP & CRM — AppSumo Listing Draft (for #236667)

> **Product:** VentureX ERP & CRM (Laravel-based, self-hosted)
> **Target file:** `VentureX-ERP-CRM-Appsumo-Release-v2.6 - Copy.zip` (rebuilt release package with the latest security/billing fixes)
> **Status note:** This is a draft you can paste/edit in AppSumo. Verify final screenshots, pricing tiers, and feature toggles against your current build before publishing.

---

## Product name
**VentureX ERP & CRM — All-in-one business operations (self-hosted)**

## Tagline (short)
Run ERP, CRM, HR, projects, and AI-assisted workflows on your own server — one app, no per-seat SaaS lock-in.

## Category / tags (suggested)
- Business software / Productivity
- CRM, ERP, Project management, HR
- Self-hosted / On-premise (PHP/Laravel)

---

## One-sentence pitch
VentureX combines CRM, sales, accounting, inventory, HR, projects, support, and AI tools in a single self-hosted Laravel application so small teams can manage operations without juggling multiple subscriptions.

---

## #pricing — exact tiers (submission #236667)

| Tier | Price | Entitlement |
|---|---|---|
| **1 Code** | **$49** | **1 company**, **standard support**, **single license rights** |
| **2 Codes Stacked** | **$98** | **Full ERP/CRM multi-company**, **advanced reporting**, **role permissions**, **priority support**, **multiple license rights** |
| **Original / Direct comparison price** | **$499** | Reference comparison price for direct/full commercial licensing — **not** the AppSumo checkout price |

- **$49 per code. Max 2 stackable codes. 2 codes = $98.**
- The **$499** price is shown only as the Original/Direct comparison. AppSumo buyers pay **$49** or **$98**.
- No credit card is required in the app — activation is code-only (see below). Optional Stripe-funded add-ons are separate, clearly labelled publisher offerings and never part of AppSumo code redemption.

### Card-free redemption
1. Buy on AppSumo and open your order to get your code(s). Payment is handled by AppSumo; **no card is entered in the app**.
2. Install VentureX and complete setup (`/install`). Sign in as an administrator.
3. Open **Admin → Settings → Plan & Layers**, paste the first code, and submit. The **1 Code / $49** tier activates.
4. No card details are ever requested — only the AppSumo code.

### 2-code stacking ($98)
1. With the first code applied, stay in **Admin → Settings → Plan & Layers**.
2. Paste the **second code** in the same field and submit.
3. The plan upgrades to **2 Codes Stacked / $98** with full ERP/CRM multi-company, advanced reporting, role permissions, priority support, and multiple license rights.
4. **Maximum 2 codes.** Verify the active tier on the Plan & Layers screen.

### Reviewer login (local preview only)
- **URL:** `http://127.0.0.1:8000/login` (or `http://localhost:8000/login`)
- **Super Admin:** `demo_admin@example.com` / `Demo_Admin_2026!`
- **CEO:** `demo_manager@example.com` / `Demo_Manager_2026!`
- **Sales Manager:** `demo_sales@example.com` / `Demo_Sales_2026!`

Production admin credentials are created during installation and are **never** bundled in the ZIP.

---

## Key benefits (bullets)
- **Self-hosted control:** Install on your own server/VPS; your data stays under your control.
- **All-in-one workspace:** Leads, deals, invoices, purchases, inventory, HR, payroll basics, projects, tasks, assets, and support in one system.
- **AI-assisted productivity (optional):** Built-in AI/RAG and automation helpers; you can bring your own API keys (BYOK) — the app does not require vendor lock-in for AI.
- **Multi-company ready:** Designed for structured business operations with roles/permissions and audit-friendly workflows.
- **Branding & documents:** Company branding and invoice/export customization (per settings) to match your business.
- **Modern stack:** Laravel, Tailwind, Vite — familiar for developers and easy to extend.

---

## What’s included (highlight for buyers)
- Full source/package per the release ZIP (as provided): Laravel app, migrations, seeders/setup guidance, docs (installation, user/admin guides included in package where provided).
- Modules commonly used: Dashboard, CRM/Contacts/Leads, Sales/Invoices, Purchases, Inventory, HR/Employees, Projects/Tasks, Support, Reports (scope depends on your final tier/plan messaging).
- AI features: optional; BYOK supported. No AI API keys are bundled in the release.

---

## Ideal for
- Small to mid-size teams that want an on-prem ERP+CRM alternative
- Agencies/founders who prefer self-hosting and customization
- Operators who need CRM + finance + HR + projects in one place

---

## Requirements (important to list)
- Web server with PHP (version per `composer.json` / README in package)
- MySQL/MariaDB (or supported DB per Laravel config)
- Composer, Node/Vite build steps as documented in `INSTALLATION.md` / `README.md`
- HTTPS recommended for production

---

## Onboarding & support expectations (edit to match your offer)
- Follow `INSTALLATION.md` and `README.md` in the package.
- Configure `.env` from `.env.example` (never commit real secrets).
- For AppSumo buyers: state your support channel (email/URL) and response expectations here.

---

## Honest limitations (recommend including)
- Self-hosted: you are responsible for hosting, backups, updates, and security hardening.
- Some advanced/enterprise integrations may require custom work.
- AI features depend on your chosen providers/keys and your configuration.

---

## Media checklist (you prepare)
- Hero screenshot of dashboard
- 3–6 screenshots: CRM pipeline, invoice, HR or projects, AI/settings (redact data)
- Short GIF/video walkthrough (optional but recommended)

---

## AppSumo form checklist (Submission #236667)
Use this before submitting for review:

- [ ] Product name, tagline, and long description pasted and proofread
- [ ] Category/tags selected
- [ ] `#pricing` set to **$49 (1 Code)** / **$98 (2 Codes Stacked)** with **$499** as Original/Direct comparison only
- [ ] All screenshots uploaded (no real customer data, no secrets)
- [ ] Requirements listed (PHP/MySQL/hosting notes)
- [ ] Support URL/email set
- [ ] Refund/support policy text aligned with AppSumo terms
- [ ] Verify download/delivery points to the correct rebuilt `VentureX-ERP-CRM-Appsumo-Release-v2.6 - Copy.zip` (or your final delivery method)
- [ ] Smoke test: fresh install from `VentureX-ERP-CRM-Appsumo-Release-v2.6 - Copy.zip` on a clean environment (migrate, seed/login flow as documented)
- [ ] Confirm no `.env`, no API keys, no private keys inside the delivered ZIP

---

## Compliance notes (keep accurate)
- Release packaging must **not** include live secrets, `.env`, or `.git` history.
- Third-party licenses: ensure `LICENSE` and dependency licenses are included as shipped.

*Draft generated to help prepare AppSumo listing #236667. Customize before publishing.*