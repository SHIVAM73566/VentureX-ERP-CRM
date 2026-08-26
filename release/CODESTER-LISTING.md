# Codester Listing — VentureX ERP & CRM

---

## Name

**VentureX ERP & CRM — Universal Business Operating System**

---

## Short Description

Full-stack Laravel ERP with CRM, invoicing, inventory, procurement, finance, AI copilot, multi-tenant security, REST API, Docker, and 17 screenshots. 339 tests passed. Ready to deploy.

---

## Description

**VentureX ERP & CRM** is a complete, production-ready business operating system built on Laravel 13 with PHP 8.4. It combines CRM, sales, invoicing, inventory, procurement, logistics, finance, AI copilot, and support ticketing into a single unified platform — designed for agencies, trading companies, wholesalers, distributors, and growing businesses.

Unlike template-based admin panels, VentureX is a **fully functional ERP** with real business logic: double-entry accounting, automated journal entries, multi-warehouse stock tracking, lead-to-invoice sales pipeline, supplier RFQ workflows, and AI-powered business insights.

### Why VentureX Stands Out

- **623 PHP files, 169 Blade templates, 74 Eloquent models, 92 controllers** — not a starter kit, a complete system
- **422 API routes** with Sanctum auth, company-scoped multi-tenant isolation, and per-company data security
- **Multi-tenant architecture** — every record is scoped to a company. Sell to multiple businesses on one installation
- **AI Copilot** — built-in conversational AI assistant, document reader, procurement advisor, and support assistant
- **REST API** — full CRUD API for every module (customers, leads, products, invoices, payments, journal entries, etc.)
- **Docker-ready** — production Dockerfile, docker-compose, nginx config, MySQL init scripts included
- **17 product screenshots** and **15 video tutorial scripts** with subtitles included
- **339 PHPUnit tests, 464 assertions, 0 failures** — verified code quality
- **WordPress integration plugin** — connect WordPress sites to your VentureX ERP via REST API
- **Installer wizard** — browser-based setup for non-technical users (database, admin, seed data)

### Modules Included

| Module | What It Does |
|--------|-------------|
| **Dashboard** | Revenue, leads, invoices, tickets — real-time KPIs |
| **CRM** | Customers, contacts, companies, leads kanban, opportunities pipeline |
| **Sales** | Quotations → Sales Orders → Invoices → Payments (full pipeline) |
| **Inventory** | Products, warehouses, stock movements, low-stock alerts, multi-warehouse |
| **Procurement** | Suppliers, RFQs, purchase orders, supplier offers with quality scoring |
| **Logistics** | Shipments, containers, landed costs |
| **Finance** | Chart of accounts, journal entries, receivables, payables, balance sheet |
| **Support** | Ticketing system, error reports, customer satisfaction tracking |
| **AI Center** | Copilot, document reader, procurement advisor, usage quotas |
| **Admin** | Roles & permissions, audit logs, security events, imports/exports, companies |
| **Settings** | System configuration, user management, MFA, trusted devices |
| **Auth** | Registration wizard, MFA setup, lock screen, password reset, session management |

---

## Features

- Full ERP suite: CRM, Sales, Inventory, Procurement, Logistics, Finance, Support
- Multi-tenant company isolation with company_id scoping on all 88 API routes
- AI Copilot with conversation history, document reader, procurement advisor
- RESTful API with Sanctum token auth for every module
- Double-entry accounting with automated journal entries
- Lead-to-invoice sales pipeline (Leads → Quotations → Orders → Invoices → Payments)
- Multi-warehouse inventory with stock movements and transfer tracking
- Supplier RFQ workflow with quality status scoring (GREEN/YELLOW/RED)
- Quotation and invoice PDF generation
- Role-based access control with granular permissions
- MFA (TOTP), trusted devices, login attempt tracking, security event logging
- Data import/export with CSV support
- Browser-based installer wizard
- Docker production deployment (Dockerfile, docker-compose, nginx, supervisord)
- WordPress plugin integration (shortcode, widget, REST proxy)
- Dark mode support across all views
- 17 product screenshots included
- 15 video tutorial scripts with SRT subtitles
- 339 PHPUnit tests (464 assertions)
- Comprehensive documentation (installation, admin guide, user guide, API docs, FAQ, troubleshooting)

---

## Instructions

### Quick Start (5 minutes)

1. **Download** and extract the ZIP file to your web server
2. **Create a MySQL 8.0+ database** for the application
3. **Copy** `.env.example` to `.env` and configure:
   - `DB_CONNECTION=mysql`
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=your_database`
   - `DB_USERNAME=your_username`
   - `DB_PASSWORD=your_password`
4. **Install dependencies:**
   ```bash
   composer install --no-dev
   npm install && npm run build
   ```
5. **Generate app key:**
   ```bash
   php artisan key:generate
   ```
6. **Run migrations and seed demo data:**
   ```bash
   php artisan migrate --seed
   ```
7. **Start the server:**
   ```bash
   php artisan serve
   ```
8. **Open** `http://localhost:8000` in your browser
9. **Login** with: `admin@venturex.demo` / `password`

### Docker Deployment

```bash
docker-compose up -d
```

### cPanel / Shared Hosting

1. Upload files via File Manager or FTP
2. Create MySQL database in cPanel
3. Update `.env` with database credentials
4. Point document root to the `public/` folder
5. Run `php artisan migrate --seed` via SSH or terminal

---

## Requirements

| Requirement | Version |
|-------------|---------|
| **PHP** | 8.4 or higher |
| **Laravel** | 13.x |
| **MySQL** | 8.0+ (or MariaDB 10.6+) |
| **Node.js** | 18+ (for building assets) |
| **Composer** | 2.x |

### PHP Extensions Required
- mbstring, xml, bcmath, pdo_mysql, tokenizer, json, fileinfo, gd, curl, zip, openssl

### Server Requirements
- Apache with mod_rewrite or Nginx
- 512MB+ RAM recommended
- 2GB+ disk space

---

## Category & Attributes

### Category

**PHP Scripts → CMS → ERP / CRM**

---

### Files Included

| File Type | Count |
|-----------|-------|
| PHP Files | 623 |
| Blade Templates | 169 |
| JavaScript Files | 4 |
| Database Migrations | 40 |
| Eloquent Models | 74 |
| Controllers | 92 |
| Product Screenshots | 17 |
| Documentation Files | 20+ |
| Video Tutorial Scripts | 15 |
| Subtitle Files (SRT) | 15 |

---

### Software Version

| Component | Version |
|-----------|---------|
| **PHP Version** | 8.4 |
| **Laravel Version** | 13.x |
| **PHPUnit** | 12.x |

---

### Software Framework

**Laravel 13**

---

### Database

**MySQL 8.0+** (also supports MariaDB 10.6+, SQLite for testing)

---

### HTML/CSS Framework

**Bootstrap** (Tailwind CSS used in Blade components)

---

### JavaScript Framework

**Node.js** (Vite build tool, vanilla JavaScript for frontend interactions)

---

## Top 20 High-Ranking Tags for Codester

```
erp
crm
laravel erp
laravel crm
erp system
business management
invoicing
inventory management
accounting
multi-tenant
saas
laravel 13
php erp
sales pipeline
procurement
invoice generator
customer management
warehouse management
ai copilot
docker
```

---

## Message to Reviewer

```
Hi Codester Team,

VentureX ERP & CRM is a production-ready Laravel 13 ERP system with 623 PHP files,
169 Blade templates, 74 models, and 339 passing tests (464 assertions, 0 failures).

WHAT'S INCLUDED:
- Complete ERP: CRM, Sales, Inventory, Procurement, Logistics, Finance, Support
- REST API with Sanctum auth (422 routes, all company-scoped for multi-tenant security)
- AI Copilot with conversation history and document reader
- Docker production deployment (Dockerfile, docker-compose, nginx)
- WordPress integration plugin
- 17 product screenshots
- 15 video tutorial scripts with subtitles
- Browser-based installer wizard
- Comprehensive documentation (installation, admin, user guide, API, FAQ)

SECURITY:
- All 88 API endpoints require authentication
- Company-scoped data isolation (every query uses ofCompany() scope)
- MFA, trusted devices, security event logging
- No hardcoded secrets, no debug code

DEMO:
- Live interactive demo: https://venturexerp.blogspot.com/
- GitHub repository: https://github.com/SHIVAM73566/VentureX-ERP-CRM
- Live demo URL: https://SHIVAM73566.github.io/VentureX-ERP-CRM/

The codebase is clean, tested, documented, and ready for immediate deployment.
PHP 8.4, Laravel 13, MySQL 8.0+, Node.js 18+.

Thank you for your review!

Shivam Chaturvedi
chaturvedishivam179@gmail.com
```
