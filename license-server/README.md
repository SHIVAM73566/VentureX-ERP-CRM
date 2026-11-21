# License Server (separate Laravel app) — Phase 3 scaffold

> **This is a scaffold/template for a separate Laravel application.** Do **not** deploy this folder as the main ERP app. Create a new Laravel project and copy/adapt these files, or use this as a specification.

## Purpose
- Host Stripe billing (Laravel Cashier), Checkout, Customer Portal.
- Receive Stripe webhooks (signature-verified, idempotent).
- Issue **ed25519-signed license tokens** (JWT-like payload signed with Ed25519 private key) containing entitlements + expiry.
- Provide `POST /api/license/verify` for clients.
- Clients verify offline using the **public key** shipped/embedded securely, with **14-day grace** when offline/reverification fails temporarily (policy below).

## Security (non-negotiable)
- **Never** put Stripe secret keys or webhook signing secrets in the ERP client repo or any buyer ZIP. Put them **only** in this server’s `.env`.
- Verify webhook signatures using Stripe’s signing secret. Reject invalid signatures (400).
- **Idempotency**: store processed Stripe event IDs (e.g. `stripe_events` table with unique `event_id`) before applying business effects; if seen, return 2xx without reapplying.
- **No customer data deletion/lockout** on subscription lapse: mark entitlements inactive for gated features only; never delete user/company business data. Exports must remain available per product policy.
- Log minimally; never log full card numbers, raw secrets, or private keys.

## Suggested stack
- Laravel 11/12 (match your ops standard) + `laravel/cashier` (Stripe).
- Database for users/customers mapping, subscriptions cache mirror (optional), `stripe_events`, `license_issuances` (audit), entitlements snapshot.
- Queue recommended for webhook processing (with retries) but handlers must remain idempotent.

## Environment (`.env`) — examples only (set real values on server)
```env
APP_NAME="VentureX License Server"
APP_ENV=production
APP_URL=https://license.example.com

DB_*=...

# Stripe (LICENSE SERVER ONLY — never in client/ZIPs)
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
CASHIER_CURRENCY=usd

# Ed25519 signing (generate offline; store private key securely)
LICENSE_ED25519_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----"
# Or path: LICENSE_ED25519_PRIVATE_KEY_PATH=/secure/path/ed25519_private.pem
LICENSE_ED25519_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----"

# Token lifetime (days) for issued license tokens
LICENSE_TOKEN_TTL_DAYS=30
# Offline grace period (days) enforced by client policy
LICENSE_OFFLINE_GRACE_DAYS=14
```

## Webhooks (implement these event types)
Register endpoint in Stripe Dashboard: `POST https://license.example.com/stripe/webhook` (or your path).  
Use Stripe signature verification (Cashier provides helpers).

Handle at minimum:
- `checkout.session.completed`
- `invoice.paid`
- `invoice.payment_failed`
- `customer.subscription.updated`
- `customer.subscription.deleted`

### Idempotency pattern (required)
1. On request, read `Stripe-Signature`, construct event via Stripe SDK with `STRIPE_WEBHOOK_SECRET`.
2. If verification fails → `400`.
3. Insert into `stripe_events` (`event_id` unique). If duplicate key → return `200` (already processed).
4. Process event in a transaction where appropriate; on success keep record. On failure, you may delete the marker only if you want retry — **prefer**: mark processed with status/error for ops visibility, but be careful not to double-apply side effects. Simpler safe pattern: **record event_id first after successful handling** OR use a `processed_at` column and only act if not processed.
5. Never grant entitlements from unverified events.

## Ed25519-signed license tokens (concept)
- Payload (JSON) example fields:
  - `sub` (customer/account id), `lic` (license id), `entitlements`: map of layer keys → `{ "active": true, "expires_at": "..." }` or booleans with global expiry
  - `iat`, `exp`, `jti`
- Sign with Ed25519 private key (server only). Encode as compact form (e.g. base64url header.payload.signature) or JWT with `EdDSA` if using a JWT library that supports Ed25519 — ensure client verifier matches exactly.
- **Client** stores only the **public key** (embedded in app config or fetched securely at install time) and verifies signature + `exp`. If verification fails or server unreachable, allow **offline grace** up to `LICENSE_OFFLINE_GRACE_DAYS` (14) using last-known verified token cached locally, then restrict premium layers (never delete data, keep exports per policy).

### `POST /api/license/verify` (server-assisted)
- Auth: API key per installation/customer or signed challenge — define per your model.
- Input: installation identifier, current token (optional), Stripe customer mapping.
- Output: fresh signed token + entitlements + `expires_at`.
- Rate limit and log attempts (without secrets).

## Offline verification (client side — implement in ERP client)
- Verify Ed25519 signature with bundled public key.
- Check `exp` and entitlement map.
- Cache last valid token securely (file/db encrypted at rest if desired).
- If network down: if cached token signature valid and within `exp` or within 14-day grace window policy you define, allow previously granted layers except those explicitly expired by token — document clearly.
- If cannot verify at all after grace: disable premium layers only; keep core read/export flows available per Phase 1.

## Stripe CLI (test mode) — quick steps
1. Install Stripe CLI and log in: `stripe login`
2. Forward webhooks to local server:
   ```bash
   stripe listen --forward-to localhost:8000/stripe/webhook
   ```
   Copy the printed `whsec_...` into `STRIPE_WEBHOOK_SECRET` for local testing.
3. Trigger test events:
   ```bash
   stripe trigger checkout.session.completed
   stripe trigger invoice.paid
   stripe trigger customer.subscription.updated
   stripe trigger customer.subscription.deleted
   ```
4. Use Stripe test cards (e.g. `4242 4242 4242 4242`) in Checkout.
5. Verify: events received, signature verified, idempotency (replay same event id behavior) confirmed, entitlements updated, license verify returns expected token.

## Tax / merchant of record (note — decide before launch)
- **Stripe Tax**: you remain merchant of record in most setups; Stripe calculates/collects/remits where supported depending on product and regions. You must configure tax registrations/settings in Stripe and in your product pricing flow.
- **Merchant of Record (MoR)** services (e.g. Paddle, Lemon Squeezy) handle tax remittance as MoR — different legal/fee model than Stripe Checkout as MoR-less in some contexts. If you switch to MoR, webhook/licensing integration differs.
- **Action required**: choose model early. Document your choice here and in legal terms. This scaffold assumes **Stripe Checkout + your server** as the integration point unless you replace it.

## Suggested file layout (after `laravel new license-server`)
- `app/Http/Controllers/StripeWebhookController.php`
- `app/Http/Controllers/LicenseVerifyController.php`
- `app/Services/LicenseTokenService.php` (Ed25519 sign/verify helpers)
- `app/Models/StripeEvent.php` (idempotency)
- `routes/web.php` or `routes/api.php` for webhook + verify
- Migrations: `stripe_events`, entitlement/license tables as needed

## References (implementer)
- Laravel Cashier (Stripe): https://laravel.com/docs/billing
- Stripe webhooks & signatures: https://stripe.com/docs/webhooks
- Ed25519 signing: use `sodium_crypto_sign_*` (libsodium) or a well-reviewed PHP library; keep private key out of env files in production where possible (use secret manager).

