# Stripe Dashboard — beginner setup guide

This guide walks you through the Stripe Dashboard clicks. Do **not** paste secret keys, webhook secrets, or Price IDs into chat, tickets, Blade, JavaScript, GitHub, or release ZIPs.

Always use **Test mode** while setting up. The toggle is at the top right of the Stripe Dashboard.

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

In Stripe Dashboard:

1. Go to **Product catalogue → Products**.
2. Click **Add product**.
3. Enter the product name from the table below.
4. Under **Pricing**, choose **Recurring → Monthly** (recommended for add-ons) or **One time** if your publisher said the add-on is one-time.
5. Enter the amount your publisher wants. VentureX does not invent prices.
6. Click **Save product**.
7. On the product page, copy the **Price ID**. It starts with `price_`.
8. Repeat for all 18 rows below.

| # | Product name | `.env` key |
|---:|---|---|
| 1 | Updates & Support (Care) | `STRIPE_PRICE_UPDATES_SUPPORT` |
| 2 | Managed Install & Maintenance | `STRIPE_PRICE_MANAGED_HOSTING` |
| 3 | AI Credits / Metered AI Add-On | `STRIPE_PRICE_AI_CREDITS` |
| 4 | Advanced Reporting | `STRIPE_PRICE_REPORTING_PACK_ADV` |
| 5 | Workflow automation+ pack | `STRIPE_PRICE_WORKFLOW_AUTOMATION_PLUS` |
| 6 | Priority integrations pack | `STRIPE_PRICE_INTEGRATIONS_PRIORITY` |
| 7 | Multi-branch / advanced operations | `STRIPE_PRICE_MULTI_BRANCH_OPS` |
| 8 | Compliance & hardening add-on | `STRIPE_PRICE_COMPLIANCE_HARDENING` |
| 9 | Granular roles & permissions | `STRIPE_PRICE_GRANULAR_RBAC` |
| 10 | Security updates & audit tooling | `STRIPE_PRICE_SECURITY_PATCHES` |
| 11 | Capacity & module expansion | `STRIPE_PRICE_CAPACITY_EXPANSION` |
| 12 | Industry template: Manufacturing+ | `STRIPE_PRICE_TPL_MANUFACTURING_PLUS` |
| 13 | Industry template: Trading & distribution | `STRIPE_PRICE_TPL_TRADING_DISTRIBUTION` |
| 14 | Industry template: Services / projects | `STRIPE_PRICE_TPL_SERVICES_PROJECTS` |
| 15 | Advanced logistics suite | `STRIPE_PRICE_LOGISTICS_SUITE_ADV` |
| 16 | Advanced finance suite | `STRIPE_PRICE_FINANCE_SUITE_ADV` |
| 17 | Partner/white-label enablement (scoped) | `STRIPE_PRICE_PARTNER_WHITE_LABEL` |
| 18 | Custom development credits | `STRIPE_PRICE_CUSTOM_DEV_CREDITS` |

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
- prompt for the 18 Price IDs,
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
