# WOOCOMMERCE - SELL VIA YOUR OWN STORE (SETUP GUIDE)

WooCommerce is not a marketplace - it is a WordPress plugin you install on YOUR OWN site to sell directly with 0% platform commission. You already have a WordPress integration plugin inside VentureX; this guide sets up your own store.

## STEP 1: HOSTING + WORDPRESS
1. Get any PHP hosting (Hostinger/Namecheap/Cloudways) or use a subdomain like shop.yourdomain.com
2. Install WordPress (one-click installer in cPanel/hPanel)
3. Install WooCommerce plugin (Plugins > Add New > search "WooCommerce" > Install > Activate)
4. Run the WooCommerce setup wizard: currency USD, country, payment methods

## STEP 2: ENABLE DIGITAL DOWNLOADS
WooCommerce > Settings > Products > Downloadable products:
```
Downloads require login: No (guest checkout OK)
Grant access after payment: Immediately
Download limit: 5 per product
Download expiry: 30 days
File download method: Force downloads
```

## STEP 3: CREATE THE PRODUCT (Products > Add New)

### _ Title
```
VentureX ERP & CRM - AI-Powered Business Operating System | Laravel 13
```

### _ Short Description (above Add to Cart)
```
Complete AI-powered ERP & CRM on Laravel 13. CRM, inventory, procurement, accounting, logistics, support tickets and AI Copilot that works without API keys. Includes web installer, Docker setup, REST API, docs and 15 video tutorials. Instant ZIP download.
```

### _ Full Description (copy)
```
<h2>The Complete AI Business Operating System</h2>
<p>VentureX ERP &amp; CRM combines everything your business needs into one professional Laravel 13 application:</p>
<ul>
<li><strong>CRM &amp; Sales</strong> - leads, Kanban pipeline, quotations, invoices, sales orders</li>
<li><strong>Inventory &amp; Procurement</strong> - multi-warehouse stock, purchase orders, RFQ to supplier selection</li>
<li><strong>Finance</strong> - double-entry bookkeeping, AP/AR, aging reports, multi-currency</li>
<li><strong>Logistics</strong> - shipments, containers, landed costs, Incoterms</li>
<li><strong>Support Center</strong> - tickets, replies, FAQ knowledge base</li>
<li><strong>AI Copilot</strong> - insights and analysis across NVIDIA/Gemini/OpenAI/Claude, works without API keys</li>
<li><strong>Security</strong> - role permissions, two-factor auth, full audit trail</li>
</ul>

<h3>What is included in the download</h3>
<ul>
<li>Full source code - 92 controllers, 74 models, 169 responsive views</li>
<li>REST API (18 endpoints controllers) for mobile apps</li>
<li>Docker deployment configuration</li>
<li>WordPress lead-capture plugin</li>
<li>23 documentation files + 15-part video tutorial series</li>
<li>Demo data seeder - explore with realistic sample business data</li>
</ul>

<h3>Requirements</h3>
<p>PHP 8.3+, MySQL 5.7+, Apache/Nginx web server. Node.js 20+ optional for asset rebuild.</p>

<h3>License</h3>
<p>Commercial license: unlimited domains, rebranding allowed, client projects allowed. Source code redistribution not permitted.</p>
```

### _ Product Data (below description)
| Field | Value |
|-------|-------|
| Product type | Simple product |
| Virtual | CHECKED |
| Downloadable | CHECKED |
| Regular price | $89.99 |
| Sale price | (optional) $69.99 launch |
| SKU | VENTUREX-ERP-001 |

### _ Downloadable Files
```
Name: VentureX ERP CRM v1.0.0
URL: upload C:/MY_ERP/release/VentureX-ERP-CRM-v1.0.0-Gumroad.zip via media picker
Download limit: 5
Expiry: 30 days
```

### _ Product Image + Gallery
```
Main: Dashboard of desktop.png
Gallery: all other screenshots from C:/MY_ERP/screenshots/Product demo/
```

## STEP 4: PAYMENT METHODS (choose both)
1. **PayPal** (built-in): WooCommerce > Settings > Payments > PayPal - enter your PayPal email
2. **Stripe card payments**: install free "Stripe Payment Gateway for WooCommerce" plugin, connect Stripe account

## STEP 5: REFUND POLICY PAGE
Create page "Refund Policy" with:
```
VentureX ERP & CRM comes with a 5-day money-back guarantee.
- Full refund if AI features were not used.
- Refunds prorated by API usage when AI features consumed.
- Technical issues unresolved within 48 hours: always fully refunded.
- Products redistributed or shared are not refundable.
Requests: support@venturexerp.com
```
Link it in footer + checkout page.

## STEP 6: LAUNCH CHECKLIST
- [ ] Test purchase with PayPal sandbox or a real $1 test
- [ ] Order confirmation email delivers download link (test it)
- [ ] SSL certificate active (https) - required by Stripe/PayPal
- [ ] Set store notice: "Instant download after payment"
