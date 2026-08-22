# SHOPIFY APP STORE - REQUIREMENTS & PREPARATION

## REALITY CHECK (IMPORTANT)
The Shopify App Store lists Shopify apps (embedded tools for Shopify merchants) - it does NOT list standalone ERP source code. To get on the App Store you need to build a small Shopify connector app. The smart play: build a "VentureX Connector" that syncs Shopify orders/products into your VentureX ERP, then list THAT app free on Shopify while the connector drives buyers back to VentureX licenses.

## WHAT YOU ALREADY HAVE
- REST API (18 controllers) in VentureX - ready to receive Shopify webhooks
- WordPress plugin precedent proving integration skills
- Full docs + screenshots for marketing

## PATH TO SHOPIFY APP STORE

### Phase 1: Build Connector App (~1-2 weeks)
1. Create Shopify Partner account: https://partners.shopify.com
2. Create app with Shopify CLI:
```
npm init @shopify/app@latest
# choose: Node.js + Remix template
npm run dev
```
3. Core features for v1:
   - OAuth install flow (Shopify handles)
   - Webhook: orders/create -> POST to VentureX /api/orders
   - Webhook: products/create|update -> POST to VentureX /api/products
   - Admin embed: "Sync now" button + last-sync status card
4. Your VentureX REST API already has endpoints to accept these payloads

### Phase 2: Listing Materials (PREPARED BELOW - ready when app is built)

### _ App Name
```
VentureX ERP & CRM Sync
```

### _ Tagline (max 60 chars)
```
Sync Shopify orders and products into your VentureX ERP
```

### _ App Description
```
Connect your Shopify store to VentureX ERP & CRM and stop copy-pasting data between systems.

FEATURES
- Automatic order sync: every new Shopify order flows into VentureX sales orders instantly
- Product catalog sync: keep inventory aligned between Shopify and your warehouses
- Customer records: Shopify customers become CRM contacts automatically
- One-click manual sync button for catch-up imports
- Sync health dashboard embedded right in your Shopify admin

WHY VENTUREX
VentureX is a complete business operating system: CRM pipeline, multi-warehouse inventory, double-entry accounting, procurement, logistics and an AI Copilot for insights. This app connects your storefront to all of it.

REQUIREMENTS
A VentureX license (one-time purchase) running on your own server. Free 5-day trial of the full ERP included with every license.

SUPPORT
Email support@venturexerp.com - we respond within 12 hours.
```

### _ Screenshots required by Shopify (1600x900 px each, min 3)
```
Export from C:/MY_ERP/screenshots/Product demo/ resized to 1600x900:
1. Dashboard of desktop.png
2. Sales Orders.png
3. customer dashboard.png
Plus 1 new: connector settings page screenshot (after building)
```

### _ App icon (1200x1200 px)
```
Square crop of VentureX logo mark, indigo gradient background #4F46E5->#7C3AED
```

### _ Pricing inside listing
```
App itself: FREE (connector)
Requires: VentureX ERP license $89.99 one-time at shivamverse418.gumroad.com/l/arrgpcw
(Shopify allows "requires external purchase" disclosure in listing)
```

### _ Categories
```
Primary: Orders and fulfillment > Order management
Secondary: Inventory and syncing
```

## PHASE 3: APPROVAL REQUIREMENTS (Shopify strict checklist)
1. GDPR webhooks mandatory: customers/data_request, customers/redact, shop/redact - implement before submit
2. App must work on test store end-to-end (they test install->use->uninstall)
3. Support link must be live (support@venturexerp.com + docs URL)
4. No upsells inside admin without clear labeling
5. Review time: 5-10 business days; common rejections are broken uninstall and missing GDPR hooks - double-check those
