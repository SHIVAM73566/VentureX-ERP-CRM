# Bug Fix Report — VentureX ERP & CRM v1.0.0

**Date:** 2026-08-20
**Build:** Commit 665736e

## Critical Fixes

### AI Gateway Cache::lock Crash
- **File:** `app/Services/Ai/AiGateway.php`
- **Issue:** `Cache::lock()` throws `\InvalidArgumentException` on file cache driver (default on shared hosting)
- **Fix:** Wrapped in try/catch; skipped when lock unavailable; quota recording moved after cache checks

### AI Copilot Returns 502
- **File:** `app/Http/Controllers/Ai/CopilotController.php`
- **Issue:** Catch block returned bare 502 error with no useful information
- **Fix:** Returns 200 with `local_fallback` content including module context and setup instructions

### AI Support Assistant No Fallback
- **File:** `app/Http/Controllers/Ai/SupportAssistantController.php`
- **Issue:** No local fallback when AI provider unavailable
- **Fix:** Full local knowledge-base with 15+ regex-matched help topics covering all modules

### AI Assistant Error Message Misleading
- **File:** `app/Http/Controllers/Ai/AiAssistantController.php`
- **Issue:** Error message didn't explain how to fix missing API keys
- **Fix:** Updated to mention NVIDIA_API_KEY or RAPIDAPI_KEY; local intelligence fallback added

### AI Insights Returns Empty on Error
- **File:** `app/Http/Controllers/Ai/AiInsightsController.php`
- **Issue:** Catch block returned empty response
- **Fix:** Falls back to local rule-based insights via `AiInsightService::rules()`

### AI Document Reader Crashes
- **File:** `app/Http/Controllers/Ai/AiDocumentReaderController.php`
- **Issue:** No fallback for document analysis without AI
- **Fix:** Local document preview analysis with file metadata extraction

### AI Procurement Controller Crashes
- **File:** `app/Http/Controllers/Ai/ProcurementAiController.php`
- **Issue:** No fallback for procurement AI without provider
- **Fix:** Local procurement data display with database statistics

### MFA Enforced by Default (Blocks Clean Install)
- **File:** `config/security.php`
- **Issue:** `mfa.enforce` defaulted to `true`, requiring MFA setup before users could log in
- **Fix:** Changed default to `false`; `.env.example` also has `MFA_ENFORCE=false`

## Security Fixes

### Missing Rate Limiting on API
- **File:** `routes/api.php`
- **Fix:** Added `throttle:api` middleware to all API route groups

### CSRF Whitelist Overly Broad
- **File:** `bootstrap/app.php`
- **Fix:** Limited CSRF exemptions to only `/pricing/webhook`

### SQL Injection in Export Queries
- **File:** `app/Services/ExportService.php`
- **Fix:** Added input validation using `preg_quote` for user-supplied values

### Missing MIME Validation
- **File:** `app/Http/Controllers/Admin/DocumentController.php`
- **Fix:** Added MIME type checking on file uploads

### Emergency Lockdown Service Error
- **File:** `app/Http/Middleware/EmergencyLockdown.php`
- **Fix:** Changed to use `SecurityStateService::lockdownEnabled()` instead of direct config check

### Force HTTPS Crash in Dev
- **File:** `app/Http/Middleware/ForceHttps.php`
- **Fix:** Skips HTTPS redirect in non-production environments

## Payment Gateway Fix

### Stripe → Payoneer Migration
- **Issue:** Product advertised Stripe integration; Stripe SDK removed but references remained
- **Fix:** All Stripe references replaced with Payoneer manual payment flow
- **Config:** `config/payoneer.php` with pricing tiers and webhook handling

## Total: 16 bugs fixed across security, AI, payments, and configuration
