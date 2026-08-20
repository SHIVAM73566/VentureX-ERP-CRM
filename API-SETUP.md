# API Setup Guide

**VentureX ERP & CRM â€” AI-Powered CRM & ERP Business Operating System**

> Version 1.0.0 | AI Provider and API Configuration

---

## Table of Contents

1. [Overview](#overview)
2. [AI Provider Configuration](#ai-provider-configuration)
3. [NVIDIA NIM API](#nvidia-nim-api)
4. [Swift API](#swift-api)
5. [Google Gemini API](#google-gemini-api)
6. [DeepSeek API](#deepseek-api)
7. [Anthropic Claude API](#anthropic-claude-api)
8. [API Key Management](#api-key-management)
9. [Rate Limiting](#rate-limiting)
10. [Webhook Configuration](#webhook-configuration)
11. [External API Integrations](#external-api-integrations)

---

## Overview

VentureX ERP & CRM integrates with multiple AI providers through a unified gateway architecture. Each provider is configured independently, enabling fallback chains, cost optimization, and workload distribution.

### Supported Providers

| Provider           | Models Supported                     | Use Cases                          |
|--------------------|---------------------------------------|------------------------------------|
| NVIDIA NIM         | Llama, Mistral, Nemotron              | Fast inference, batch processing   |
| Swift              | Proprietary LLMs                      | Conversational AI, content gen     |
| Google Gemini      | Gemini 2.5 Pro, Gemini Flash          | Multimodal analysis, search        |
| DeepSeek           | DeepSeek-R1, DeepSeek V3              | Code generation, reasoning         |
| Anthropic Claude   | Claude Opus, Sonnet, Haiku            | Document analysis, long-form gen   |

### Architecture

```
Application Request
        â”‚
        â–¼
   â”Œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”
   â”‚  AI Gateway  â”‚
   â””â”€â”€â”€â”€â”€â”€â”¬â”€â”€â”€â”€â”€â”€â”˜
          â”‚
    â”Œâ”€â”€â”€â”€â”€â”¼â”€â”€â”€â”€â”€â”¬â”€â”€â”€â”€â”€â”€â”€â”€â”€â”¬â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”
    â–¼     â–¼     â–¼         â–¼          â–¼
 NVIDIA Swift Gemini DeepSeek Claude
```

The AI Gateway handles:

- Request routing based on task type and provider availability
- Automatic failover when a provider is unavailable
- Response caching to reduce API calls
- Token counting and quota enforcement
- Cost tracking per provider

---

## AI Provider Configuration

### Environment Variables

All AI provider settings are configured in the `.env` file:

```ini
# AI Gateway Configuration
AI_ENABLED=true
AI_DEFAULT_PROVIDER=nvidia
AI_FALLBACK_ENABLED=true
AI_CACHE_ENABLED=true
AI_CACHE_TTL=3600

# NVIDIA NIM
AI_NVIDIA_ENABLED=true
AI_NVIDIA_API_KEY=nvapi-xxxxxxxxxxxx
AI_NVIDIA_BASE_URL=https://integrate.api.nvidia.com/v1
AI_NVIDIA_MODEL=meta/llama-3.1-70b-instruct
AI_NVIDIA_MAX_TOKENS=4096
AI_NVIDIA_TEMPERATURE=0.7

# Swift
AI_SWIFT_ENABLED=false
AI_SWIFT_API_KEY=sk-swift-xxxxxxxxxxxx
AI_SWIFT_BASE_URL=https://api.swift.ai/v1
AI_SWIFT_MODEL=swift-pro
AI_SWIFT_MAX_TOKENS=4096
AI_SWIFT_TEMPERATURE=0.7

# Google Gemini
AI_GEMINI_ENABLED=true
AI_GEMINI_API_KEY=AIzaSyxxxxxxxxxxxx
AI_GEMINI_MODEL=gemini-2.5-pro
AI_GEMINI_MAX_TOKENS=8192
AI_GEMINI_TEMPERATURE=0.7

# DeepSeek
AI_DEEPSEEK_ENABLED=true
AI_DEEPSEEK_API_KEY=sk-deepseek-xxxxxxxxxxxx
AI_DEEPSEEK_BASE_URL=https://api.deepseek.com/v1
AI_DEEPSEEK_MODEL=deepseek-r1
AI_DEEPSEEK_MAX_TOKENS=4096
AI_DEEPSEEK_TEMPERATURE=0.7

# Anthropic Claude
AI_CLAUDE_ENABLED=true
AI_CLAUDE_API_KEY=sk-ant-xxxxxxxxxxxx
AI_CLAUDE_MODEL=claude-sonnet-4-20250514
AI_CLAUDE_MAX_TOKENS=8192
AI_CLAUDE_TEMPERATURE=0.7
```

### Configuration via Admin Panel

1. Navigate to **Settings > AI Providers**.
2. Click the provider you want to configure.
3. Toggle **Enabled** on or off.
4. Enter your API key in the secure field.
5. Adjust model settings as needed.
6. Click **Test Connection** to verify the configuration.
7. Click **Save**.

### Fallback Chain Configuration

1. Navigate to **Settings > AI Providers > Fallback Chain**.
2. Drag providers into the desired fallback order.
3. The system will try providers in order when the primary is unavailable.

Example fallback chain:

```
Primary: NVIDIA NIM
  â””â”€ Fallback 1: Google Gemini
       â””â”€ Fallback 2: Anthropic Claude
            â””â”€ Fallback 3: DeepSeek
```

---

## NVIDIA NIM API

### Getting Your API Key

1. Visit [build.nvidia.com](https://build.nvidia.com).
2. Create an account or sign in.
3. Navigate to **API Keys** in your dashboard.
4. Click **Generate API Key**.
5. Copy the key (starts with `nvapi-`).

### Supported Models

| Model                        | Context Window | Best For                  |
|------------------------------|----------------|---------------------------|
| meta/llama-3.1-70b-instruct  | 128K tokens    | General purpose           |
| meta/llama-3.1-8b-instruct   | 128K tokens    | Fast responses            |
| mistralai/mistral-large      | 32K tokens     | Complex reasoning         |
| nvidia/nemotron-ultra-253b   | 128K tokens    | Enterprise tasks          |

### Configuration

Set in `.env`:

```ini
AI_NVIDIA_ENABLED=true
AI_NVIDIA_API_KEY=nvapi-xxxxxxxxxxxx
AI_NVIDIA_BASE_URL=https://integrate.api.nvidia.com/v1
AI_NVIDIA_MODEL=meta/llama-3.1-70b-instruct
AI_NVIDIA_MAX_TOKENS=4096
AI_NVIDIA_TEMPERATURE=0.7
AI_NVIDIA_TOP_P=0.9
```

### Rate Limits

NVIDIA NIM free tier provides:

- 1,000 API calls per day
- 5 requests per second
- 4,000 tokens per minute

Paid plans offer higher limits. Check your NVIDIA dashboard for current usage.

---

## Swift API

### Getting Your API Key

1. Visit the Swift AI dashboard at your organization's Swift endpoint.
2. Navigate to **API Access**.
3. Generate a new API key.
4. Copy the key (starts with `sk-swift-`).

### Supported Models

| Model         | Context Window | Best For                    |
|---------------|----------------|-----------------------------|
| swift-pro     | 16K tokens     | High-quality content        |
| swift-fast    | 8K tokens      | Quick responses             |
| swift-vision  | 16K tokens     | Image and document analysis |

### Configuration

Set in `.env`:

```ini
AI_SWIFT_ENABLED=true
AI_SWIFT_API_KEY=sk-swift-xxxxxxxxxxxx
AI_SWIFT_BASE_URL=https://api.swift.ai/v1
AI_SWIFT_MODEL=swift-pro
AI_SWIFT_MAX_TOKENS=4096
AI_SWIFT_TEMPERATURE=0.7
```

### Rate Limits

Default limits vary by plan. Configure custom limits in the admin panel under **Settings > AI Providers > Swift > Rate Limits**.

---

## Google Gemini API

### Getting Your API Key

1. Visit the [Google AI Studio](https://aistudio.google.com).
2. Sign in with your Google account.
3. Navigate to **API Keys**.
4. Click **Create API Key**.
5. Select or create a Google Cloud project.
6. Copy the generated key.

### Supported Models

| Model              | Context Window | Best For                       |
|--------------------|----------------|--------------------------------|
| gemini-2.5-pro     | 1M tokens      | Complex analysis, long docs    |
| gemini-2.5-flash   | 1M tokens      | Fast multimodal tasks          |
| gemini-2.0-flash   | 1M tokens      | Balanced speed and quality     |

### Configuration

Set in `.env`:

```ini
AI_GEMINI_ENABLED=true
AI_GEMINI_API_KEY=AIzaSyxxxxxxxxxxxx
AI_GEMINI_MODEL=gemini-2.5-pro
AI_GEMINI_MAX_TOKENS=8192
AI_GEMINI_TEMPERATURE=0.7
AI_GEMINI_TOP_P=0.95
AI_GEMINI_TOP_K=40
```

### Rate Limits

Free tier:

- 15 requests per minute
- 1,000,000 tokens per minute
- 1,500 requests per day

Pay-as-you-go offers significantly higher limits through Google Cloud.

---

## DeepSeek API

### Getting Your API Key

1. Visit [platform.deepseek.com](https://platform.deepseek.com).
2. Create an account or sign in.
3. Navigate to **API Keys**.
4. Click **Create New Key**.
5. Copy the key (starts with `sk-deepseek-`).

### Supported Models

| Model           | Context Window | Best For                      |
|-----------------|----------------|-------------------------------|
| deepseek-r1     | 64K tokens     | Reasoning, mathematics        |
| deepseek-v3     | 64K tokens     | General purpose, fast         |
| deepseek-coder  | 64K tokens     | Code generation and analysis  |

### Configuration

Set in `.env`:

```ini
AI_DEEPSEEK_ENABLED=true
AI_DEEPSEEK_API_KEY=sk-deepseek-xxxxxxxxxxxx
AI_DEEPSEEK_BASE_URL=https://api.deepseek.com/v1
AI_DEEPSEEK_MODEL=deepseek-r1
AI_DEEPSEEK_MAX_TOKENS=4096
AI_DEEPSEEK_TEMPERATURE=0.7
```

### Rate Limits

Default limits:

- 60 requests per minute
- 10,000 tokens per minute

---

## Anthropic Claude API

### Getting Your API Key

1. Visit [console.anthropic.com](https://console.anthropic.com).
2. Create an account or sign in.
3. Navigate to **API Keys**.
4. Click **Create Key**.
5. Copy the key (starts with `sk-ant-`).

### Supported Models

| Model            | Context Window | Best For                          |
|------------------|----------------|-----------------------------------|
| claude-opus-4    | 200K tokens    | Most capable, complex tasks       |
| claude-sonnet-4  | 200K tokens    | Balanced performance              |
| claude-haiku     | 200K tokens    | Fast and cost-effective           |

### Configuration

Set in `.env`:

```ini
AI_CLAUDE_ENABLED=true
AI_CLAUDE_API_KEY=sk-ant-xxxxxxxxxxxx
AI_CLAUDE_MODEL=claude-sonnet-4-20250514
AI_CLAUDE_MAX_TOKENS=8192
AI_CLAUDE_TEMPERATURE=0.7
```

### Rate Limits

Default limits (varies by plan):

- Tier 1: 50 requests per minute, 40,000 tokens per minute
- Tier 2: 1,000 requests per minute, 400,000 tokens per minute

---

## API Key Management

### Storing API Keys

API keys are stored encrypted in the database using Laravel's built-in encryption. They are never exposed in full in the admin panel â€” only the last 4 characters are shown.

### Key Rotation

1. Navigate to **Settings > AI Providers**.
2. Click on the provider.
3. Click **Rotate Key**.
4. Enter the new key.
5. The old key is archived and the new key takes effect immediately.
6. Test the connection to verify the new key works.

### Key Security Best Practices

- Never commit API keys to version control.
- Use environment variables for all keys.
- Rotate keys every 90 days.
- Monitor API usage for unauthorized access.
- Use separate keys for development and production.
- Set spending limits where the provider supports it.

### Viewing API Usage

Navigate to **Settings > AI Providers > Usage** to view:

- **Calls Today** â€” Number of API calls made today
- **Tokens Used** â€” Total tokens consumed today
- **Cost Estimate** â€” Estimated cost based on provider pricing
- **Error Rate** â€” Percentage of failed requests
- **Average Response Time** â€” Mean latency per provider

---

## Rate Limiting

### Application-Level Rate Limits

VentureX ERP & CRM enforces rate limiting to prevent abuse and control costs.

#### Default Limits

| Scope              | Limit                    | Window     |
|--------------------|--------------------------|------------|
| Per User           | 100 AI queries           | Per day    |
| Per Company         | 10,000 AI queries        | Per month  |
| Per API Token      | 60 requests              | Per minute |
| Global             | 1,000 requests           | Per minute |
| Export Operations   | 10 exports               | Per hour   |

#### Configuring Limits

1. Navigate to **Settings > Security > Rate Limits**.
2. Adjust limits for each scope.
3. Configure response behavior when limits are exceeded:
   - **Return Error** â€” Return a 429 status code
   - **Queue Request** â€” Add to a processing queue
   - **Degrade Gracefully** â€” Use cached or simplified responses

### Provider-Level Rate Limits

Each AI provider has its own rate limits. The application tracks usage per provider and automatically:

1. Switches to an alternative provider when limits are approached.
2. Queues requests when all providers are near their limits.
3. Notifies administrators when usage exceeds 80% of limits.

### Handling Rate Limit Errors

When a rate limit is hit, the application:

1. Retries after a backoff period (1s, 2s, 4s, 8s, max 3 retries).
2. Falls back to the next provider in the chain.
3. Returns a user-friendly error if all providers are exhausted.

---

## Webhook Configuration

### Outgoing Webhooks

Configure webhooks to notify external systems of events:

1. Navigate to **Settings > Webhooks**.
2. Click **+ New Webhook**.
3. Configure:
   - **URL** â€” The endpoint to receive webhook payloads
   - **Events** â€” Select which events trigger the webhook
   - **Secret** â€” A shared secret for payload verification
   - **Headers** â€” Custom headers to include in the request
4. Click **Test Webhook** to verify connectivity.
5. Click **Save**.

### Webhook Events

| Event                  | Description                                    |
|------------------------|------------------------------------------------|
| customer.created       | New customer added                             |
| customer.updated       | Customer record modified                       |
| opportunity.created    | New sales opportunity                          |
| opportunity.won        | Opportunity closed as won                      |
| order.created          | New sales order                                |
| order.shipped          | Order marked as shipped                         |
| invoice.created        | New invoice generated                          |
| invoice.paid           | Invoice fully paid                             |
| stock.low              | Product stock below reorder level              |
| approval.pending       | New item awaiting approval                     |
| ai.report.generated    | AI report completed                            |

### Webhook Payload Format

```json
{
  "event": "customer.created",
  "timestamp": "2025-07-15T10:30:00Z",
  "data": {
    "id": 1234,
    "name": "Acme Corporation",
    "email": "info@acme.example"
  },
  "metadata": {
    "user_id": 5,
    "company_id": 1
  }
}
```

### Incoming Webhooks

Configure incoming webhooks for integrations with external systems:

1. Navigate to **Settings > Webhooks > Incoming**.
2. Generate a webhook URL with a unique token.
3. Configure the external system to send data to this URL.
4. Map incoming fields to application fields.

---

## External API Integrations

### REST API

VentureX ERP & CRM exposes a RESTful API for external integrations.

#### Authentication

```bash
# Obtain an API token
POST /api/auth/token
Content-Type: application/json

{
  "email": "admin@yourdomain.com",
  "password": "your-admin-password"
}
```

#### Using the Token

```bash
GET /api/customers
Authorization: Bearer your-api-token-here
Accept: application/json
```

#### Available Endpoints

| Method  | Endpoint                    | Description                |
|---------|-----------------------------|----------------------------|
| GET     | /api/customers              | List customers             |
| POST    | /api/customers              | Create customer            |
| GET     | /api/customers/{id}         | Get customer details       |
| PUT     | /api/customers/{id}         | Update customer            |
| GET     | /api/contacts               | List contacts              |
| POST    | /api/contacts               | Create contact             |
| GET     | /api/leads                  | List leads                 |
| POST    | /api/leads                  | Create lead                |
| GET     | /api/opportunities          | List opportunities         |
| GET     | /api/quotations             | List quotations            |
| POST    | /api/quotations             | Create quotation           |
| GET     | /api/orders                 | List sales orders          |
| GET     | /api/invoices               | List invoices              |
| GET     | /api/products               | List products              |
| GET     | /api/stock                  | List stock levels          |

#### Pagination

```bash
GET /api/customers?page=2&per_page=25
```

Response:

```json
{
  "data": [...],
  "meta": {
    "current_page": 2,
    "last_page": 10,
    "per_page": 25,
    "total": 250
  }
}
```

#### Filtering and Sorting

```bash
GET /api/customers?status=active&sort=name&order=asc&search=acme
```

---

**Next Steps:**

- Read [AI-SETUP.md](documentation/AI-SETUP.md) for detailed AI Gateway configuration
- Read [SECURITY.md](documentation/SECURITY.md) for API security best practices
- Read [DEPLOYMENT.md](documentation/DEPLOYMENT.md) for production API configuration
