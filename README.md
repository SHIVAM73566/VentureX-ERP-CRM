# VentureX ERP & CRM - AI-Powered CRM & ERP Business Operating System

![VentureX ERP & CRM](https://img.shields.io/badge/Version-1.0.0-blue) ![Laravel](https://img.shields.io/badge/Laravel-13-red) ![PHP](https://img.shields.io/badge/PHP-8.3+-purple) ![License](https://img.shields.io/badge/License-Proprietary-green)

A complete, production-ready AI-powered CRM and ERP business operating system built with Laravel 13, Blade, Alpine.js, and Tailwind CSS.

## ðŸŽ¯ Features

### Core Modules
- **CRM** â€” Customer management, pipeline, activities, portal
- **Sales** â€” Quotations, orders, invoices, payments
- **Inventory** â€” Products, warehouses, stock management
- **Procurement** â€” Purchase orders, RFQs, suppliers
- **Finance** â€” Chart of accounts, journals, AR/AP
- **Logistics** â€” Shipments, containers, tracking
- **HR** â€” Employees, leave, payroll, performance
- **Projects** â€” Tasks, time tracking, milestones
- **Helpdesk** â€” Tickets, SLA, knowledge base

### AI Features
- Natural language chat assistant
- Revenue forecasting
- Customer behavior analysis
- Risk detection and alerts
- Multi-provider support (GPT-5, Claude, Gemini, DeepSeek, NVIDIA)

### Security
- 3-Step Registration (Email, Phone, Selfie)
- 2FA/MFA with TOTP
- App Lock with 6-digit PIN
- Device trust and fingerprinting
- 10-layer security architecture
- Emergency lockdown mode
- Role-Based Access Control (RBAC)
- Complete audit logging

## ðŸ“¸ Screenshots

| Dashboard | Customers | Support |
|-----------|-----------|---------|
| ![Dashboard](screenshots/Product%20demo/02-dashboard-desktop.png) | ![Customers](screenshots/Product%20demo/03-customers.png) | ![Support](screenshots/Product%20demo/01-login.png) |

| Purchase Orders | Sales Orders | Invoices |
|-----------------|--------------|----------|
| ![Purchase Orders](screenshots/Product%20demo/07-purchase-orders.png) | ![Sales Orders](screenshots/Product%20demo/08-sales-orders.png) | ![Invoices](screenshots/Product%20demo/09-invoices.png) |

| AI Quotas | Audit Log | Roles & Permissions |
|-----------|-----------|---------------------|
| ![AI Quotas](screenshots/Product%20demo/13-ai-quotas.png) | ![Audit Log](screenshots/Product%20demo/14-audit-log.png) | ![RBAC](screenshots/Product%20demo/15-rbac-roles.png) |

| Mobile Dashboard | Mobile Customers | Export Center |
|------------------|------------------|---------------|
| ![Mobile Dashboard](screenshots/Product%20demo/17-dashboard-mobile.png) | ![Mobile Customers](screenshots/Product%20demo/18-customers-mobile.png) | ![Exports](screenshots/Product%20demo/12-exports.png) |

## ðŸš€ Quick Start

### Requirements
- PHP 8.2+
- MySQL 8.0+ or MariaDB 10.6+
- Composer
- Node.js 18+

### Installation

```bash
# 1. Clone/extract the project
cd /var/www/html
unzip VentureX-ERP.zip
cd VentureX-ERP

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

### Demo Credentials

> âš ï¸ **Security Notice**: These are DEMO credentials for testing only. Change all passwords before production use.

| Role | Email | Password |
|------|-------|----------|
| Demo Admin | demo_admin@example.com | Demo_Admin_2026! |
| Demo Manager | demo_manager@example.com | Demo_Manager_2026! |
| Demo Sales | demo_sales@example.com | Demo_Sales_2026! |

Run `php artisan db:seed --class=DemoCredentialSeeder` to create demo accounts.

Open http://127.0.0.1:8000 and login with any credentials above.

## ðŸ“š Documentation

- [Installation Guide](documentation/INSTALLATION.md)
- [User Guide](documentation/USER_GUIDE.md)
- [Security Guide](documentation/SECURITY.md)
- [Deployment Guide](documentation/DEPLOYMENT.md)
- [API Documentation](documentation/API_DOCUMENTATION.md)
- [Troubleshooting](documentation/TROUBLESHOOTING.md)
- [Changelog](CHANGELOG.md)

## ðŸ—ï¸ Architecture

```
VentureX ERP & CRM/
â”œâ”€â”€ app/
â”‚   â”œâ”€â”€ Http/Controllers/    # 62+ Controllers
â”‚   â”œâ”€â”€ Models/              # 64+ Eloquent Models
â”‚   â”œâ”€â”€ Services/            # 32+ Business Services
â”‚   â””â”€â”€ Policies/            # 32 Authorization Policies
â”œâ”€â”€ database/migrations/     # 36 Database Migrations
â”œâ”€â”€ resources/views/         # 125 Blade Views
â”œâ”€â”€ routes/                  # 220+ Routes
â”œâ”€â”€ config/                  # 22 Configuration Files
â””â”€â”€ documentation/           # Complete Documentation
```

## ðŸ”’ Security

VentureX ERP & CRM includes a comprehensive 10-layer security architecture:

1. **HTTPS Enforcement** â€” Force SSL/TLS connections
2. **Security Headers** â€” CSP, HSTS, X-Frame-Options
3. **Rate Limiting** â€” API and form submission throttling
4. **Brute Force Protection** â€” Login attempt limiting
5. **Multi-Factor Authentication** â€” TOTP-based 2FA
6. **Role-Based Access Control** â€” Granular permissions
7. **Emergency Lockdown** â€” System-wide security mode
8. **Input Validation** â€” Server-side sanitization
9. **File Upload Security** â€” Malware scanning
10. **Audit Logging** â€” Complete activity tracking

## ðŸ“Š Statistics

- **250+** Features
- **125** Blade Views
- **64+** Eloquent Models
- **62+** Controllers
- **36** Database Migrations
- **32+** Services
- **32** Policies
- **220+** Routes
- **22** Configuration Files
- **22** Documentation Files

## ðŸ› ï¸ Tech Stack

- **Backend:** Laravel 13, PHP 8.3+
- **Frontend:** Blade, Alpine.js 3, Tailwind CSS v4
- **Database:** MySQL 8.0+, MariaDB 10.6+
- **Build:** Vite 8.x
- **Server:** Apache 2.4+, Nginx 1.24+

## ðŸ“„ License

This project is licensed under a Commercial License â€” see the [LICENSE](LICENSE) file for details.

## ðŸ“ž Support

- ðŸ“§ Email: support@venturexerp.com
- ðŸ“– Documentation: /documentation/
- ðŸ› Issues: GitHub Issues

## â­ Star Us

If you find VentureX ERP & CRM useful, please give us a star on GitHub!

---

**VentureX ERP & CRM** â€” Built with â¤ï¸ for modern businesses.
