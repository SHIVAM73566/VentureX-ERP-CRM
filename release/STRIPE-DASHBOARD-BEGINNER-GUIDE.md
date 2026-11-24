# Stripe Dashboard — beginner setup guide

This guide walks you through the Stripe Dashboard clicks. Do **not** paste secret keys, webhook secrets, or Price IDs into chat, tickets, Blade, JavaScript, GitHub, or release ZIPs.

Use **Test mode / sandbox** while setting up. The toggle is at the top right of the Stripe Dashboard. If your CLI is logged into a live account, previewing is still safe, but creating prices requires the explicit `-AllowLive` switch.

---

## Before you start

1. Open <https://dashboard.stripe.com/test>.
2. Make sure **Test mode** is switched on.
3. Run this in a terminal on the server:

```powershell
stripe login
```

A browser window opens. Log in to your Stripe account and approve the CLI. You only need to do this once per machine.

---

## Step 1 — Create 18 Products and Prices

### Option A — automatic (recommended, minimal clicking)

1. In Command Prompt, log in once:
   ```powershell
   stripe login
   ```
2. Preview the prices (safe — creates nothing):
   ```powershell
   cd C:\MY_ERP
   powershell -NoProfile -ExecutionPolicy Bypass -File .\release\stripe-provision-prices.ps1
   ```
3. When the preview looks right, create the Products/Prices and write their IDs to `.env`.

   Test/sandbox CLI account:
   ```powershell
   powershell -NoProfile -ExecutionPolicy Bypass -File .\release\stripe-provision-prices.ps1 -Apply
   ```

   Live CLI account (creates real live Stripe Products/Prices):
   ```powershell
   powershell -NoProfile -ExecutionPolicy Bypass -File .\release\stripe-provision-prices.ps1 -Apply -AllowLive
   ```

The helper:
- backs up `.env`,
- skips any `STRIPE_PRICE_*` that is already set,
- creates each Product and Price in the active CLI mode; live mode requires `-AllowLive` and the helper adds Stripe CLI's `--live` flag automatically,
- shows only redacted Stripe error messages if a command fails (no keys, account IDs or Price IDs),
- writes only the Price IDs to `.env` (values are never printed),
- preserves every unrelated `.env` line,
- runs `php artisan config:clear`.

**To change a price:** open `release\stripe-provision-prices.ps1` and edit the `AmountCents` and `Display` values in the price list near the top. Amounts are in cents (`14900` = `$149`). Then run the `-Apply` command again with `-Force` (and `-AllowLive` only if you intentionally use a live account).

Starter values below are **illustrative only**. Final price, tax and renewal terms always appear on Stripe Checkout before payment.

| # | Product name | Starter | `.env` key |
|---:|---|---|---|
| 1 | Updates & Support (Care) | $149/year | `STRIPE_PRICE_UPDATES_SUPPORT` |
| 2 | Managed Install & Maintenance | $79/month | `STRIPE_PRICE_MANAGED_HOSTING` |
| 3 | AI Credits / Metered AI Add-On | $49 bundle | `STRIPE_PRICE_AI_CREDITS` |
| 4 | Advanced Reporting | $29/month | `STRIPE_PRICE_REPORTING_PACK_ADV` |
| 5 | Workflow automation+ pack | $39/month | `STRIPE_PRICE_WORKFLOW_AUTOMATION_PLUS` |
| 6 | Priority integrations pack | $49/month | `STRIPE_PRICE_INTEGRATIONS_PRIORITY` |
| 7 | Multi-branch / advanced operations | $59/month | `STRIPE_PRICE_MULTI_BRANCH_OPS` |
| 8 | Compliance & hardening add-on | $99/year | `STRIPE_PRICE_COMPLIANCE_HARDENING` |
| 9 | Granular roles & permissions | $29/month | `STRIPE_PRICE_GRANULAR_RBAC` |
| 10 | Security updates & audit tooling | $99/year | `STRIPE_PRICE_SECURITY_PATCHES` |
| 11 | Capacity & module expansion | $39/month | `STRIPE_PRICE_CAPACITY_EXPANSION` |
| 12 | Industry template: Manufacturing+ | $199 once | `STRIPE_PRICE_TPL_MANUFACTURING_PLUS` |
| 13 | Industry template: Trading & distribution | $199 once | `STRIPE_PRICE_TPL_TRADING_DISTRIBUTION` |
| 14 | Industry template: Services / projects | $199 once | `STRIPE_PRICE_TPL_SERVICES_PROJECTS` |
| 15 | Advanced logistics suite | $79/month | `STRIPE_PRICE_LOGISTICS_SUITE_ADV` |
| 16 | Advanced finance suite | $79/month | `STRIPE_PRICE_FINANCE_SUITE_ADV` |
| 17 | Partner/white-label enablement (scoped) | $299 once | `STRIPE_PRICE_PARTNER_WHITE_LABEL` |
| 18 | Custom development credits | $199 bundle | `STRIPE_PRICE_CUSTOM_DEV_CREDITS` |

### Option B — manual Dashboard clicks

1. Go to **Product catalogue → Products**.
2. Click **Add product**.
3. Enter the product name from the table above.
4. Under **Pricing**, choose **Recurring → Monthly/Yearly** or **One time** to match the starter value above.
5. Enter the amount. VentureX does not invent prices.
6. Click **Save product**.
7. On the product page, copy the **Price ID**. It starts with `price_`.
8. Repeat for all 18 rows and paste each ID into the matching key in `.env`.

---

## Step 2 — Create the webhook

1. In Stripe Dashboard, go to **Developers → Webhooks**.
2. Click **Add endpoint**.
3. Endpoint URL:

```text
https://<your-host>/billing/stripe/webhook
```

   Example: `https://erp.example.com/billing/stripe/webhook`

4. Under **Events to send**, click **Select events**, then choose these six:

```text
checkout.session.completed
checkout.session.async_payment_succeeded
invoice.paid
invoice.payment_failed
customer.subscription.updated
customer.subscription.deleted
```

5. Click **Add endpoint**.
6. On the new endpoint page, click **Reveal** under **Signing secret**.
7. Copy the value. It starts with `whsec_`. You will paste it into the setup script when prompted.

---

## Step 3 — Enable the Billing Portal

1. Go to **Settings → Billing → Customer portal**.
2. Click **Activate portal** if it is not active.
3. Set the return URL to:

```text
https://<your-host>/admin/settings?tab=plan
```

4. Save.

---

## Step 4 — Get the Stripe secret key

1. Go to **Developers → API keys**.
2. In **Test mode**, copy the **Secret key**. It starts with `sk_test_`.
3. Do not copy the publishable key. VentureX uses only server-side keys.

---

## Step 5 — Fill `.env` with the setup script

From `C:\MY_ERP`, run:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\release\setup-stripe-env.ps1 -HostUrl "https://<your-host>"
```

The script will:

- back up your existing `.env`,
- prompt securely for `STRIPE_SECRET` and `STRIPE_WEBHOOK_SECRET` (typing is hidden),
- prompt only for Price IDs that are still missing (after Option A it will not ask again),
- set the success/cancel/portal URLs to `/admin/settings?tab=plan`,
- run `php artisan config:clear`.

It never prints your secrets and never commits them.

---

## Step 6 — Verify in the app

1. Log in as a super admin.
2. Open `https://<your-host>/admin/settings?tab=plan`.
3. You should see:
   - **Stripe billing status → Checkout ready**
   - **Price IDs configured: 18/18**
   - all 18 add-on cards,
   - active **Upgrade securely with Stripe** buttons.
4. Click one Upgrade button and complete a test checkout with Stripe's test card `4242 4242 4242 4242`.
5. Confirm the entitlement becomes **Active** on the plan tab.
6. Replay the webhook event from **Developers → Webhooks → your endpoint → Send test webhook**. Entitlements must not double-activate.
7. Use **Manage billing & invoices in Stripe** to confirm portal access and cancellation behavior.

---

## AppSumo rules (unchanged)

- **1 Code = $49**
- **2 Codes Stacked = $98**
- **Original/Direct comparison = $499**
- **Maximum 2 codes**
- **Card-free redemption**

Stripe is only for optional publisher-provided add-ons. It does not change AppSumo code redemption.
