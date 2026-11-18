# VentureX ERP & CRM Documentation

Welcome to the v1.3.1 documentation set. These guides cover installation, deployment, configuration, security, APIs and day-to-day use of VentureX ERP & CRM.

> The root [`INSTALLATION.md`](../INSTALLATION.md) is the canonical installation guide. This folder keeps the same guide for readers browsing `/documentation/`.

## 📚 Documentation files

| Document | Description |
|---|---|
| [INSTALLATION.md](INSTALLATION.md) | Canonical installation guide: local, Docker, VPS, cPanel and WordPress subdomain |
| [GETTING-STARTED.md](GETTING-STARTED.md) | First-login tour and common workflows |
| [USER_GUIDE.md](USER_GUIDE.md) | Complete user manual |
| [PRODUCT-GUIDE.md](PRODUCT-GUIDE.md) | In-app product and RAG guide |
| [ADMIN_GUIDE.md](ADMIN_GUIDE.md) | Administration reference |
| [API_DOCUMENTATION.md](API_DOCUMENTATION.md) | REST API reference |
| [API-SETUP.md](API-SETUP.md) | API and AI key setup |
| [DEPLOYMENT.md](DEPLOYMENT.md) | Production deployment guide |
| [VPS.md](VPS.md) | Ubuntu 22.04/24.04 VPS deployment |
| [CPANEL.md](CPANEL.md) | cPanel shared-hosting deployment |
| [CLOUD.md](CLOUD.md) | Generic cloud deployment |
| [SECURITY.md](SECURITY.md) | Security features and configuration |
| [TROUBLESHOOTING.md](TROUBLESHOOTING.md) | Common issues and solutions |
| [DATABASE-SETUP.md](DATABASE-SETUP.md) | Database configuration and management |
| [BACKUP_AND_RESTORE.md](BACKUP_AND_RESTORE.md) | Backup and disaster recovery |
| [AI-SETUP.md](AI-SETUP.md) | AI gateway and provider setup |
| [AI-API-KEYS.md](AI-API-KEYS.md) | Secure API key handling |
| [RAG-GUIDE.md](RAG-GUIDE.md) | 84 enterprise RAG pipelines |
| [WIX.md](WIX.md) | Wix integration |
| [BLOGGER.md](BLOGGER.md) | Blogger integration |
| [CUSTOM-INTEGRATION.md](CUSTOM-INTEGRATION.md) | Custom website integration |
| [THIRD_PARTY_LICENSES.md](THIRD_PARTY_LICENSES.md) | Third-party dependency licenses |
| [LICENSES-AND-THIRD-PARTY.md](LICENSES-AND-THIRD-PARTY.md) | License inventory |

## 🚀 Quick installation

```bash
unzip VentureX-ERP-CRM-v1.3.1-source.zip
cd VentureX-ERP-CRM
composer install
cp .env.example .env
php artisan key:generate
npm install && npm run build
php artisan serve
```

Open <http://127.0.0.1:8000/install>, choose **Clean Production Mode** and create the first administrator. For a throwaway local preview only, choose **Demo Preview Mode**.

> **LOCAL DEMO ONLY:** demo accounts (`demo_admin@example.com`, `demo_manager@example.com`, `demo_sales@example.com`) must never be used on a public or shared server. See [`INSTALLATION.md`](INSTALLATION.md) for details.

For production, use `/install` or `php artisan migrate --force`. Do not run `php artisan migrate --seed` in production because legacy seeders can add demo data.

## 📖 Module guides

- **CRM:** customers, contacts, leads, opportunities, activities and pipeline
- **Sales:** quotations, orders, invoices, payments and receivables
- **Inventory:** products, warehouses, stock movements and reorder alerts
- **Procurement:** suppliers, offers, requisitions, RFQs and purchase orders
- **Finance:** chart of accounts, journals, AR/AP and reporting
- **Logistics:** shipments, containers and landed costs
- **HR:** employees, attendance, leave, payroll, reviews and onboarding
- **Support:** tickets, FAQ, help centre and AI assistant

## 🔐 Security

- TOTP multi-factor authentication
- App lock and device trust
- Role-based access control with granular permissions
- Complete audit logging
- Emergency lockdown mode
- Rate limiting and brute-force protection
- Encrypted AI credentials and red-team tested controls

See [SECURITY.md](SECURITY.md) and the root [SECURITY-AUDIT-REPORT.md](../SECURITY-AUDIT-REPORT.md).

## 🤖 AI features

- Local-first assistant that works without API keys
- Key-only onboarding for NVIDIA NIM, Google Gemini, OpenAI and Anthropic Claude
- 84 enterprise RAG pipelines with sources, confidence and latency
- AERO X Agentic Engine with auditable, transactional rules

See [AI-API-KEYS.md](AI-API-KEYS.md) and [RAG-GUIDE.md](RAG-GUIDE.md).

## 🆘 Support

- **Email / PayPal checkout:** chaturvedishivam179@gmail.com
- **Documentation:** this folder and the root `README.md`
- **Issues:** <https://github.com/SHIVAM73566/VentureX-ERP-CRM/issues>
- **Maintainer:** Shivam Chaturvedi

## License

VentureX ERP & CRM is licensed under the **MIT License** — see the root [LICENSE](../LICENSE). Third-party dependencies retain their own licenses; see [THIRD_PARTY_LICENSES.md](THIRD_PARTY_LICENSES.md).
