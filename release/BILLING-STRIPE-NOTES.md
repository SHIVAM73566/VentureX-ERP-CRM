# Billing & Payments (Stripe) — Informational (no secrets)

This document describes how **purchases and recurring billing** are intended to work for VentureX ERP & CRM add-ons/layers when offered by the software publisher. It is for **buyers and operators** reading this package.

## Important rules (read first)
- **No API keys, no webhook secrets, and no live credentials** belong in this software package or in any distributed ZIP. Payment secrets are configured only on the **publisher’s license/billing server** (separate system), never inside your deployed ERP application files you distribute to end users.
- This ERP can be used **without** online billing: you may operate with a one-time licensed package as agreed with the seller. Paid layers/subscriptions are optional unless your contract says otherwise.
- **Your data is protected:** if a subscription lapses, the intended behavior is to **disable premium/layer-gated features only**. **Customer/business data is not locked or deleted** solely due to non-payment, and **data export remains available** (subject to your legal/security policies). Exact behavior is defined by the version you run and the publisher’s terms.

## How checkout is meant to work (high level)
1. You choose a plan/layer on the publisher’s official checkout page (hosted by the payment provider, e.g. Stripe Checkout).
2. After successful payment, the publisher’s billing system records the entitlement (what you purchased) and issues a **signed license/entitlement token** (or equivalent) that your ERP installation can verify.
3. Your ERP checks entitlement (online when available, with a documented offline grace period if implemented). Features unlock/lock according to entitlements—not by deleting your data.

## Payouts to the publisher (Stripe)
- Customer payments are processed by the payment provider (e.g. Stripe) according to the publisher’s Stripe account and connected payout settings.
- **Payout timing, fees, taxes, and settlement** are governed by the payment provider and the publisher’s account configuration—not by this ERP source code.
- This file does **not** contain payout bank details, Stripe account identifiers, or any credentials.

## What you should do as a buyer/operator
- Buy only through **official links** provided by the publisher (website, invoice, or marketplace listing stated by the seller).
- Keep your ERP and license-server integration values (if any) in secure server environment variables on **your** infrastructure—never commit them to git or share screenshots containing secrets.
- For tax/VAT/GST and invoicing, follow the publisher’s checkout and receipt flow and your local regulations.

## Support
For billing questions, refunds, or invoice help, contact the publisher using the contact method listed in your purchase confirmation or official product page (not via secrets shared in chat/email threads).
