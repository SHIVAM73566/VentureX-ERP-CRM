# AI API Keys — Secure Setup

VentureX ERP & CRM can use NVIDIA NIM (build.nvidia.com), Google Gemini,
OpenAI or Anthropic Claude for enhanced AI answers.
You can connect any provider **inside the app** - no server access or `.env`
editing required - or keep using environment variables for automated
deployments.

All AI features still work without any key using the built-in local intelligence
layer. A key only improves answer quality and model choice.

---

## 1. Get an NVIDIA API key

1. Sign in at <https://build.nvidia.com>.
2. Open **API Keys** (or <https://org.ngc.nvidia.com/setup/api-key>).
3. Generate a key. It starts with `nvapi-` and NVIDIA only shows it once.
4. Copy it somewhere safe until you paste it into VentureX.

---

## 2. Add the key in the app (recommended)

1. Sign in as a **Super Admin**.
2. Open **Administration → Settings → AI Engine**.
3. In the **NVIDIA NIM API Key** panel, paste the key.
4. Pick a model (the default is `nvidia/llama-3.3-nemotron-super-49b-v1.5`).
5. Click **Save & enable key**.

The provider is enabled for your company immediately. The page then shows:

- a green **Connected** badge,
- the masked key (last 4 characters only),
- the active model and the last update time.

Use **Run configuration check** to confirm the stored key decrypts and the
provider is wired into the runtime gateway. Use **Remove stored key** to stop
using it (the row is soft-deleted and can be restored by support).

> The plaintext key is never rendered back into the page, even when replacing
> it. The input is always blank.

---

## 3. Security model

| Control | What it does |
| --- | --- |
| **Encryption at rest** | The key is encrypted with the application cipher and `APP_KEY` before it is written to `ai_providers.encrypted_key`. A database dump alone does not reveal the key. |
| **Keyed fingerprint** | A SHA-256 HMAC (`key_hash`) is stored beside the ciphertext so the app can detect reuse/rotation without decrypting. |
| **Masked display** | Only `****last4` is ever sent to the browser. No endpoint returns plaintext. |
| **Step-up authentication** | Saving, testing, and removing a key requires a fresh password/MFA verification (5-minute window) via the `step_up` middleware. A stolen session alone cannot change keys. |
| **Rate limiting** | Key writes are limited to 5 requests/minute per user; the full provider console is limited to 10/minute. |
| **No secret round-trip** | `api_key` is registered with `dontFlash()` in `bootstrap/app.php`, so a failed validation never stores the secret in the session or re-renders it in HTML. |
| **No caching** | The Settings page is served with `Cache-Control: no-store` so credential metadata is not cached by browsers or proxies. |
| **Audit trail** | Every save/test/remove is recorded in `audit_logs` with the key **tail only** — never the secret. |
| **Tenant isolation** | Keys are scoped to `company_id`; a Super Admin of another company cannot read or change them. |
| **Least privilege** | The routes live under `role:super_admin` and also enforce the `configure` gate in the controller. |

### Where the key lives

```
Browser  ──POST (TLS)──▶  AiKeyController  ──encrypt──▶  ai_providers.encrypted_key
                              │                              + key_hash (HMAC)
                              └──audit log (tail only)──▶   audit_logs
```

Plaintext exists only in memory for the duration of the save request and when
the AI gateway signs an outbound provider call. It is never written to logs,
sessions, HTML, or the audit trail.

### Operational recommendations

- Serve the app over **HTTPS** in production (the ERP already ships HSTS and
  security headers).
- Back up `APP_KEY` securely. If it is lost, stored provider keys cannot be
  decrypted and must be re-entered. If it is leaked, rotate it and re-enter all
  provider keys.
- Rotate NVIDIA keys periodically (for example every 90 days) and immediately
  after any suspected exposure. Use **Replace & enable key**.
- Keep the Super Admin role small. Step-up auth is a second gate, not a
  replacement for least privilege.
- Review **Admin → Audit Logs** for `ai_provider_saved`, `ai_provider_test`, and
  `ai_provider_deleted` events.

---

## 4. Environment-variable fallback

For immutable infrastructure or CI-driven deployments you can still provide the
key through the environment:

```dotenv
AI_PROVIDER=nvidia
NVIDIA_API_KEY=nvapi-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
NVIDIA_MODEL=nvidia/llama-3.3-nemotron-super-49b-v1.5
NVIDIA_BASE_URL=https://integrate.api.nvidia.com
```

A key saved in the app takes precedence for the company at request time. If you
prefer to manage everything in `.env`, leave the in-app key empty.

---

## 5. Other providers and the Super Intelligence Brain

The same secure storage engine powers four key-only providers. Open
**Administration → Settings → AI Engine** and paste the matching key:

| Provider | Key prefix | Default model | Endpoint (fixed server-side) |
| --- | --- | --- | --- |
| NVIDIA NIM | `nvapi-` | `nvidia/llama-3.3-nemotron-super-49b-v1.5` | NVIDIA integrate API |
| Google Gemini | `AIza` | `gemini-2.5-flash` | `generativelanguage.googleapis.com` |
| OpenAI | `sk-` (not `sk-ant-`) | `gpt-4.1-mini` | `api.openai.com` |
| Anthropic Claude | `sk-ant-` | `claude-sonnet-4-5` | native Messages API |

No Base URL, path or auth-mode fields are exposed or accepted from the browser:
they come only from `config/ai.php`. A key with the wrong prefix is rejected
before storage.

Saving any key activates the **AERO X Super Intelligence Brain** shield. Every
AI request passes through four layers:

1. **Encryption at rest** - AES-256 via the application key, plus an HMAC
   fingerprint and masked key tail for identification.
2. **Prompt and PII sanitisation** - secrets, card numbers, emails and phone
   numbers are redacted before the prompt is cached or transmitted.
3. **Threat detection and rate limiting** - prompt-injection and abuse patterns
   are blocked and throttled per company.
4. **Audit telemetry** - provider, model, latency, token usage and security
   events are recorded without the secret.

Open **Administration → AI Providers** for the full console (setup wizard,
per-provider limits, usage, rotation, custom OpenAI-compatible endpoints and the
API security dashboard).

---

## 6. Troubleshooting

| Symptom | Fix |
| --- | --- |
| “The API key must start with nvapi-.” | You pasted a key for a different provider. Generate an NVIDIA key at build.nvidia.com. |
| Redirected to a “Verify your identity” page | Step-up authentication expired. Re-enter your password/MFA and submit again. |
| “The stored NVIDIA key could not be decrypted.” | `APP_KEY` changed or the row was corrupted. Save the key again. |
| “No gateway provider active.” | Save the key first, then run the configuration check. Also confirm `AI_PROVIDER` or the provider setting is `nvidia`. |
| AI answers still use local intelligence | Confirm the panel shows **Connected** and the AI Engine provider is set to **NVIDIA NIM**. |
| Key works but model errors | Pick a model your NVIDIA account can access; model availability changes over time. |

---

## 7. Related documentation

- [AI Setup](AI-SETUP.md)
- [Security Guide](SECURITY.md)
- [Admin Guide](ADMIN_GUIDE.md)
- [Product Guide](PRODUCT-GUIDE.md)
- [Troubleshooting](TROUBLESHOOTING.md)
