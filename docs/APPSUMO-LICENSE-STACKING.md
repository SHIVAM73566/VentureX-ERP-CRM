# AppSumo license & stacking (VentureX ERP & CRM)

This document explains the **exact AppSumo pricing, code redemption, and stacking** for VentureX ERP & CRM (self-hosted Laravel application). Use it for AppSumo submission **#236667** and for buyer-facing documentation.

## Exact pricing (submission #236667)

| Offer | Price | What you get |
|---|---|---|
| **1 Code** | **$49** | **1 company**, **standard support**, **single license rights** |
| **2 Codes Stacked** | **$98** | **Full ERP/CRM multi-company**, **advanced reporting**, **role permissions**, **priority support**, **multiple license rights** |
| **Original / Direct comparison price** | **$499** | Reference comparison price for direct/full commercial licensing — **not** the AppSumo checkout price |

- **$49 per code; 2 codes stack linearly to $98.**
- **Maximum stack: 2 codes.** Do not redeem more than 2 codes unless the live AppSumo page explicitly states otherwise.
- **$499** is shown only as the Original/Direct comparison price. AppSumo buyers pay **$49** or **$98**.

## What changes with 1 code vs 2 codes (stacked)

| Feature / limit | **1 Code — $49** | **2 Codes Stacked — $98** |
|---|---|---|
| **Companies** | **1 company** | **Full ERP/CRM multi-company** |
| **Advanced reporting** | Standard reporting | **Advanced reporting** |
| **Role permissions** | Standard roles | **Role permissions (full)** |
| **Support level** | **Standard support** | **Priority support** |
| **License rights** | **Single license rights** | **Multiple license rights** |

If the live AppSumo listing differs, the **AppSumo listing page is the source of truth for purchase terms**; keep this file in sync when you change the deal.

### Notes (important)

- "Full ERP/CRM multi-company" describes the entitlement granted by the stacked deal; it is not a promise of unlimited tenants unless explicitly written.
- Advanced reporting and role permissions depend on features enabled in your installed version.
- License rights describe **usage rights granted by the deal**, not technical enforcement beyond what the software provides. You are responsible for complying with AppSumo terms and the seller's license terms (`LICENSE`).
- **Self-hosted responsibility:** backups, security, hosting, and updates remain your responsibility.

## Card-free redemption (no credit card in the app)

Payment is handled entirely by AppSumo. **No credit card is entered in VentureX ERP & CRM** — activation is by code only.

1. Purchase the deal on **AppSumo** and retrieve your **1 or 2 codes** from your order. Keep codes private.
2. Download and install the release package following `INSTALLATION.md` and `README.md` (copy `.env.example` to `.env`, configure the database, run migrations as documented).
3. Sign in as an **administrator** and open **Admin → Settings → Plan & Layers** (the in-app Plan/Licensing area).
4. Paste your **first code** into the redemption field and submit. This activates the **1 Code / $49** entitlement. No card details are requested at any point.
5. Verify the active tier on the **Plan & Layers** screen.

## Stacking 2 codes ($98)

1. With the first code applied, stay in **Admin → Settings → Plan & Layers**.
2. Paste the **second code** in the same redemption field and submit.
3. The plan upgrades to **2 Codes Stacked / $98**: full ERP/CRM multi-company, advanced reporting, role permissions, priority support, multiple license rights.
4. **Maximum 2 codes.** A third code cannot be stacked on this deal.
5. Keep records of your AppSumo order ID and codes for support. Never post codes publicly.

## Reviewer login (local preview only)

- **URL:** `http://127.0.0.1:8000/login` (or `http://localhost:8000/login`)
- **Super Admin:** `demo_admin@example.com` / `Demo_Admin_2026!`
- **CEO:** `demo_manager@example.com` / `Demo_Manager_2026!`
- **Sales Manager:** `demo_sales@example.com` / `Demo_Sales_2026!`

These LOCAL DEMO accounts are for throwaway local evaluation only. **Production credentials are created during installation and are never bundled in the ZIP.**

## Where to find related docs in this package

- `README.md` — product overview and quick start
- `INSTALLATION.md` — installation steps
- `ADMIN-GUIDE.md` — administration notes
- `PRICING.md` — canonical pricing and redemption reference
- `release/APPSUMO-LISTING.md` (seller side) — marketing draft; **do not** treat as license terms

## Support

Support level (**Standard** vs **Priority**) follows the **AppSumo deal tier** you purchased (1 code / $49 vs 2 codes / $98) as published at purchase time. Contact details: use the support channel listed on the AppSumo product page or in your seller communications.

---

*Keep in sync with `PRICING.md`, `APPSUMO-LISTING.md`, and the live AppSumo listing.*
