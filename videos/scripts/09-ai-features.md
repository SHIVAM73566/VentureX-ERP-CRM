# Video 9: AI Features & Multi-Provider Setup
**Duration:** 4–5 minutes

## Pre-Recording Checklist
- [ ] Screen recording ready
- [ ] Resolution: 1920x1080
- [ ] VentureX running at http://localhost:8000
- [ ] Logged in as demo_admin@example.com
- [ ] At least one AI provider configured in .env
- [ ] NVIDIA account created at build.nvidia.com (free tier)
- [ ] Subtitles file: ../subtitles/09-ai-features.srt

---

## Script

### [0:00 – 0:15] INTRO

**On Screen:** Title card — "Video 9: AI Features & Multi-Provider Setup"

**Voiceover:** "Welcome back. In this video we explore VentureX's built-in AI layer — a multi-provider system that powers smart search, insights, and recommendations across the entire ERP."

**Action:** Fade to VentureX dashboard.

---

### [0:15 – 0:55] AI DASHBOARD OVERVIEW

**On Screen:** Navigate to the AI Dashboard (`/ai/dashboard`). Show the overview panel with usage graphs, recent AI interactions, and provider status indicators.

**Voiceover:** "This is the AI Dashboard. At the top you see real-time provider status — green means healthy, yellow means degraded, red means down. Below that is your usage breakdown showing tokens consumed today, this week, and this month. Every AI interaction in the system is logged here so you have full visibility."

**Action:** Hover over each status indicator. Scroll down to show the usage chart. Click into the recent interactions table to show a sample entry.

---

### [0:55 – 1:35] AI USAGE & PLAN PAGE

**On Screen:** Navigate to AI Usage & Plan page (`/ai/usage`). Show per-user and per-company quota panels.

**Voiceover:** "The Usage and Plan page shows quota allocation at two levels — per user and per company. Each user gets a daily and hourly token limit. When a user hits their limit, the system returns a friendly message instead of an error. The company-level limit acts as a hard ceiling. Admins can adjust these limits from Settings."

**Action:** Point to the user quota bar, then the company quota bar. Show the rate-limit breakdown: requests per hour and requests per day. Briefly open the settings panel where limits can be edited.

---

### [1:35 – 2:25] MULTI-PROVIDER ARCHITECTURE

**On Screen:** Open `app/Services/Ai/AiRouter.php` in the code editor. Show the class structure and the `route()` method.

**Voiceover:** "VentureX uses a multi-provider architecture. Three providers are supported out of the box — Gemini for creative and conversational tasks, NVIDIA for code generation and technical analysis, and OpenAI as a general-purpose fallback. The AiRouter class decides which provider handles each request based on the task type."

**Action:** Highlight the `route()` method. Show the task-type mapping — for example `creative → gemini`, `code → nvidia`, `general → openai`. Scroll down to show the provider instantiation logic.

**Voiceover:** "When a request comes in, the router inspects the task type, selects the best provider, and if that provider is unavailable, it walks through the fallback order configured in your environment."

**Action:** Point to the fallback loop in the code.

---

### [2:25 – 2:55] CONFIGURING NVIDIA API KEY

**On Screen:** Show https://build.nvidia.com in the browser. Then switch to the `.env` file.

**Voiceover:** "NVIDIA offers a generous free tier for API access. Sign up at build.nvidia.com, generate an API key, and add it to your .env file as NVIDIA_API_KEY. The free tier gives you enough tokens for development and small production workloads."

**Action:** Show the NVIDIA dashboard, copy an API key. Open `.env`, paste the key next to `NVIDIA_API_KEY=`. Also point out `AI_PROVIDER=` and `AI_FALLBACK_ORDER=` on adjacent lines.

---

### [2:55 – 3:25] AI HEALTH MONITORING

**On Screen:** Navigate to AI Health page (`/ai/health`). Show the provider status dashboard.

**Voiceover:** "The Health page is your operations console for AI. Each provider shows its last successful ping, average response time, error rate over the last 24 hours, and current availability percentage. If a provider drops below 95% availability, the system automatically shifts traffic to the next provider in the fallback order."

**Action:** Hover over each provider card. Expand the response-time graph. Show the auto-failover toggle.

---

### [3:25 – 3:55] AI QUOTA SYSTEM & RATE LIMITING

**On Screen:** Show the quota enforcement code — `app/Services/Ai/AiQuotaManager.php`. Then switch back to the browser to demonstrate hitting a limit.

**Voiceover:** "Quota enforcement happens in the AiQuotaManager. Every request checks two gates — the hourly rate limit and the daily token cap. Both are configurable per user role. For example, a sales manager might get 500 requests per day while a CEO gets unlimited. If a limit is reached, the system returns a clear message and logs the event for auditing."

**Action:** Show the rate-limit check in code. Then in the browser, trigger a quota-exceeded scenario (if possible) or show the admin panel where limits are configured.

---

### [3:55 – 4:15] USING AI IN PRACTICE

**On Screen:** Demonstrate AI-powered search, insights, and recommendations in the CRM module.

**Voiceover:** "In practice, AI is woven throughout VentureX. Use smart search to find customers by natural language. Open a customer record and click Insights to get AI-generated notes on engagement patterns. In sales, the recommendation engine suggests next-best actions based on deal stage and historical data."

**Action:** Type a natural-language search query into the global search bar. Open a customer record, click the AI Insights tab. Open a deal and show the AI recommendation panel.

---

### [4:15 – 4:40] FALLBACK SYSTEM DEMONSTRATION

**On Screen:** Temporarily disable the primary AI provider in `.env`, then trigger an AI request to show the fallback in action.

**Voiceover:** "If a provider goes down, the fallback system kicks in seamlessly. I'll disable the primary provider now and make a request — you'll see the system automatically route to the next available provider without any user-facing error."

**Action:** Comment out the primary provider's API key in `.env`. Refresh the AI dashboard. Make an AI search request. Show the health page updating the failed provider to red and the fallback provider taking over.

---

### [4:40 – 4:55] .ENV CONFIGURATION SUMMARY

**On Screen:** Show the full AI section of the `.env` file.

**Voiceover:** "Here's the complete AI configuration block. AI_PROVIDER sets your primary provider. NVIDIA_API_KEY, GEMINI_API_KEY, and OPENAI_API_KEY hold your credentials. AI_FALLBACK_ORDER defines the retry sequence. AI_DAILY_LIMIT and AI_HOURLY_LIMIT set global caps. That covers everything for AI setup."

**Action:** Highlight each relevant `.env` variable as it is mentioned.

---

### [4:55 – 5:00] OUTRO

**On Screen:** Title card — "Next: Settings, Users & Going Live"

**Voiceover:** "That's the AI layer. In the final video we cover settings, user management, and how to take VentureX live. See you there."

**Action:** Fade to black.

---

## Key Screenshots to Capture
1. AI Dashboard overview
2. Usage & Plan page with quota bars
3. AiRouter.php route method
4. NVIDIA free-tier signup page
5. .env AI configuration block
6. AI Health provider status cards
7. Quota enforcement in code
8. AI search in action
9. Fallback demonstration
10. Final .env summary
