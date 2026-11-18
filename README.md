# VentureX ERP & CRM

**The AI-native business operating system for Laravel.**

![Version](https://img.shields.io/badge/Version-1.3.1-blue)
![Laravel](https://img.shields.io/badge/Laravel-13-red)
![PHP](https://img.shields.io/badge/PHP-8.4-purple)
![Tests](https://img.shields.io/badge/Tests-484%2B%20passed-brightgreen)
![RAG](https://img.shields.io/badge/RAG-84%20pipelines-orange)
![License](https://img.shields.io/badge/License-MIT-green)

VentureX ERP & CRM is a self-hosted Laravel 13 application that brings CRM, sales, inventory, procurement, finance, logistics, HR, support, and 84 source-cited enterprise RAG pipelines into one auditable workspace. A lead becomes a customer, a quotation becomes an order, stock moves, an invoice is raised, payment is recorded, and finance sees the result without re-typing anything.

- **Version:** v1.3.1
- **License:** MIT — see [LICENSE](LICENSE)
- **Maintainer:** Shivam Chaturvedi (VentureAI Pro)
- **PayPal checkout / sales enquiries:** chaturvedishivam179@gmail.com
- **Verification:** [SECURITY-AUDIT-REPORT.md](SECURITY-AUDIT-REPORT.md) · [release/SHA256SUMS.txt](release/SHA256SUMS.txt)

> **Test badge:** the `Tests-484+ passed` badge is a placeholder carried forward from the previous verified build. Final v1.3.1 counts will be published in `SECURITY-AUDIT-REPORT.md` after central integration; do not treat the badge as a new claim.

---

## Feature matrix

| Area | What is included |
|---|---|
| CRM | Customers, contacts, leads, opportunities, activities, pipeline, scoring and 360° history |
| Sales | Quotations, sales orders, invoices, payments, approvals and receivables |
| Inventory | Products, warehouses, stock movements, reorder alerts, transfers and valuation |
| Procurement | Suppliers, supplier offers, requisitions, RFQs, purchase orders and landed costs |
| Finance | Chart of accounts, journals, AR/AP, multi-currency, GST/VAT, P&L, balance sheet and cash flow |
| Logistics | Shipments, containers, dispatch, delivery notes and landed-cost audit |
| HR | Employees, attendance, leave, payroll, performance reviews and onboarding |
| Support | Tickets, help centre, FAQ and AI support assistant |
| AI / RAG | Local-first assistant, Copilot, document reader, 84 enterprise pipelines with sources/confidence/latency |
| Automation | AERO X Agentic Engine with auditable, transactional rules and rollback on failure |
| Security | TOTP MFA, RBAC, rate limiting, app lock, emergency lockdown, audit logs and encrypted AI keys |
| Administration | Users, roles, branding, import/export, audit logs, backups and one-time web installer |
| Deployment | Local PHP, Docker Compose, VPS/Ubuntu, cPanel shared hosting, WordPress subdomain and GitHub Action |

---

## One-minute setup

All guides start from the v1.3.1 source artifact and use PHP 8.4, Composer 2, Node.js 20+, MySQL 8.0+ (or MariaDB 10.6+). Set the web root to the project's `public/` directory. Never expose the project root directly.

### Local — Windows or Mac

```bash
unzip VentureX-ERP-CRM-v1.3.1-source.zip
cd VentureX-ERP-CRM
composer install
cp .env.example .env
php artisan key:generate
npm install && npm run build
php artisan serve
```

Open <http://localhost:8000/install>, choose **Clean Production Mode** (recommended), create the first administrator and finish the installer. For a throwaway local preview only, choose **Demo Preview Mode** and use the LOCAL DEMO credentials below. Preview URLs are limited to `localhost` and `127.0.0.1`.

### Docker (bundled Compose file)

```bash
unzip VentureX-ERP-CRM-v1.3.1-source.zip
cd VentureX-ERP-CRM
cp .env.example .env
docker compose -f docker/docker-compose.yml --project-directory . up -d --build
docker compose -f docker/docker-compose.yml --project-directory . exec app php artisan key:generate
docker compose -f docker/docker-compose.yml --project-directory . exec app php artisan migrate --force
```

Open <http://localhost/install> and complete the one-time installer. The bundled stack includes Nginx, PHP-FPM, MySQL 8, Redis and a queue worker. See [docker/README.md](docker/README.md) and [INSTALLATION.md](INSTALLATION.md).

### VPS / Ubuntu 22.04 or 24.04

```bash
# Install PHP 8.4 + extensions, MySQL 8, Nginx, Composer and Node.js 20+ first
sudo mkdir -p /var/www/venturex
cd /var/www/venturex
# Upload and extract VentureX-ERP-CRM-v1.3.1-source.zip here
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
npm ci && npm run build
sudo chown -R www-data:www-data storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 775 {} +
sudo find storage bootstrap/cache -type f -exec chmod 664 {} +
sudo ln -s /var/www/venturex/public /etc/nginx/venturex-public  # document root must be /public
```

Point the Nginx server block at `/var/www/venturex/public`, add the scheduler cron and queue worker, enable HTTPS, then open `https://your-domain/install`. Full commands are in [INSTALLATION.md](INSTALLATION.md) and [documentation/VPS.md](documentation/VPS.md).

### cPanel shared hosting

1. In **cPanel → Select PHP Version**, choose PHP 8.4 and enable `pdo_mysql`, `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `gd`, `intl`, `fileinfo` and `opcache`.
2. In **cPanel → MySQL Databases**, create a database and a user with **All Privileges**; note the prefixed names for `.env`.
3. Upload the extracted v1.3.1 files beside `public_html`, not inside it.
4. Set the addon domain/subdomain **document root to `/public`** (for example `/home/user/venturex/public`). If the host will not allow this, use a subdomain with a custom document root or choose a VPS.
5. Set directory permissions to `775` and files to `664`, then open `https://your-domain/install`.

Friendly details and fallbacks are in [INSTALLATION.md](INSTALLATION.md) and [documentation/CPANEL.md](documentation/CPANEL.md).

### WordPress subdomain

1. Keep WordPress on the main domain and create a subdomain such as `erp.example.com`.
2. Point that subdomain's document root to the ERP project's `public/` directory (not WordPress and not the project root).
3. Install VentureX through `/install` over HTTPS, then create a Sanctum API token in the ERP.
4. Copy `integrations/wordpress/venturex-erp-connector` into WordPress `wp-content/plugins`, activate it, and set the API URL to `https://erp.example.com/api` plus the token. Do not put the token in public JavaScript.

The connector README documents the supported endpoints and HTTPS requirements. Integration guides: [documentation/WIX.md](documentation/WIX.md) and [documentation/CUSTOM-INTEGRATION.md](documentation/CUSTOM-INTEGRATION.md).

---

## Installer modes and local demo credentials

`/install` is a one-time setup wizard. It checks requirements, configures the database and `.env`, creates the first administrator, runs migrations with `--force`, and writes `storage/.installed`. After setup, the installer is disabled.

| Mode | Use it for | Data created |
|---|---|---|
| **Clean Production Mode** (recommended) | Real deployments, client installs, VPS, cPanel, Docker production | Schema, required roles/permissions, settings and the administrator you specify. **No demo users and no sample business records.** |
| **Demo Preview Mode** | Throwaway local evaluation only | Optional sample records and the LOCAL DEMO sign-ins below. Never use on a public or shared server. |

### LOCAL DEMO ONLY — do not use in production

| Role | Email | Password |
|---|---|---|
| Super Admin | `demo_admin@example.com` | `Demo_Admin_2026!` |
| CEO | `demo_manager@example.com` | `Demo_Manager_2026!` |
| Sales Manager | `demo_sales@example.com` | `Demo_Sales_2026!` |

These accounts exist only when Demo Preview Mode or local demo seeders are deliberately enabled. Change or remove them before exposing the application. Preview URLs are local only: <http://localhost:8000/login> and <http://127.0.0.1:8000/login>.

For a manual clean install, use `php artisan migrate --force` and optional non-demo seeders. Do **not** run `migrate --seed` in production: the legacy seed command includes demo data. See [INSTALLATION.md](INSTALLATION.md).

---

## Screenshots

| Dashboard | Customers | Login |
|---|---|---|
| ![Dashboard](screenshots/Product%20demo/Dashboard%20of%20desktop.png) | ![Customers](screenshots/Product%20demo/customer%20dashboard.png) | ![Login](screenshots/Product%20demo/LOGIN%20DAHBOARD.png) |

| Purchase Orders | Sales Orders | Invoices |
|---|---|---|
| ![Purchase Orders](screenshots/Product%20demo/Purchase%20Orders.png) | ![Sales Orders](screenshots/Product%20demo/Sales%20Orders.png) | ![Invoices](screenshots/Product%20demo/Invoices.png) |

| Leads | Opportunities | Pipeline |
|---|---|---|
| ![Leads](screenshots/Product%20demo/LEADS.png) | ![Opportunities](screenshots/Product%20demo/Opportunities.png) | ![Pipeline](screenshots/Product%20demo/Opportunity%20Pipeline.png) |

| Import Data | Export Center | Finance Dashboard |
|---|---|---|
| ![Import Data](screenshots/Product%20demo/Import%20Data.png) | ![Export Center](screenshots/Product%20demo/Export%20Center.png) | ![Finance Dashboard](screenshots/Product%20demo/Finance%20Dashboard.png) |

---

## Documentation

- [Installation Guide](INSTALLATION.md)
- [Getting Started](documentation/GETTING-STARTED.md)
- [Product Guide](documentation/PRODUCT-GUIDE.md)
- [84-Pipeline RAG Guide](documentation/RAG-GUIDE.md)
- [AI API Keys — Secure Setup](documentation/AI-API-KEYS.md)
- [User Guide](documentation/USER_GUIDE.md)
- [Administrator Guide](documentation/ADMIN_GUIDE.md)
- [Security Guide](documentation/SECURITY.md)
- [Deployment Guide](documentation/DEPLOYMENT.md)
- [cPanel Guide](documentation/CPANEL.md)
- [VPS Guide](documentation/VPS.md)
- [API Setup](documentation/API-SETUP.md)
- [API Documentation](documentation/API_DOCUMENTATION.md)
- [Troubleshooting](documentation/TROUBLESHOOTING.md)
- [Changelog](CHANGELOG.md)
- [Security & Enterprise Verification Report](SECURITY-AUDIT-REPORT.md)
- [Release checksums](release/SHA256SUMS.txt)

## Releases and verification

- Current release target: **v1.3.1**
- Prior verified artifacts: [release/SHA256SUMS.txt](release/SHA256SUMS.txt)
- [GitHub deploy action](https://github.com/SHIVAM73566/venturex-deploy-action) — GitHub Marketplace approval pending.
- The root `LICENSE` is MIT. Third-party components keep their own licenses; see [documentation/THIRD_PARTY_LICENSES.md](documentation/THIRD_PARTY_LICENSES.md).

## Support and attribution

- Maintainer: **Shivam Chaturvedi** — chaturvedishivam179@gmail.com
- PayPal checkout / sales: **chaturvedishivam179@gmail.com**
- Documentation: `/documentation/`
- Issues: <https://github.com/SHIVAM73566/VentureX-ERP-CRM/issues>

---

**VentureX ERP & CRM v1.3.1** — built for modern businesses. Copyright © 2026 Shivam Chaturvedi. Licensed under MIT.
