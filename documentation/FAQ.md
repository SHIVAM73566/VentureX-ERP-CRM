# Frequently Asked Questions

**VentureX ERP & CRM — AI-Powered CRM & ERP Business Operating System**

> Version 1.0.0

---

## General

### What is VentureX ERP & CRM?

VentureX ERP & CRM is an AI-powered CRM and ERP business operating system designed for small to mid-sized businesses. It combines customer relationship management, sales, procurement, inventory, logistics, finance, and document management into a single platform with built-in AI capabilities.

### What technology stack does it use?

VentureX ERP & CRM is built on Laravel 13.25 (PHP 8.3+), with Blade templates, Alpine.js 3 for interactivity, Tailwind CSS v4 for styling, MySQL 8.0 for data storage, and Vite for asset compilation. It supports Redis for caching and queuing in production.

### Is there a free trial or demo available?

Yes. After running `php artisan db:seed`, demo accounts are created with random passwords for security. To create demo accounts with known passwords, run:

```bash
php artisan db:seed --class=DemoCredentialSeeder
```

This creates demo accounts with these credentials:

- **Demo Admin**: demo_admin@example.com / Demo_Admin_2026!
- **Demo Manager**: demo_manager@example.com / Demo_Manager_2026!
- **Demo Sales**: demo_sales@example.com / Demo_Sales_2026!

> —¸ **Security Notice**: Change all passwords before production use.

### Can VentureX ERP & CRM be self-hosted?

Yes. VentureX ERP & CRM can be self-hosted on your own servers using Docker Compose or a traditional LAMP/LEMP stack. See [INSTALLATION.md](INSTALLATION.md) for setup instructions.

### What browsers are supported?

VentureX ERP & CRM supports the latest versions of Chrome, Firefox, Safari, and Edge. Internet Explorer is not supported.

---

## Installation and Setup

### What are the minimum server requirements?

You need PHP 8.3+, MySQL 8.0, a web server (Apache or Nginx), Composer 2.8+, and Node.js 20+. A minimum of 2 GB RAM and 2 GB disk space is required. See [INSTALLATION.md](INSTALLATION.md) for the full list.

### How do I install VentureX ERP & CRM?

Follow the step-by-step instructions in [INSTALLATION.md](INSTALLATION.md). The short version: clone the repository, install dependencies with Composer and npm, configure your .env file, generate the application key, run migrations and seeders, build frontend assets, and start the development server.

### Can I use Docker for installation?

Yes. A Docker Compose configuration is provided that sets up all required services (application, Nginx, MySQL, Redis, queue workers, and scheduler). See the Docker section in [INSTALLATION.md](INSTALLATION.md).

### How do I configure the database?

Edit the .env file with your MySQL connection details, then run `php artisan migrate --seed`. Detailed database configuration is covered in [DATABASE-SETUP.md](DATABASE-SETUP.md).

---

## Users and Permissions

### How do I add new users?

Navigate to Settings > Users and click "New User." Fill in the user's name, email, and initial password, assign a role, and save. See [ADMIN-GUIDE.md](ADMIN-GUIDE.md) for details.

### What roles are available?

The system includes default roles: Super Admin, CEO, Sales Manager, Sales Representative, Procurement Manager, Warehouse Manager, Accountant, and Viewer. You can create custom roles with granular permissions at Settings > Roles. See [ADMIN-GUIDE.md](ADMIN-GUIDE.md).

### Can I create custom roles with specific permissions?

Yes. Navigate to Settings > Roles, click "New Role," and configure the permission matrix for each module (View, Create, Edit, Delete, Export, Approve). You can also set record-level access (All, Department, or Assigned Only).

### How do I enable two-factor authentication?

Navigate to Profile > Security and click "Enable Two-Factor Authentication." Scan the QR code with an authenticator app (Google Authenticator, Authy, etc.), enter the verification code, and save your recovery codes. Administrators can enforce 2FA for all users at Settings > Security > Authentication.

---

## CRM and Sales

### How do I convert a lead to an opportunity?

Open the lead and click "Convert." You can convert it to an Opportunity, a Quotation, or directly to a Customer record. The lead data is carried forward to the new record.

### Can I customize the sales pipeline stages?

Yes. Navigate to Settings > CRM > Pipeline Stages to add, rename, reorder, or remove stages. Each stage can have associated probability percentages for forecasting.

### How do quotations become invoices?

A quotation is accepted by the customer, then converted to a Sales Order. When the order is confirmed, an invoice can be generated automatically or manually from the order page.

### Can I send invoices by email?

Yes. Open the invoice and click "Send to Customer." The invoice is sent as a PDF attachment using the configured email settings. You can customize the email template at Settings > Templates.

---

## Inventory

### How does stock tracking work?

Every product tracks stock levels per warehouse. Stock increases on purchase order receipt and decreases on sales order delivery. Stock adjustments and inter-warehouse transfers are also tracked with full audit history.

### Can I set up low stock alerts?

Yes. Set the "Reorder Level" on each product. When stock falls below this level, the system generates a low stock alert visible on the dashboard and optionally sends an email notification.

### Does VentureX ERP & CRM support product variants?

Yes. Define attributes (size, color, etc.) on a base product, and the system generates a variant for each combination, each with its own SKU, price, and stock level.

---

## AI Features

### How do I set up AI providers?

Navigate to Settings > AI Providers, select a provider, enter your API key, choose a model, and click "Test Connection." Detailed setup instructions for each provider are in [AI-SETUP.md](AI-SETUP.md) and [API-SETUP.md](API-SETUP.md).

### What AI providers are supported?

VentureX ERP & CRM supports NVIDIA NIM, Swift, Google Gemini, DeepSeek, and Anthropic Claude. You can configure multiple providers with automatic fallback chains.

### Is my data sent to AI providers?

AI queries are sent to the configured provider's API for processing. You can enable data anonymization to strip personally identifiable information before sending. You can also restrict AI to local-only models in Settings > AI Governance. See [SECURITY.md](SECURITY.md) for data privacy details.

### Are there usage limits for AI?

Yes. Default quotas are 100 queries per user per day and 10,000 per company per month. These are configurable by administrators at Settings > AI Providers > Quotas.

---

## Backup and Maintenance

### How do I back up my data?

Use `mysqldump -u username -p my_erp > backup.sql` for database backups or navigate to Settings > Maintenance > Backups in the admin panel. See [BACKUP_AND_RESTORE.md](BACKUP_AND_RESTORE.md) for details.

### How do I restore from a backup?

Use `mysql -u username -p my_erp < backup_file.sql` to restore from a backup, or click "Restore" next to a backup in Settings > Maintenance > Backups. Always test restores on a staging environment first.

### How do I upgrade to a new version?

Pull the latest code, run `composer install`, `npm install && npm run build`, then `php artisan migrate --force`. Always back up before upgrading. See the Version Migration section in [BACKUP_AND_RESTORE.md](BACKUP_AND_RESTORE.md).

---

## Troubleshooting

### I see a 500 error. What do I do?

Check `storage/logs/laravel.log` for the specific error message. Common causes include missing database tables (run `php artisan migrate`), incorrect file permissions (check storage/ and bootstrap/cache/), or missing environment configuration (verify .env). See [TROUBLESHOOTING.md](TROUBLESHOOTING.md).

### The application is running slowly. What can I optimize?

Enable OPcache in PHP, configure Redis for caching and sessions, run `php artisan config:cache && php artisan route:cache && php artisan view:cache`, build production frontend assets with `npm run build`, and ensure your MySQL queries are indexed. See the Performance section in [DEPLOYMENT.md](DEPLOYMENT.md).

### Where can I find more help?

- Review [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for common issues
- Check the project's GitHub Issues page for known bugs
- Contact your system administrator
- Email support at support@venturexerp.com
