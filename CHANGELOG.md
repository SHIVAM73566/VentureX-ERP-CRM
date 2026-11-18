# Changelog — VentureX ERP & CRM

All notable changes to VentureX ERP & CRM are documented here.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.3.1] — Unreleased / pending central integration

Target release for the buyer-facing documentation and deployment metadata refresh. Test totals are intentionally not restated here; final counts, assertions and checksum values will be published in [`SECURITY-AUDIT-REPORT.md`](SECURITY-AUDIT-REPORT.md) after central integration.

### Phase 1 — Installer and data lifecycle

#### Added
- **Clean Production Mode** in the one-time `/install` wizard: creates schema, required roles/permissions, settings and the administrator chosen during setup without demo users or sample business records.
- Explicit **Demo Preview Mode** for throwaway local evaluations only, with clear LOCAL DEMO credential labelling in buyer documentation.
- A guarded **wipe/reset tool** for NonProduction environments so a local or staging instance can be returned to a clean state safely; production data is protected by environment and confirmation checks.
- Documentation for safe manual installation with `php artisan migrate --force` and no demo seeders.

#### Changed
- Installer values are written to `.env` with proper escaping; documentation now explains manual quoting for special characters.
- Installation guides standardise on PHP 8.4, MySQL 8, permissions `775` for directories and `664` for files (`755`/`644` where required), and an absolute rule that the document root must be `public/`.
- The root `.htaccess` fallback no longer uses invalid `<Directory>`/`<DirectoryMatch>` blocks or an unguarded rewrite loop; `public/.htaccess` remains the standard front controller.

### Phase 2 — Enterprise data, concurrency and performance

#### Added
- Guided **data importer** improvements with validation, row-level error reporting, templates, duplicate/update handling and audit-visible import results.
- **Enterprise indexes** on high-traffic lookup and reporting columns to reduce full-table scans in customers, inventory, sales, procurement and finance views.
- **Pessimistic locking / row-level locking** around concurrent stock and financial operations to prevent lost updates under simultaneous users.
- A repeatable **enterprise load-test plan** for verifying index and locking behaviour under sustained multi-user traffic. Results will be recorded in the audit report rather than claimed in release copy.

#### Changed
- Deployment and database guides now describe index maintenance, lock monitoring and queue-worker operation alongside backups and scheduler setup.
- Docker instructions use the bundled `docker/docker-compose.yml` with `--project-directory .` instead of an invented Compose file.

### Phase 3 — Security, integrations and release metadata

#### Security
- Hardened RBAC permission synchronisation and login throttling, including per-account and per-source IP limits.
- Continued audit coverage for CSRF, session tampering, privilege escalation, MFA/app-lock bypass, XSS, injection and AI key exposure.
- WordPress connector: API endpoint paths corrected to the implemented `/api/crm/customers`, `/api/crm/leads`, `/api/support/tickets` and `/api/sales/orders` routes; the REST proxy is no longer publicly callable; token storage uses authenticated AES-256-GCM encryption derived from WordPress `AUTH_KEY` and `LOGGED_IN_KEY`, with HTTPS required.

#### Changed
- README, installation, deployment, cPanel, VPS, API and support documentation refreshed for **v1.3.1**, PHP 8.4 and Laravel 13.
- Deploy action metadata refreshed for Ubuntu 22.04/24.04, PHP 8.4, MySQL 8, queue worker, scheduler and one-time installer behaviour. GitHub Marketplace approval is pending; no published badge is claimed.
- Release listing documents updated from v1.0.0 to v1.3.1 where the content describes the current offer; historical archive notes remain labelled as historical. MIT license wording aligned with the root `LICENSE`.
- Mojibake in the historical changelog text corrected.

#### Removed / deprecated
- Private LAN preview addresses removed from public documentation; only `localhost` and `127.0.0.1` previews are documented.
- False "Marketplace published" wording removed.
- Obsolete `VentureX-ERP-UPLOAD.zip` artifact name removed from guides.

---

## [1.3.0] — 2026-11-18

- HR suite: employee directory, attendance, leave, payroll runs, performance reviews and automated onboarding.
- AERO X Agentic Engine for autonomous, auditable business rules with transactional rollback.
- 84 enterprise RAG pipelines across CRM, procurement, inventory, finance, logistics, executive and HR domains.
- Key-only AI provider onboarding for NVIDIA NIM, Google Gemini, OpenAI and Anthropic Claude.
- Invoice branding controls for logo, accent colour, PDF typography, legal IDs, footer, terms and watermark.
- Global Product guide and Getting Started tour inside the application.
- RBAC role-sync and login rate-limit hardening; verified release artifacts and security report published.

---

## [1.0.0] — 2026-08-16

### Initial release

#### CRM & Sales
- 360° customer profiles with full interaction history
- Lead capture, scoring, and lifecycle tracking
- Sales pipeline with drag-and-drop deal stages
- Quotation builder with multi-level approval workflows
- Sales order management and fulfillment tracking
- Email integration for automatic activity logging

#### Procurement
- Purchase order creation with configurable approval chains
- Vendor directory with supplier scorecards
- Goods receipt and purchase invoice matching
- Purchase return workflows

#### Inventory
- Multi-warehouse stock management
- Real-time inventory levels with low-stock alerts
- Batch and serial number tracking
- Reorder point configuration and automatic purchase suggestions
- Barcode scanning support
- Stock transfer between warehouses
- Inventory valuation (FIFO, weighted average)

#### Logistics & Warehousing
- Shipment tracking with carrier integration
- Warehouse zone and bin management
- Goods receipt and dispatch workflows
- Returns and reverse logistics handling
- Delivery note and packing slip generation

#### Finance & Accounting
- Configurable chart of accounts
- Accounts payable and accounts receivable
- Multi-currency support with exchange rate management
- Bank reconciliation and payment processing
- GST/VAT tax calculation and compliance
- Profit & loss statement, balance sheet, and cash flow reports
- Budget tracking and variance analysis
- Journal entries with multi-line support

#### Artificial Intelligence
- AI-powered demand forecasting
- Smart customer segmentation and churn prediction
- Anomaly detection in financial transactions
- Natural language search across all modules
- AI-generated business insights dashboard
- Document parsing and data extraction from invoices and receipts

#### Approvals & Workflows
- Multi-level approval chains with conditional routing
- Role-based and amount-based workflow triggers
- Email and in-app notification engine
- Full audit trail for all approval actions
- Configurable escalation rules

#### Administration
- Role-based access control (RBAC) with granular permissions
- Multi-company support with consolidated reporting
- Custom fields on all core entities
- Activity logs and compliance reporting
- Data import and export (CSV, Excel)
- RESTful API with authentication and rate limiting
- LDAP and SSO integration

#### Reporting & Analytics
- Real-time dashboards with configurable KPI widgets
- Drag-and-drop report builder
- Scheduled email report delivery
- Export reports to PDF, Excel, and CSV
- Cross-module analytics and drill-down views

#### General
- Modern responsive UI (desktop and tablet)
- Dark mode and light mode themes
- Multi-language support (English, Hindi)
- Comprehensive documentation (user, admin, API, developer, deployment)
- Docker Compose production deployment
- Database migration and seed scripts
- Demo dataset with sample company ("Jain Metal") and transactions

---

> Historical release notes are preserved for provenance. Security verification and final v1.3.1 counts are published in `SECURITY-AUDIT-REPORT.md`.
