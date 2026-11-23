# Buyer Delivery (DRAFT workflow)

> **DRAFT — process template.** No real buyer data, invoices, or credentials should be committed to this repository. Use this as an internal checklist.

## Goal
Deliver VentureX ERP & CRM (commercial licence, post–`v1.3.1` line) to a paying buyer in a controlled way: payment confirmed → invoice issued → access granted → delivery logged.

## Step 1 — Payment received
- Confirm payment in your payment processor (e.g. Stripe/Lemon Squeezy/Gumroad/bank transfer) against the agreed tier in `PRICING.md`.
- Record the transaction ID in your private accounting system (not in git).

## Step 2 — Issue invoice
- Send an invoice/receipt to the buyer (your template). Include: buyer legal name, date, tier, amount, currency, and a reference to `LICENSE` (commercial, DRAFT until finalized) and `PRICING.md`.

## Step 3 — Grant access (choose one delivery method)

### Option A: Private repository access (recommended if you use a private GitHub repo)
1. Invite the buyer (or their designated technical contact) as a **read-only** collaborator to the private repository that contains the licensed code.
2. Share via a secure channel (not public chat): the exact tag/branch to use (e.g. a release tag) and any setup notes (link to `INSTALLATION.md`).
3. Do **not** grant admin/write access unless explicitly sold as part of a separate agreement.

### Option B: Release ZIP delivery
1. Prepare a clean release archive (for example via `git archive` from the approved tag), excluding secrets (`.env`, keys, private tokens).
2. Share the ZIP and checksum (if you publish `SHA256SUMS.txt`) through a secure channel.
3. Remind the buyer: they must follow `INSTALLATION.md` and keep copies only as permitted by `LICENSE`.

## Step 4 — Log the delivery (CSV template, no real data in repo)
Maintain a **private** CSV (outside git, or in a private secure store) with columns such as:

```csv
date,buyer_name_or_company,tier,amount_currency,transaction_id,delivery_method,repo_tag_or_zip_name,notes
```

Example row (illustrative only, **do not commit real rows**):

```csv
2026-01-01,"Example Client LLC","Single Project","TODO/USD","tx_example_123","private_repo_readonly","v1.4.0","Invited GitHub user example-dev"
```

## What not to do
- Do **not** commit real buyer names, emails, transaction IDs, or invoices into this repository.
- Do **not** provide licence keys/telemetry/backdoors as part of “delivery” unless separately designed and documented (this project does not include phone-home licensing code).
- Do **not** promise updates/support beyond what is written in the buyer’s order and `PRICING.md`/`LICENSE`.

## Related files
- `LICENSE` (commercial, DRAFT)
- `LICENSE-MIT-LEGACY.md` (MIT for `v1.3.1` and earlier)
- `PRICING.md`
- `INSTALLATION.md`
