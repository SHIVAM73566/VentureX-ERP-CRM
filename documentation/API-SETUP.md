# API Setup Guide

**VentureX ERP & CRM â€” AI-Powered CRM & ERP Business Operating System**

> Version 1.0.0 | AI Provider and API Key Configuration

---

## Table of Contents

1. [Overview](#overview)
2. [Environment Variables](#environment-variables)
3. [Provider Setup](#provider-setup)
4. [Fallback Configuration](#fallback-configuration)
5. [Rate Limiting and Quotas](#rate-limiting-and-quotas)
6. [Key Security Best Practices](#key-security-best-practices)

---

## Overview

VentureX ERP & CRM integrates with multiple AI providers through a unified gateway architecture. All AI calls are made by the backend â€” the browser never communicates with AI providers directly. API keys are stored as environment variables and are never exposed to clients.

### Supported Providers

| Provider           | Auth Mode       | API Key Env Var         | Use Cases                          |
|--------------------|-----------------|-------------------------|------------------------------------|
| NVIDIA NIM         | Bearer token    | `NVIDIA_API_KEY`        | Fast inference, batch processing   |
| Swift (RapidAPI)   | RapidAPI header | `RAPIDAPI_KEY`          | Conversational AI, content gen     |
| Google Gemini (RapidAPI) | RapidAPI header | `RAPIDAPI_KEY`    | Multimodal analysis, search        |
| DeepSeek (RapidAPI)| RapidAPI header  | `RAPIDAPI_KEY`          | Code generation, reasoning         |
| Claude (RapidAPI)  | RapidAPI header  | `CLAUDE_RAPIDAPI_KEY` or `RAPIDAPI_KEY` | Document analysis, long-form gen |
| OpenAI             | Bearer token    | `OPENAI_API_KEY`        | General purpose AI                 |
| Anthropic          | Bearer token    | `ANTHROPIC_API_KEY`     | Complex reasoning, analysis        |

> **Note:** VentureX ERP & CRM has **no public REST API**. AI features are accessed exclusively through the web UI (Livewire components). There are no `/api/customers`, `/api/leads`, or similar REST endpoints.

---

## Environment Variables

All AI configuration is in the `.env` file. The ERP works without any AI keys configured.

### Core Settings

```ini
# Primary AI provider: swift, gemini, deepseek, nvidia, claude, openai, anthropic
AI_PROVIDER=swift
AI_MODEL=gpt-5
AI_DEFAULT_PROVIDER=swift

# Cache and timeouts
AI_CACHE_TTL_HOURS=24
AI_REQUEST_TIMEOUT=60
AI_RETRIES=1

# Fallback order (comma-separated, tried in order when primary fails)
AI_FALLBACK_ORDER=gemini,deepseek
```

### API Keys

```ini
# RapidAPI key (used by Swift, Gemini, DeepSeek, and Claude providers)
RAPIDAPI_KEY=

# NVIDIA NIM (bearer token, direct API)
NVIDIA_API_KEY=
NVIDIA_BASE_URL=https://integrate.api.nvidia.com
NVIDIA_MODEL=nvidia/llama-3.3-nemotron-super-49b-v1.5

# OpenAI (bearer token, direct API)
OPENAI_API_KEY=
OPENAI_MODEL=gpt-4o

# Anthropic (bearer token, direct API)
ANTHROPIC_API_KEY=
ANTHROPIC_MODEL=claude-sonnet-4-20250514

# Claude via RapidAPI (optional override)
CLAUDE_RAPIDAPI_KEY=
CLAUDE_RAPIDAPI_HOST=claude-3-5-sonnet.p.rapidapi.com
CLAUDE_MODEL=claude-3-5-sonnet
```

### Rate Limiting

```ini
AI_RATE_MAX_USER_PER_HOUR=60
AI_RATE_MAX_USER_PER_DAY=200
AI_RATE_MAX_COMPANY_PER_HOUR=300
AI_QUOTA_ENABLED=true
```

### Provider Health

```ini
AI_HEALTH_MAX_FAILURES=3
AI_HEALTH_WINDOW=600
```

### Generation Parameters

```ini
AI_TEMPERATURE=0.4
AI_TOP_P=0.9
AI_MAX_TOKENS=2048
AI_DEDUP_LOCK_TTL=180
```

---

## Provider Setup

### NVIDIA NIM

1. Visit [build.nvidia.com](https://build.nvidia.com).
2. Create an account or sign in.
3. Navigate to **API Keys** and click **Generate API Key**.
4. Copy the key (starts with `nvapi-`).
5. Set in `.env`:
   ```ini
   NVIDIA_API_KEY=nvapi-xxxxxxxxxxxx
   ```
6. Optional model override:
   ```ini
   NVIDIA_MODEL=nvidia/llama-3.3-nemotron-super-49b-v1.5
   ```

### RapidAPI Providers (Swift, Gemini, DeepSeek, Claude)

Swift, Gemini, DeepSeek, and Claude are accessed through RapidAPI. A single `RAPIDAPI_KEY` works for all of them.

1. Visit [rapidapi.com](https://rapidapi.com).
2. Create an account or sign in.
3. Subscribe to the relevant AI provider APIs.
4. Copy your API key from **Security** tab in any subscribed API.
5. Set in `.env`:
   ```ini
   RAPIDAPI_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   ```

For Claude, you can optionally use a separate key:
```ini
CLAUDE_RAPIDAPI_KEY=xxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### OpenAI

1. Visit [platform.openai.com](https://platform.openai.com).
2. Create an account or sign in.
3. Navigate to **API Keys** and click **Create new secret key**.
4. Copy the key (starts with `sk-`).
5. Set in `.env`:
   ```ini
   OPENAI_API_KEY=sk-xxxxxxxxxxxx
   OPENAI_MODEL=gpt-4o
   ```

### Anthropic

1. Visit [console.anthropic.com](https://console.anthropic.com).
2. Create an account or sign in.
3. Navigate to **API Keys** and click **Create Key**.
4. Copy the key (starts with `sk-ant-`).
5. Set in `.env`:
   ```ini
   ANTHROPIC_API_KEY=sk-ant-xxxxxxxxxxxx
   ANTHROPIC_MODEL=claude-sonnet-4-20250514
   ```

### Testing Provider Connections

From the admin panel:

1. Navigate to **Settings > AI Providers**.
2. Click the provider card.
3. Click **Test Connection**.
4. Enter a test prompt and view the response.

---

## Fallback Configuration

When the primary AI provider fails, the gateway automatically retries with the next provider in the fallback chain.

### Configuring Fallback Order

Set in `.env`:

```ini
AI_FALLBACK_ORDER=gemini,deepseek
```

The system tries providers in this order:

1. **Primary** â€” The provider set in `AI_PROVIDER`
2. **Fallback 1** â€” First provider in `AI_FALLBACK_ORDER`
3. **Fallback 2** â€” Second provider in `AI_FALLBACK_ORDER`

### Task-Based Routing

Different task types are routed to specific providers for optimal performance. This is configured in `config/ai.php` under `task_routing`. Simple tasks use low-cost providers, while complex reasoning tasks can escalate to Claude when the business impact score is high enough.

### Provider Health Monitoring

A provider that fails repeatedly (controlled by `AI_HEALTH_MAX_FAILURES` within `AI_HEALTH_WINDOW` seconds) is temporarily skipped. A successful request resets the failure counter.

---

## Rate Limiting and Quotas

### Application-Level Rate Limits

| Scope              | Default Limit          | Configurable Via              |
|--------------------|------------------------|-------------------------------|
| Per User Per Hour  | 60 queries             | `AI_RATE_MAX_USER_PER_HOUR`   |
| Per User Per Day   | 200 queries            | `AI_RATE_MAX_USER_PER_DAY`    |
| Per Company Per Hour | 300 queries          | `AI_RATE_MAX_COMPANY_PER_HOUR`|

### Quota System

When `AI_QUOTA_ENABLED=true`, role-based daily and weekly quotas are enforced. Default limits vary by role (configured in `config/ai.php` under `quota.defaults`). Users who exceed their quota see a "Request More AI Access" button in the UI for admin approval.

### Handling Rate Limits

When a rate limit is hit, the application:

1. Retries after a backoff period.
2. Falls back to the next provider in the chain if available.
3. Returns a user-friendly error if all providers are exhausted.

---

## Key Security Best Practices

- Never commit API keys to version control.
- Use environment variables for all keys (`.env` file).
- Rotate keys periodically.
- Monitor API usage for unauthorized access.
- Use separate keys for development and production.
- Set spending limits where the provider supports it.
- The AI gateway never exposes API keys to the browser or client-side code.

---

**Next Steps:**

- Read [AI-SETUP.md](AI-SETUP.md) for detailed AI Gateway configuration and features
- Read [SECURITY.md](SECURITY.md) for security best practices
- Read [DEPLOYMENT.md](DEPLOYMENT.md) for production configuration
