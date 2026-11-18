# VentureX ERP & CRM — Architecture Overview (Acquire Due Diligence)

## System overview
- **Stack:** Laravel 13 (PHP 8.4), Blade + Alpine.js + Tailwind CSS, Vite for asset bundling.  
- **Database:** MySQL/PostgreSQL recommended in production (SQLite acceptable only for local/dev).  
- **Tenancy model:** Company-scoped data with scoped queries and fail-closed behaviors in shared services. Roles/permissions control access to modules and sensitive actions.

### Core modules (high level)
- CRM: customers, leads, activities, follow-ups  
- Sales: quotations, sales orders, invoices, payments  
- Procurement: suppliers, purchase orders, RFQs/offers (as implemented)  
- Inventory: products, stock movements, warehouses/locations (as implemented)  
- Logistics: shipments, landed costs (as implemented)  
- Finance: chart of accounts, journals, payables/receivables flows (as implemented)  
- HR/Support/Admin: company settings, users, roles, audit logs, imports/exports (as implemented)

### Request flow (typical)
1. Browser → web routes (`routes/web.php`) → controllers in `app/Http/Controllers/*`  
2. Authorization via policies/gates and navigation/permission checks (`app/Support/*`)  
3. Eloquent models in `app/Models/*`, business logic in `app/Services/*`  
4. Views in `resources/views/*`, built assets in `public/build/` (Vite manifest)

## 84 RAG pipelines (summary)
VentureX includes an internal **Retrieval-Augmented Generation (RAG)** workspace (`/ai/rag`) built on company-scoped document stores and pipeline definitions registered in `app/Services/Rag/RagPipelineRegistry.php` (and related services under `app/Services/Rag/*`).

- **Total:** **84 pipelines** grouped into **7 domains** (as defined in the registry): e.g. Executive, CRM, Sales, Procurement, Inventory, Logistics, Finance/HR/Admin-related domains depending on product configuration.  
- **Behavior:** Each pipeline retrieves only from allowed, company-scoped sources; responses are grounded in tenant data and return sources/confidence/latency metadata in the UI/API flows.  
- **Security/ACL:** Pipeline access is gated by permissions; finance/HR/admin pipelines require elevated module permissions; cross-tenant and null-company records are excluded.  
- **Operations:** Index/sync actions are exposed via RAG controller endpoints; heavy processing may be queued when `VENTUREX_QUEUE_HEAVY_TASKS` is enabled (default off).

For the full, authoritative list and descriptions, see the in-app **Product Guide** (RAG tab) and `RagPipelineRegistry`.

## Security checklist (Acquire.com technical due diligence)
Use this checklist during review; mark items verified in your environment.

### Application & configuration
- [ ] `.env` not committed; production secrets set via secure secret storage / server env
- [ ] `APP_DEBUG=false`, `APP_ENV=production`, strong `APP_KEY`
- [ ] HTTPS enforced at reverse proxy; secure session cookies (`SESSION_SECURE_COOKIE=true`) as appropriate
- [ ] Database uses least-privilege user; backups encrypted at rest/offsite

### Authentication & authorization
- [ ] Login throttling / lockout behavior validated for deployment
- [ ] Role/permission matrix reviewed (`config/permission.php`, seeders)
- [ ] Sensitive admin actions (demo wipe, imports, AI key management) restricted to super admins and step-up protections where implemented

### Multi-tenancy & data isolation
- [ ] Company scoping enforced on queries for tenant models
- [ ] File/storage paths do not allow cross-tenant access
- [ ] RAG pipelines exclude other companies’ and null-company data

### Input validation & uploads
- [ ] Form requests / validation rules in place for major endpoints
- [ ] Upload MIME/extension checks enforced; SVG/logo restrictions applied as documented
- [ ] CSV/XLSX imports validated and size-limited

### Secrets & AI keys
- [ ] AI provider keys stored encrypted at rest (application encryption)
- [ ] Keys never logged or echoed in full in UI/flash/messages
- [ ] Outbound API calls use timeouts and error handling

### Infrastructure
- [ ] Web server document root is `public/`
- [ ] `storage/` and `bootstrap/cache/` writable by PHP-FPM user only as needed
- [ ] Queue workers and scheduler configured if queues/Horizon/scheduler are used

### Logging & monitoring
- [ ] Application logs monitored; log rotation enabled
- [ ] Error tracking (if enabled) does not capture secrets

### Dependency & release hygiene
- [ ] `composer install --no-dev --optimize-autoloader` for production
- [ ] `npm ci && npm run build` for production assets
- [ ] Run `php artisan test` (and browser smoke tests) before release

## References (in-repo)
- `README.md`, `INSTALLATION.md`, `SECURITY-AUDIT-REPORT.md`, `CHANGELOG.md`
- `docs/` and `documentation/` folders (if present) for extended runbooks

