# Billing & Payments (Stripe) — server-side implementation, no secrets

This document describes the Stripe upgrade/add-on flow shipped with VentureX ERP & CRM. It contains **no API keys, webhook secrets, Stripe account identifiers, Price IDs or live credentials**.

## Non-negotiable rules

- Stripe secrets live **only** in the billing server environment (`.env` / secret manager): `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `STRIPE_PRICE_*`, `STRIPE_CHECKOUT_SUCCESS_URL`, `STRIPE_CHECKOUT_CANCEL_URL`, `STRIPE_BILLING_PORTAL_RETURN_URL`.
- No secret, publishable key, Price ID or account ID is hardcoded in Blade, JavaScript, source, docs, git history or release ZIPs.
- AppSumo pricing is unchanged: **1 Code = $49**, **2 Codes Stacked = $98**, **Original/Direct comparison = $499**, **max 2 codes**, **card-free redemption** inside the app. Stripe is for optional publisher-provided add-ons, not AppSumo code redemption.

## What is implemented

### Server-side checkout

- `POST /admin/settings/billing/checkout` (super admin, step-up, throttled) resolves the requested layer from the server-side catalog in `config/layers.php`.
- The Stripe Price ID is read only from `config('services.stripe.prices')`, which reads `STRIPE_PRICE_*` from the server environment. An unmapped layer returns 404; a missing key disables checkout.
- The Checkout Session is created server-to-server with an `Idempotency-Key`, tenant `company_id`, `layer_key` and validity metadata. The browser is redirected to Stripe-hosted Checkout. Exact price, tax and renewal terms are displayed by Stripe before payment.

### Signed, idempotent webhook

- `POST /billing/stripe/webhook` is CSRF-exempt because it authenticates with Stripe's signature instead of a browser CSRF token.
- `StripeSignatureVerifier` hashes the **raw request body** with `t.payload` using HMAC-SHA256, compares with `hash_equals`, enforces a timestamp tolerance, and rejects malformed/stale signatures with HTTP 400.
- Verified events are recorded in the `billing_events` ledger by `event_id` with a unique constraint. Duplicate deliveries are accepted without double-activating anything; concurrent `processing` deliveries are treated as accepted.
- Events handled: `checkout.session.completed`, `checkout.session.async_payment_succeeded`, `invoice.paid`, `invoice.payment_failed`, `customer.subscription.updated`, `customer.subscription.deleted`.
- Entitlements are activated/revoked inside the tenant that paid. One-time add-ons get a bounded expiry. Subscription cancellation disables only the paid layer; **business data is never deleted or locked**.

### Billing Portal

- `POST /admin/settings/billing/portal` (super admin, step-up, throttled) reads the stored `stripe_customer_id` from the tenant's entitlement record. The customer ID is never accepted from the request, URL, Blade or JavaScript.
- Stripe-hosted Billing Portal lets the customer update payment methods, download invoices and cancel subscriptions. The return URL comes from `STRIPE_BILLING_PORTAL_RETURN_URL` or defaults to the Plan & Layers settings tab.

### Plan tab visibility

- `/admin/settings?tab=plan` always renders all 18 add-on cards, the **Stripe billing status** panel, the AppSumo price reference, and the **LICENSE TOKEN (ADVANCED)** section.
- The status panel reports only readiness booleans, the exact missing `STRIPE_PRICE_*` names, the configured-price count, and the portal return URL. No secret key, webhook secret or Price ID value is ever rendered.
- When `STRIPE_SECRET` or a layer's Price ID is missing, its card stays visible with a friendly explanation and a disabled Upgrade button. Adding the value to the server `.env` and refreshing activates that card's checkout button.

### Automatic price provisioning

- `release/stripe-provision-prices.ps1` is the beginner helper. Preview mode is the default; `-Apply` creates Stripe Products/Prices after `stripe login` and writes the resulting Price IDs to the server `.env`.
- The editable price list is at the top of that script. Amounts are illustrative starter values in cents; change `AmountCents` and `Display` before running `-Apply`. Final price, tax and renewal terms remain Stripe Checkout's responsibility.
- The helper previews by default, detects live vs. test/sandbox via `stripe whoami --format json`, requires explicit `-AllowLive` before live creation, backs up `.env`, skips already-set `STRIPE_PRICE_*` keys, preserves unrelated lines, never prints secret or Price ID values, and runs `php artisan config:clear`. Native stderr from `stripe.exe` is captured safely; `$LASTEXITCODE`, not PowerShell's `NativeCommandError`, decides success.
- When the CLI session is live, the helper automatically adds Stripe CLI's `--live` flag to every Product/Price create (and dry-run) command. Without it, Stripe CLI 1.55.14 rejects write commands with `You're in live mode. Add --live to run the command, or run 'stripe switch' to select a sandbox.` That was the cause of the earlier `Product creation failed (exit code 1)`.
- On CLI failure, the helper prints only redacted Stripe error messages/codes. API keys, webhook secrets, account IDs/labels, customer/product/Price IDs, emails, tokens and URLs are replaced before display.
- `release/setup-stripe-env.ps1` now prompts only for Price IDs that are still missing, so it will not duplicate work done by the provisioning helper.

### Add-on catalog

Advanced Reporting, Granular Roles & Security Patches, Capacity Expansion, Industry Templates (Manufacturing/Trading/Services), Advanced Logistics, Advanced Finance, and Custom Development Credits are all defined in `config/layers.php`. Each offer has plain-language value copy; price and renewal terms are always shown by Stripe at checkout. There are no countdowns, fake scarcity, pre-checked consents or forced upgrades.

## Manual Stripe Dashboard steps (operator checklist)

For a click-by-click walkthrough and the secure helpers, see `release/STRIPE-DASHBOARD-BEGINNER-GUIDE.md`, `release/stripe-provision-prices.ps1`, and `release/setup-stripe-env.ps1`.

1. Create the Products/Prices matching each layer in `config/services.php` (`STRIPE_PRICE_*`). Copy each Price ID into the server `.env` only; never paste them into source, docs or chat screenshots.
2. In **Developers → Webhooks**, add `https://<your-host>/billing/stripe/webhook`.
3. Subscribe exactly these events:
   - `checkout.session.completed`
   - `checkout.session.async_payment_succeeded`
   - `invoice.paid`
   - `invoice.payment_failed`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
4. Copy the endpoint signing secret into `STRIPE_WEBHOOK_SECRET` on the server. Restart/reload PHP-FPM so the new environment is visible.
5. In **Settings → Billing → Customer portal**, enable the portal and set the return URL to the same absolute URL as `STRIPE_BILLING_PORTAL_RETURN_URL` (or `https://<your-host>/admin/settings?tab=plan`).
6. Send one test Checkout Session and confirm: redirect to Stripe, successful webhook delivery, entitlement activation, duplicate delivery idempotency, portal redirect, and cancellation revoking only the paid layer.
7. Verify the production webhook endpoint returns 2xx for valid signatures and 400 for invalid/stale signatures. Never disable signature verification to “make it work”.

## Troubleshooting

- **Checkout button does nothing / 404:** the layer has no `STRIPE_PRICE_*` value on this server, or Stripe config cache needs clearing (`php artisan config:clear && php artisan config:cache`).
- **Webhook 400:** wrong `STRIPE_WEBHOOK_SECRET`, proxy modified the body, or the timestamp is outside `STRIPE_WEBHOOK_TOLERANCE_SECONDS`. Stripe must deliver the raw body.
- **Billing Portal unavailable:** complete a Stripe upgrade first so a `stripe_customer_id` exists for the tenant, then retry with step-up authentication.
