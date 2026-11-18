# VentureX ERP & CRM Connector for WordPress

**Version:** 1.3.1  
**License:** MIT  
**Maintainer:** Shivam Chaturvedi — chaturvedishivam179@gmail.com

This plugin connects a WordPress site to a self-hosted VentureX ERP & CRM v1.3.1 instance over HTTPS using a Laravel Sanctum API token.

## What is supported

| Feature | Status | Endpoint |
|---|---|---|
| Connection test | Supported | `GET /api/user` |
| `[venturex_form]` lead shortcode | Supported | `POST /api/crm/leads` |
| Authenticated admin proxy for exact mapped endpoints | Supported (administrators only) | `/wp-json/venturex/v1/proxy` |
| Customer lookup | Supported | `GET /api/crm/customers` |
| Ticket creation | Supported | `POST /api/support/tickets` |
| Order creation | Supported | `POST /api/sales/orders` |
| Contact Form 7 / Gravity Forms / comment / WooCommerce hooks | **Experimental** — verify your field mapping before production use | nearest lead/order endpoint above |

There is no `/api/health` endpoint in v1.3.1. Use the authenticated `GET /api/user` check.

## Requirements

- WordPress 5.8+ and PHP 7.4+
- VentureX ERP & CRM v1.3.1 reachable over **HTTPS** (`https://erp.example.com/api`)
- A Sanctum API token created by an ERP administrator
- WordPress `AUTH_KEY` and `LOGGED_IN_KEY` configured in `wp-config.php`
- OpenSSL with AES-256-GCM support (`openssl_encrypt` / `openssl_decrypt`)

`localhost`, `127.0.0.1` and `::1` are accepted only for local development. Remote non-HTTPS URLs are rejected.

## Install

1. Sign in to the ERP and create a Sanctum API token for an administrator account. Store it temporarily in a password manager; do not paste it into public pages, JavaScript, support tickets or chat.
2. Copy this `venturex-erp-connector` directory to `wp-content/plugins/` on the WordPress site.
3. In WordPress, go to **Plugins → Installed Plugins** and activate **VentureX ERP & CRM Connector**.
4. Go to **Settings → VentureX ERP**.
5. Set **API URL** to the ERP base API URL, for example `https://erp.example.com/api`. Do not include a trailing endpoint path.
6. Paste the Sanctum token into **API Token** and click **Save Settings**.
7. Click **Test Connection**. A successful test shows **Connected**. If it fails, check the URL, HTTPS certificate, token and ERP user permissions.
8. Add `[venturex_form]` to a page to capture a lead. The form posts through WordPress admin-ajax with a nonce; the token stays server-side.

### Upgrading from a pre-1.3.1 connector

Tokens stored by older versions used weak encryption and are deliberately not readable. After upgrading, re-enter the Sanctum token in **Settings → VentureX ERP** and click **Test Connection** again.

## Security notes

- Tokens are encrypted with **AES-256-GCM** using a key derived from `AUTH_KEY` and `LOGGED_IN_KEY` via HKDF. The ciphertext includes a random IV and authentication tag; tampering fails decryption.
- The REST proxy requires `manage_options`. It is not publicly callable and only accepts the exact endpoint keys and methods listed above.
- The token is never sent to the browser and is never written to JavaScript or logs.
- Use a separate, least-privilege ERP token for the connector and rotate it from the ERP if it may have been exposed.
- If `AUTH_KEY` or `LOGGED_IN_KEY` is missing, the settings page shows a warning and refuses to save a token.
- HTTPS is required because transport security and encryption-at-rest solve different problems.

## Troubleshooting

| Symptom | Fix |
|---|---|
| "API URL must use HTTPS" | Use the HTTPS ERP subdomain; localhost is the only exception. |
| "Token encryption is unavailable" | Define `AUTH_KEY` and `LOGGED_IN_KEY` in `wp-config.php`; enable OpenSSL. |
| Connection fails after upgrade | Re-enter the token; legacy ciphertext is intentionally rejected. |
| 403 from the proxy | The WordPress user must be an administrator with `manage_options`. |
| Lead form shows an error | Check ERP token permissions and `storage/logs/laravel.log` on the ERP server. |

## Files

- `venturex-erp-connector.php` — plugin bootstrap, secure token handling and helpers
- `includes/class-venturex-api.php` — allowlisted API calls
- `includes/class-venturex-admin.php` — settings UI and AJAX handlers
- `includes/class-venturex-forms.php` — experimental form hooks
- `includes/class-venturex-shortcode.php` — lead form and authenticated proxy
- `templates/lead-form.php` — shortcode/widget template
- `assets/` — styles and scripts

Copyright © 2026 Shivam Chaturvedi. Licensed under MIT.
