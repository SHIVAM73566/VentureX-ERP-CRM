# VentureX ERP & CRM Documentation

Welcome to the VentureX ERP & CRM documentation. This guide covers everything you need to know to install, configure, and use VentureX ERP & CRM.

## ðŸ“š Documentation Files

| Document | Description |
|----------|-------------|
| [INSTALLATION.md](INSTALLATION.md) | Step-by-step installation guide |
| [USER_GUIDE.md](USER_GUIDE.md) | Complete user manual |
| [API_DOCUMENTATION.md](API_DOCUMENTATION.md) | REST API reference |
| [DEPLOYMENT.md](DEPLOYMENT.md) | Production deployment guide |
| [SECURITY.md](SECURITY.md) | Security features and configuration |
| [TROUBLESHOOTING.md](TROUBLESHOOTING.md) | Common issues and solutions |
| [DATABASE-SETUP.md](DATABASE-SETUP.md) | Database configuration and management |
| [AI-SETUP.md](AI-SETUP.md) | AI provider setup and configuration |
| [API-SETUP.md](API-SETUP.md) | API key and provider configuration |
| [BACKUP_AND_RESTORE.md](BACKUP_AND_RESTORE.md) | Backup and disaster recovery |

## ðŸš€ Getting Started

### Quick Installation (5 minutes)

```bash
# 1. Extract the package
unzip VentureX-ERP.zip
cd VentureX-ERP

# 2. Install dependencies
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env file
DB_DATABASE=my_erp
DB_USERNAME=root
DB_PASSWORD=your_password

# 5. Run migrations and seed demo data
php artisan migrate --seed

# 6. Build frontend assets
npm install && npm run build

# 7. Start the server
php artisan serve
```

Open http://127.0.0.1:8000 and login with demo credentials:

- Email: `demo_admin@example.com`
- Password: `Demo_Admin_2026!`

> —¸ These are demo credentials for testing only. Change passwords before production use.

### Alternative: CLI Setup Wizard

```bash
php artisan setup
```

Interactive wizard that guides you through the entire installation process.

## ðŸ“– Module Guides

### CRM (Customer Relationship Management)
- Manage customers and contacts
- Track activities (calls, emails, meetings)
- Customer portal for self-service
- AI-powered insights and recommendations

### Sales
- Create quotations and convert to orders
- Generate invoices and track payments
- Sales pipeline management
- Revenue analytics and forecasting

### Inventory
- Product catalog with variants
- Multi-warehouse management
- Stock level tracking
- Low stock alerts

### Procurement
- Purchase order management
- Supplier relationship tracking
- Request for Quotation system
- Three-way matching

### Finance
- Chart of accounts
- Journal entries
- Accounts receivable and payable
- Financial reporting

### Logistics
- Shipment tracking
- Container management
- Landed cost calculation
- Delivery scheduling

### HR
- Employee management
- Leave and attendance
- Payroll processing
- Performance reviews

### Projects
- Task management
- Time tracking
- Milestone tracking
- Resource allocation

### Helpdesk
- Ticket management
- SLA tracking
- Knowledge base
- Customer satisfaction

## ðŸ” Security

VentureX ERP & CRM includes comprehensive security features:

- **3-Step Registration** — Email, Phone, and Selfie verification
- **Multi-Factor Authentication** — TOTP-based 2FA
- **App Lock** — 6-digit PIN protection
- **Device Trust** — Trusted device management
- **RBAC** — Role-based access control
- **Audit Logging** — Complete activity tracking
- **Emergency Lockdown** — System-wide security mode

See [SECURITY.md](SECURITY.md) for detailed security documentation.

## ðŸ¤– AI Features

VentureX ERP & CRM integrates with multiple AI providers:

- **GPT-5** — OpenAI's latest model
- **Claude** — Anthropic's AI assistant
- **Gemini** — Google's AI model
- **DeepSeek** — Advanced reasoning model
- **NVIDIA** — Enterprise AI platform

Configure your preferred provider in Settings > AI Configuration.

## ðŸ†˜ Support

- **Email:** support@venturexerp.com
- **Documentation:** This folder
- **Video Tutorials:** Available on YouTube
- **Community Forum:** Coming soon

## ðŸ“ License

VentureX ERP & CRM is licensed under a Commercial License — see the [LICENSE](../LICENSE) file for details.

> **Note:** This software is proprietary. Unauthorized copying, modification, or distribution is prohibited.
