# VentureX ERP & CRM - AI-Powered Business Management Suite

![Version](https://img.shields.io/badge/Version-1.0.0-blue) ![Laravel](https://img.shields.io/badge/Laravel-13-red) ![PHP](https://img.shields.io/badge/PHP-8.3+-purple) ![License](https://img.shields.io/badge/License-Proprietary-green)

A complete, production-ready AI-powered CRM and ERP business operating system built with Laravel 13, Blade, Alpine.js, and Tailwind CSS.

## Purchase Full Source Code

Get the complete source code with documentation, video tutorials, and support:

[![Gumroad](https://img.shields.io/badge/Download-%2459-Gumroad-orange)](https://gumroad.com/l/venturex-erp)
[![Payhip](https://img.shields.io/badge/Download-%2459-Payhip-blue)](https://payhip.com/b/venturex-erp)

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
- PHP 8.3+
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

- **Backend:** Laravel 13, PHP 8.3+
- **Frontend:** Blade, Alpine.js 3, Tailwind CSS v4
- **Database:** MySQL 8.0+, MariaDB 10.6+
- **Build:** Vite 8.x
- **Server:** Apache 2.4+, Nginx 1.24+
- **Payment:** Payoneer (manual), PayPal
- **AI:** NVIDIA Nemotron, RapidAPI, Local Intelligence

## License

This project is licensed under a Commercial License - see the [LICENSE](LICENSE) file for details.

## Support

- Email: support@venturexerp.com
- Documentation: /documentation/
- Issues: GitHub Issues

## Sponsor This Project

If you find VentureX ERP & CRM useful, consider supporting the project:

[![Sponsor](https://img.shields.io/badge/Sponsor-GitHub-red)](https://github.com/sponsors/SHIVAM73566)
[![Buy Me A Coffee](https://img.shields.io/badge/Buy%20Me%20A%20Coffee-yellow)](https://buymeacoffee.com/venturexerp)

---

**VentureX ERP & CRM** - Built for modern businesses.
