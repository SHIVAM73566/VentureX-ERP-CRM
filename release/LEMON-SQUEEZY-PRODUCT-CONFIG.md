# ============================================================
# LEMON SQUEEZY — PRODUCT CONFIGURATION CONTENT
# VentureX ERP & CRM
# Copy & paste each section into Lemon Squeezy dashboard
# ============================================================


# ============================================================
# 1. VARIANTS (3 Tiers)
# ============================================================

## Variant 1: Starter

Name: Starter
Price: $59.00
Description:
Everything you need to run your business. Full source code with all 8 modules — CRM, Sales, Inventory, Procurement, Finance, Logistics, Support, and Admin. Includes AI copilot, Docker configs, and documentation. Self-hosted, unlimited users, one-time purchase. Perfect for small businesses and freelancers getting started with ERP.

## Variant 2: Professional

Name: Professional
Price: $99.00
Description:
Everything in Starter plus priority email support for 90 days, custom branding guide (remove VentureX branding and add your own), and a 30-minute onboarding call to help you deploy and configure your ERP. Includes all future updates for 1 year. Ideal for agencies and growing businesses that need setup assistance.

## Variant 3: Enterprise

Name: Enterprise
Price: $199.00
Description:
Everything in Professional plus 1 year of priority support, custom feature requests (up to 5), white-label deployment assistance, multi-company setup consultation, and lifetime updates. Dedicated Slack channel for direct developer access. Built for companies that need a tailored ERP solution with ongoing partnership.


# ============================================================
# 2. LICENSE KEYS
# ============================================================

## Recommendation: OFF

Reason: Your product is open source code, not a compiled application. There's no license server, no phone-home, no activation mechanism. Buyers download the ZIP, extract it, and run it on their own server. License keys would add unnecessary friction and contradict the "you own it" positioning.

## License Delivery Message (if you enable it later):

Thank you for purchasing VentureX ERP & CRM!

Your purchase includes the complete source code with a Commercial License. You can:
- Deploy on unlimited domains
- Use for unlimited projects and clients
- Modify the source code freely
- White-label for your business

No activation needed. Download, extract, and start using immediately.

License Type: Commercial (Lifetime)
Support: support@venturexerp.com
Documentation: Included in /documentation/ folder


# ============================================================
# 3. STOREFRONT DISPLAY
# ============================================================

## Recommendation: ON

## Storefront Description (150 characters max):

Complete self-hosted ERP + CRM + AI copilot. Laravel 13, PHP 8.4. CRM, invoicing, inventory, procurement, finance. One-time $59. Unlimited users. Full source code.

(148 characters)


# ============================================================
# 4. CONFIRMATION MODAL (Post-Purchase Popup)
# ============================================================

## Headline:
Welcome to VentureX ERP & CRM!

## Body:
Your purchase is complete. Here's what to do next:

1. Download the ZIP file from your email receipt
2. Upload to your server and extract
3. Run: composer install && npm install && npm run build
4. Copy .env.example to .env and set your database
5. Run: php artisan migrate --seed
6. Open http://your-domain.com/install

The 6-step installation wizard will guide you through the rest.

Need help? Email support@venturexerp.com or check the included documentation.

## Button Text:
Download Now


# ============================================================
# 5. EMAIL RECEIPT
# ============================================================

## Subject Line:
Your VentureX ERP & CRM download is ready

## Email Body:

---

Hi {{customer.first_name}},

Thank you for purchasing VentureX ERP & CRM!

Your order #{{order.id}} is confirmed and your download is ready.

---

DOWNLOAD YOUR PRODUCT
---------------------

Download Link: {{product.download_url}}

This link does not expire. You can re-download anytime from your Lemon Squeezy account.

---

QUICK START (6 Steps)
----------------------

1. Upload the ZIP to your server (e.g., /var/www/html/)
2. Extract: unzip VentureX-ERP-CRM-v1.0.0.zip
3. Install dependencies: composer install && npm install && npm run build
4. Configure: cp .env.example .env && php artisan key:generate
5. Setup database: php artisan migrate --seed
6. Start: php artisan serve

Open http://localhost:8000 in your browser. The installation wizard will guide you.

---

DEMO CREDENTIALS
----------------

After running php artisan migrate --seed, use these accounts:

Super Admin: demo_admin@example.com / Demo_Admin_2026!
CEO: demo_manager@example.com / Demo_Manager_2026!
Sales Manager: demo_sales@example.com / Demo_Sales_2026!

(Change all passwords before production use)

---

WHAT'S INCLUDED
---------------

- Full Laravel 13 source code (249 PHP files)
- 8 business modules (CRM, Sales, Inventory, Procurement, Finance, Logistics, Support, Admin)
- AI copilot (works without API keys)
- 157 Blade views with dark mode
- Docker & Docker Compose configs
- Complete documentation (Installation, User Guide, Admin Guide, API Docs)
- 17 high-resolution screenshots
- Import/Export center (CSV/XLSX)
- REST API with rate limiting
- 339 PHPUnit tests (464 assertions, 0 failures)

---

LICENSE TERMS
-------------

License Type: Commercial (Lifetime)

You can:
- Deploy on unlimited domains
- Use for unlimited client projects
- Modify the source code freely
- White-label for your business

You cannot:
- Redistribute the source code publicly
- Resell as-is without modification
- Share download links

---

SYSTEM REQUIREMENTS
-------------------

- PHP 8.4+
- MySQL 8.0+ or MariaDB 10.6+
- Composer 2.x
- Node.js 18+
- Apache 2.4+ or Nginx 1.24+

---

NEED HELP?
-----------

Documentation: Included in /documentation/ folder
Email: support@venturexerp.com
GitHub: https://github.com/SHIVAM73566/VentureX-ERP-CRM/issues

Response time: Within 24 hours

---

Thank you for choosing VentureX ERP & CRM.

Build better businesses.

— The VentureX Team

support@venturexerp.com

---

(This receipt serves as your proof of purchase. Keep it for your records.)

Order ID: {{order.id}}
Date: {{order.created_at}}
Amount: {{order.total}}

---

# ============================================================
# 6. ADDITIONAL LEMON SQUEEZY SETTINGS
# ============================================================

## Product Status: Published

## Visible on Storefront: Yes

## License Agreement: OFF
(You provide license terms in the email receipt instead)

## Download Limit: Unlimited
(Source code product — no reason to limit downloads)

## Download Expiration: Never
(No expiration — buyers own it forever)

## Receipt Button Text: Download VentureX ERP & CRM

## Receipt Button URL: {{product.download_url}}

## Custom Footer (Email):
This email was sent by Lemon Squeezy on behalf of VentureX ERP & CRM.

## Physical Delivery: OFF
(Digital product — no shipping)

## Tax: Included in price
(Lemon Squeezy handles all tax calculation and remittance)

## Currency: USD

## Release Date: Today's date


# ============================================================
# 7. PRODUCT IMAGE / THUMBNAIL
# ============================================================

## Recommended Thumbnail:
Use "Dashboard of desktop.png" from screenshots/Product demo/

## Recommended Banner:
Use "Finance Dashboard.png" or "AI Support Assistant.png" — these show the most impressive features


# ============================================================
# 8. CATEGORY ON LEMON SQUEEZY STORE
# ============================================================

Primary: Software
Secondary: Business Tools

Tags: erp, crm, laravel, php, business, management, ai, inventory, invoicing, procurement, finance, self-hosted, dashboard, admin, api
