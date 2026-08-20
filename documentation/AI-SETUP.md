# AI Setup Guide

**VentureX ERP & CRM â€” AI-Powered CRM & ERP Business Operating System**

> Version 1.0.0 | AI Gateway and Provider Configuration

---

## Table of Contents

1. [AI Gateway Overview](#ai-gateway-overview)
2. [Provider Configuration](#provider-configuration)
3. [Quota Management](#quota-management)
4. [Response Caching](#response-caching)
5. [Fallback Chains](#fallback-chains)
6. [Cost Tracking](#cost-tracking)
7. [AI Features by Module](#ai-features-by-module)
8. [Troubleshooting](#troubleshooting)

---

## AI Gateway Overview

The AI Gateway is the central hub that manages all AI interactions within VentureX ERP & CRM. It abstracts away provider-specific details and provides a unified interface for the application.

### Architecture

```
â”Œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”
â”‚                 VentureX ERP & CRM                       â”‚
â”‚                                                   â”‚
â”‚  â”Œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”  â”Œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”  â”Œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”       â”‚
â”‚  â”‚   Sales   â”‚  â”‚   CRM    â”‚  â”‚Inventory â”‚  ...  â”‚
â”‚  â””â”€â”€â”€â”€â”€â”¬â”€â”€â”€â”€â”˜  â””â”€â”€â”€â”€â”¬â”€â”€â”€â”€â”€â”˜  â””â”€â”€â”€â”€â”¬â”€â”€â”€â”€â”€â”˜       â”‚
â”‚        â”‚            â”‚              â”‚               â”‚
â”‚        â””â”€â”€â”€â”€â”€â”€â”€â”€â”¬â”€â”€â”€â”´â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”˜               â”‚
â”‚                 â–¼                                  â”‚
â”‚        â”Œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”                          â”‚
â”‚        â”‚   AI Service   â”‚                          â”‚
â”‚        â””â”€â”€â”€â”€â”€â”€â”€â”¬â”€â”€â”€â”€â”€â”€â”€â”€â”˜                          â”‚
â”‚                â–¼                                   â”‚
â”‚     â”Œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”                       â”‚
â”‚     â”‚     AI Gateway       â”‚                       â”‚
â”‚     â”‚  â”Œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”  â”‚                       â”‚
â”‚     â”‚  â”‚  Rate Limiter  â”‚  â”‚                       â”‚
â”‚     â”‚  â”œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”¤  â”‚                       â”‚
â”‚     â”‚  â”‚  Cache Layer   â”‚  â”‚                       â”‚
â”‚     â”‚  â”œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”¤  â”‚                       â”‚
â”‚     â”‚  â”‚  Quota Engine  â”‚  â”‚                       â”‚
â”‚     â”‚  â”œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”¤  â”‚                       â”‚
â”‚     â”‚  â”‚  Router        â”‚  â”‚                       â”‚
â”‚     â”‚  â”œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”¤  â”‚                       â”‚
â”‚     â”‚  â”‚  Fallback      â”‚  â”‚                       â”‚
â”‚     â”‚  â”œâ”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”¤  â”‚                       â”‚
â”‚     â”‚  â”‚  Cost Tracker  â”‚  â”‚                       â”‚
â”‚     â”‚  â””â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”˜  â”‚                       â”‚
â”‚     â””â”€â”€â”€â”€â”€â”€â”¬â”€â”€â”€â”¬â”€â”€â”€â”¬â”€â”€â”€â”¬â”€â”€â”€â”˜                       â”‚
â”‚            â”‚   â”‚   â”‚   â”‚                            â”‚
â””â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”¼â”€â”€â”€â”¼â”€â”€â”€â”¼â”€â”€â”€â”¼â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”˜
             â–¼   â–¼   â–¼   â–¼
         NVIDIA Swift Gemini Claude DeepSeek
```

### Enabling the AI Gateway

Set in `.env`:

```ini
AI_PROVIDER=nvidia
AI_DEFAULT_PROVIDER=nvidia
```

Or toggle via the admin panel at **Settings > AI Providers**.

### Verifying Provider Connections

From the admin panel:

1. Navigate to **Settings > AI Providers**.
2. Click the provider card.
3. Click **Test Connection**.
4. Enter a test prompt and view the response and latency.

You can also check the application log for AI gateway activity:

```bash
tail -100 storage/logs/laravel.log | grep -i "ai\|provider"
```

---

## Provider Configuration

### Per-Provider Settings

Each AI provider has the following configurable settings:

| Setting           | Description                                       |
|-------------------|---------------------------------------------------|
| Enabled           | Toggle provider on or off                         |
| API Key           | API key for authentication (env variable)         |
| Base URL          | API endpoint URL (for custom deployments)         |
| Model             | Which model to use for requests                   |
| Max Tokens        | Maximum tokens per response                       |
| Temperature       | Creativity level (0.0 - 1.0)                      |

### Task-Based Routing

Route different types of tasks to different providers for optimal performance. The task routing is configured in `config/ai.php` under `task_routing`:

| Task Type              | Default Provider   | Reason                       |
|------------------------|--------------------|------------------------------|
| Email Drafts           | NVIDIA NIM         | Fast, cost-effective         |
| CRM Summaries          | NVIDIA NIM         | Fast inference               |
| General Assistant      | NVIDIA NIM         | Low latency                  |
| Inventory Analysis     | NVIDIA NIM         | Standard analysis            |
| Finance Analysis       | NVIDIA NIM         | Standard analysis            |
| Deep Supplier Analysis | Claude             | Complex reasoning            |
| Executive Reviews      | Claude             | High-impact decisions        |
| Strategic Questions    | Claude             | Complex business reasoning   |

Simple tasks always use low-cost providers. Claude is reserved as a specialist, high-value provider for complex reasoning tasks. The AI Decision Engine escalates to Claude when the task's business impact score reaches the configured threshold (`AI_CLAUDE_MIN_IMPACT`, default: 70).

### Supported Models per Provider

| Provider    | Default Model                                | Best For                    |
|-------------|----------------------------------------------|-----------------------------|
| NVIDIA NIM  | nvidia/llama-3.3-nemotron-super-49b-v1.5    | General purpose, fast       |
| Swift       | gpt-5 (via RapidAPI)                         | High-quality content        |
| Gemini      | gemini-3.5-flash (via RapidAPI)              | Fast multimodal tasks       |
| DeepSeek    | DeepSeek-V3.2 (via RapidAPI)                 | Reasoning, code             |
| Claude      | claude-3-5-sonnet (via RapidAPI)             | Complex analysis            |
| OpenAI      | gpt-4o                                       | General purpose             |
| Anthropic   | claude-sonnet-4-20250514                     | Complex reasoning           |

---

## Quota Management

### Global Quotas

Configure system-wide AI usage quotas:

1. Navigate to **Settings > AI Providers > Quotas**.
2. Set limits:

| Quota Type                | Default    | Description                            |
|---------------------------|------------|----------------------------------------|
| Per User Per Day          | Role-based | Queries per user per day               |
| Per User Per Week         | Role-based | Queries per user per week              |
| Max Tokens Per Query      | 2,048      | Token limit per individual request     |

### Per-Role Default Quotas

Default daily/weekly quotas by role:

| Role              | Daily  | Weekly |
|-------------------|--------|--------|
| super_admin       | 999    | 9,999  |
| ceo / cfo / coo   | 100    | 500    |
| company_admin     | 100    | 500    |
| sales_manager     | 50     | 200    |
| finance_manager   | 50     | 200    |
| sales_executive   | 30     | 120    |
| accountant        | 30     | 120    |
| warehouse_manager | 30     | 120    |
| hr_manager        | 30     | 120    |
| viewer            | 5      | 15     |
| user              | 1      | 3      |

### Quota Enforcement

When a quota is reached:

1. The user sees a clear message indicating they've reached their limit.
2. Administrators receive a notification.
3. The user can request a quota increase through the admin panel.
4. Quotas reset based on the configured period.

### Monitoring Quota Usage

Navigate to **Settings > AI Providers > Usage Dashboard** to view:

- **Usage by User** â€” Who is using the most AI resources
- **Usage by Module** â€” Which features consume the most queries
- **Usage by Provider** â€” Distribution across providers
- **Cost Breakdown** â€” Estimated cost per user, module, and provider

---

## Response Caching

### How Caching Works

When a user asks the AI a question, the gateway first checks the cache. If an identical query has been made recently, the cached response is returned without making an API call.

### Cache Configuration

Set in `.env`:

```ini
AI_CACHE_TTL_HOURS=24
```

The cache TTL is configured via `AI_CACHE_TTL_HOURS` (in hours, converted to seconds internally). The default is 24 hours.

### Cache Management

From the admin panel:

1. Navigate to **Settings > AI Providers > Cache**.
2. View cache statistics:
   - **Hit Rate** â€” Percentage of queries served from cache
   - **Total Cached** â€” Number of cached responses
3. Click **Clear Cache** to flush all cached responses.

### Cache Invalidation

The cache is automatically invalidated when:

- The TTL expires
- The underlying data changes significantly
- A provider configuration changes
- The cache is manually cleared

---

## Fallback Chains

### How Fallback Works

When a request is routed to a provider and fails, the gateway automatically retries with the next provider in the chain.

### Failure Detection

A provider is considered failed when:

- It returns an HTTP 5xx error
- It times out (exceeds the configured timeout)
- It returns a rate limit error (HTTP 429)
- It returns an invalid response

### Configuring Fallback Chains

Set in `.env`:

```ini
AI_FALLBACK_ORDER=gemini,deepseek
```

The system tries providers in this order:

1. **Primary** â€” The provider set in `AI_PROVIDER`
2. **Fallback 1** â€” First provider in `AI_FALLBACK_ORDER`
3. **Fallback 2** â€” Second provider in `AI_FALLBACK_ORDER`

### Provider Health Monitoring

A provider that fails repeatedly within the health window is temporarily skipped:

- `AI_HEALTH_MAX_FAILURES` â€” Number of failures before skipping (default: 3)
- `AI_HEALTH_WINDOW` â€” Time window in seconds (default: 600)

A successful request resets the failure counter.

### Fallback Scenarios

| Scenario                           | Behavior                                      |
|-------------------------------------|-----------------------------------------------|
| Primary provider fails              | Try next provider in chain                    |
| All providers fail                  | Return cached response if available           |
| No cached response                  | Return error with suggested retry time        |
| Rate limit on primary               | Automatically use next provider               |
| Provider returns empty response     | Treat as failure, try next provider           |

---

## Cost Tracking

### Cost Configuration

Estimated cost per million tokens is configured in `config/ai.php` under `cost_per_mtok`:

| Provider    | Input Cost (per 1M tokens) | Output Cost (per 1M tokens) |
|-------------|----------------------------|-----------------------------|
| NVIDIA NIM  | $0.20                      | $0.60                       |
| Swift       | $0.50                      | $1.50                       |
| Gemini      | $0.20                      | $0.70                       |
| DeepSeek    | $0.27                      | $1.10                       |
| Claude      | $3.00                      | $15.00                      |
| OpenAI      | $2.50                      | $10.00                      |
| Anthropic   | $3.00                      | $15.00                      |

> Note: Costs are estimates. Update values in `config/ai.php` to match your actual provider pricing.

### Cost Dashboard

Navigate to **Settings > AI Providers > Cost Dashboard** to view:

- **Daily Cost** â€” Estimated cost per day
- **Monthly Cost** â€” Running total for the current month
- **Cost by Provider** â€” Breakdown by provider
- **Cost by User** â€” Per-user spending
- **Cost by Feature** â€” Which AI features cost the most

---

## AI Features by Module

### Sales Module AI Features

- **Sales Forecasting** â€” Predict future revenue based on historical data, pipeline, and market trends.
- **Lead Scoring** â€” Automatically score leads based on conversion probability.
- **Deal Insights** â€” Analyze opportunity health and recommend next actions.
- **Email Generation** â€” Draft follow-up emails and proposals.
- **Competitor Analysis** â€” Summarize competitive landscape for a deal.

### CRM Module AI Features

- **Customer Segmentation** â€” Identify customer segments and patterns.
- **Churn Prediction** â€” Flag at-risk customers before they leave.
- **Recommendation Engine** â€” Suggest cross-sell and upsell opportunities.
- **Sentiment Analysis** â€” Analyze customer feedback and communications.
- **Contact Enrichment** â€” Suggest missing contact information.

### Inventory Module AI Features

- **Demand Forecasting** â€” Predict product demand for better stock management.
- **Reorder Optimization** â€” Calculate optimal reorder quantities and timing.
- **Dead Stock Detection** â€” Identify slow-moving and obsolete inventory.
- **Price Optimization** â€” Suggest pricing adjustments based on market data.
- **Supplier Risk Assessment** â€” Evaluate supplier reliability and risk.

### Finance Module AI Features

- **Anomaly Detection** â€” Identify unusual financial transactions.
- **Cash Flow Prediction** â€” Forecast future cash positions.
- **Expense Categorization** â€” Auto-categorize expenses.
- **Budget Variance Analysis** â€” Analyze deviations from budget.
- **Tax Optimization** â€” Identify tax-saving opportunities.

### Document Module AI Features

- **Document Summarization** â€” Generate summaries of long documents.
- **Key Information Extraction** â€” Pull important data from documents.
- **Contract Analysis** â€” Identify key terms and risks in contracts.
- **OCR Enhancement** â€” Improve text extraction from scanned documents.
- **Document Classification** â€” Auto-categorize uploaded documents.

---

## Troubleshooting

### Common Issues

#### "AI service is unavailable"

1. Check that at least one provider is configured with a valid API key in `.env`.
2. Verify the API key is valid by clicking **Test Connection** in the admin panel.
3. Check your internet connection.
4. Review the error log: `tail -100 storage/logs/laravel.log`

#### "Rate limit exceeded"

1. Check your current usage at **Settings > AI Providers > Usage**.
2. Increase quotas if needed at **Settings > AI Providers > Quotas**.
3. Enable caching to reduce API calls.
4. Distribute requests across multiple providers.

#### "API key is invalid"

1. Verify the key is correct in `.env`.
2. Regenerate the API key at the provider's dashboard.
3. Update the key in `.env` and run `php artisan config:cache`.
4. Click **Test Connection** to verify.

#### Slow response times

1. Check the cache TTL setting (`AI_CACHE_TTL_HOURS`).
2. Check provider status at their status pages.
3. Consider using a faster model or provider.
4. Reduce `AI_MAX_TOKENS` if responses are excessively long.

#### "Quota exceeded for user/company"

1. Check current quota usage at **Settings > AI Providers > Usage**.
2. Increase the quota for the user or company.
3. Review whether cached responses could reduce consumption.

### Log Files

AI-related activity is logged in:

- `storage/logs/laravel.log` â€” General application errors including AI gateway errors

Filter AI-specific logs:

```bash
grep -i "ai\|provider\|gateway" storage/logs/laravel.log | tail -50
```

---

**Next Steps:**

- Read [API-SETUP.md](API-SETUP.md) for API key acquisition and provider setup
- Read [SECURITY.md](SECURITY.md) for AI security and privacy considerations
- Read [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for additional troubleshooting
