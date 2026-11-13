# VentureX ERP & CRM - AI-Powered Business Management Suite

![Version](https://img.shields.io/badge/Version-1.0.0-blue) ![Laravel](https://img.shields.io/badge/Laravel-13-red) ![PHP](https://img.shields.io/badge/PHP-8.4+-purple) ![License](https://img.shields.io/badge/License-Proprietary-green)

A complete, production-ready AI-powered CRM and ERP business operating system built with Laravel 13, Blade, Alpine.js, and Tailwind CSS.

## Introduction

VentureX ERP & CRM gives a company one shared workspace for the entire commercial cycle: a lead becomes a customer, a customer becomes a quotation, a quotation becomes an order, an order moves stock, an invoice becomes a payment, and finance sees the result without re-typing anything. Purchasing, warehouses, shipments and landed costs live in the same system, and an AI layer answers questions from your live records with sources and confidence scores.

It is designed for teams of 5–500 people who have outgrown spreadsheets, shared inboxes and disconnected invoicing tools but do not want a six-month enterprise rollout.

**Who it is for**

- Wholesale distributors and trading companies
- Manufacturers managing stock, purchasing and sales
- Service agencies running a pipeline and milestone invoicing
- Import/export businesses that need true landed cost and margin
- Owners and finance teams who want one dashboard instead of ten reports

## How to use it (quick examples)

> The same walkthrough is available inside the app at **Getting Started** in the sidebar, with copyable examples and a saved checklist.

### 1. Sell something — lead to payment
1. Add a **Customer** (Customers → New).
2. Create a **Quotation** with 10 units of a product (Sales → Quotations).
3. Mark the deal won and convert it to a **Sales Order**.
4. Raise an **Invoice** from the order.
5. Record the **Payment** — receivables update automatically.

*Example:* 10 × Bluetooth Headset @ ₹2,499 → quotation → order → `INV-2026-0042` → paid by UPI → balance ₹0.

### 2. Buy and receive stock — requisition to shelf
1. Compare **Supplier Offers** and pick a supplier.
2. Raise a **Purchase Requisition**, approve it, and generate a **Purchase Order**.
3. Receive the goods against the PO — stock increases in the chosen warehouse.
4. Allocate freight and duty in **Landed Costs** to see true cost per unit.

*Example:* `PR-2026-0031` → `PO-2026-0114` → 200 units received → landed cost ₹1,890/unit.

### 3. Understand the numbers
- **Finance → Dashboard** for revenue, receivables and payables.
- **Finance → Receivables / Payables** for what is owed and overdue.
- **Inventory → Stock** for reorder alerts and warehouse balances.
- **Admin → Audit Logs** for a full trail of who changed what.

### 4. Ask AI about your own data
Open **AI Center → AI Assistant** (or **Enterprise RAG** for sourced answers) and ask:

- “Which customers have overdue invoices this month?”
- “Show products below reorder level in each warehouse.”
- “Summarise procurement spend by supplier this quarter.”
- “What is the landed cost and margin of container CONT-2026-014?”

Every answer is generated from your records, and RAG answers include the source documents, confidence and latency.

### 5. Bring your existing data
Use **Admin → Import Data** for CSV imports and **Admin → Export Center** for controlled exports. Roles and permissions decide who can import, export or approve.

## Features

### Core Modules
- **CRM** - Customer management, pipeline, activities, contacts
- **Sales** - Quotations, orders, invoices, payments
- **Inventory** - Products, warehouses, stock management
- **Procurement** - Purchase orders, RFQs, suppliers, supplier offers
- **Finance** - Chart of accounts, journals, AR/AP, dashboards
- **Logistics** - Shipments, containers, landed costs
- **Support** - Help center, tickets, documentation, FAQ
- **Admin** - Users, roles, settings, import/export, audit logs

### AI Features (Work Without API Keys)
- Natural language chat assistant
- AI Copilot with context-aware suggestions
- AI Support Assistant with built-in knowledge base
- AI Document Reader for file analysis
- AI Procurement intelligence
- **80 enterprise RAG pipelines** across CRM, procurement, inventory, finance, logistics and executive domains, with sources, confidence and latency
- Rule-based business insights
- Multi-provider support (NVIDIA, RapidAPI, local fallback)

### Security
- Multi-Factor Authentication (TOTP)
- Role-Based Access Control (RBAC)
- 10-layer security architecture
- Emergency lockdown mode
- Complete audit logging
- Rate limiting and brute force protection

## Screenshots

| Dashboard | Customers | Login |
|-----------|-----------|-------|
| ![Dashboard](screenshots/Product%20demo/Dashboard%20of%20desktop.png) | ![Customers](screenshots/Product%20demo/customer%20dashboard.png) | ![Login](screenshots/Product%20demo/LOGIN%20DAHBOARD.png) |

| Purchase Orders | Sales Orders | Invoices |
|-----------------|--------------|----------|
| ![Purchase Orders](screenshots/Product%20demo/Purchase%20Orders.png) | ![Sales Orders](screenshots/Product%20demo/Sales%20Orders.png) | ![Invoices](screenshots/Product%20demo/Invoices.png) |

| Leads | Opportunities | Pipeline |
|-------|---------------|----------|
| ![Leads](screenshots/Product%20demo/LEADS.png) | ![Opportunities](screenshots/Product%20demo/Opportunities.png) | ![Pipeline](screenshots/Product%20demo/Opportunity%20Pipeline.png) |

| AI Quotas | Audit Logs | Roles |
|-----------|------------|-------|
| ![AI Quotas](screenshots/Product%20demo/AI%20Quotas.png) | ![Audit Logs](screenshots/Product%20demo/Audit%20Logs.png) | ![Roles](screenshots/Product%20demo/Roles.png) |

| Import Data | Export Center | Finance Dashboard |
|-------------|---------------|-------------------|
| ![Import Data](screenshots/Product%20demo/Import%20Data.png) | ![Export Center](screenshots/Product%20demo/Export%20Center.png) | ![Finance Dashboard](screenshots/Product%20demo/Finance%20Dashboard.png) |

| AI Support | Landed Costs |
|------------|--------------|
| ![AI Support](screenshots/Product%20demo/AI%20Support%20Assistant.png) | ![Landed Costs](screenshots/Product%20demo/Landed%20Costs.png) |

## Quick Start

### Requirements
- PHP 8.4+
- MySQL 8.0+ or MariaDB 10.6+
- Composer
- Node.js 20+

### Installation

```bash
# 1. Extract the project
cd /var/www/html
unzip VentureX-ERP-CRM-v1.0.0.zip
cd VentureX-ERP-CRM

# 2. Install dependencies
composer install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# Edit .env with your database credentials

# 4. Setup database
php artisan migrate --seed

# 5. Build assets
npm install
npm run build

# 6. Start the server
php artisan serve
```

Open http://localhost:8000/install in your browser to run the 6-step installation wizard.

### Demo Credentials

> **Security Notice**: These are DEMO credentials for testing only. Change all passwords before production use.

| Role | Email | Password |
|------|-------|----------|
| Super Admin | demo_admin@example.com | Demo_Admin_2026! |
| CEO | demo_manager@example.com | Demo_Manager_2026! |
| Sales Manager | demo_sales@example.com | Demo_Sales_2026! |

## AI Configuration (Optional)

All AI features work **out-of-the-box without any API keys** using built-in local intelligence. For enhanced AI capabilities:

1. Get a free NVIDIA API key from https://build.nvidia.com
2. Add `NVIDIA_API_KEY=nvapi-xxx` to your `.env` file
3. AI features will now use NVIDIA Nemotron for intelligent responses

## Documentation

- [Getting Started & Quick Examples](documentation/GETTING-STARTED.md)
- [80-Pipeline RAG Guide](documentation/RAG-GUIDE.md)
- [Installation Guide](documentation/INSTALLATION.md)
- [User Guide](documentation/USER_GUIDE.md)
- [Admin Guide](documentation/ADMIN_GUIDE.md)
- [Security Guide](documentation/SECURITY.md)
- [Deployment Guide](documentation/DEPLOYMENT.md)
- [API Documentation](documentation/API_DOCUMENTATION.md)
- [Troubleshooting](documentation/TROUBLESHOOTING.md)
- [Changelog](CHANGELOG.md)

## Architecture

```
VentureX ERP & CRM/
  app/
    Http/Controllers/    # 62+ Controllers
    Models/              # 64+ Eloquent Models
    Services/            # 32+ Business Services
    Policies/            # 32 Authorization Policies
  database/migrations/   # 39 Database Migrations
  resources/views/       # 125+ Blade Views
  routes/                # 220+ Routes
  config/                # 22 Configuration Files
  documentation/         # Complete Documentation
  docker/                # Docker Support
```

## Tech Stack

- **Backend:** Laravel 13, PHP 8.4+
- **Frontend:** Blade, Alpine.js 3, Tailwind CSS v4
- **Database:** MySQL 8.0+, MariaDB 10.6+
- **Build:** Vite 8.x
- **Server:** Apache 2.4+, Nginx 1.24+
- **AI:** NVIDIA Nemotron, RapidAPI, Local Intelligence

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

- Email: chaturvedishivam179@gmail.com, ocoronews@gmail.com
- Documentation: /documentation/
- Issues: GitHub Issues

## Sponsor This Project

If you find VentureX ERP & CRM useful, consider supporting the project:

[![Sponsor](https://img.shields.io/badge/Sponsor-GitHub-red)](https://github.com/sponsors/SHIVAM73566)
[![Buy Me A Coffee](https://img.shields.io/badge/Buy%20Me%20A%20Coffee-yellow)](https://buymeacoffee.com/venturexerp)

---

**VentureX ERP & CRM** - Built for modern businesses.
