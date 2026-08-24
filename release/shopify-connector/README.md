SHOPIFY CONNECTOR APP — COMPLETE BUILD & SUBMIT GUIDE
=====================================================

Goal: Get "VentureX ERP & CRM Sync" live on the Shopify App Store.
Time needed: ~4-6 hours setup + 5-10 days Shopify review.
Cost: $0 (free Partner account + free development store).

=====================================================
STEP 1 — INSTALL TOOLS (10 min)
=====================================================
1. Install Node.js 20+: https://nodejs.org (LTS version)
2. Open terminal and confirm:
   node --version    (should show v20+)

=====================================================
STEP 2 — CREATE PARTNER ACCOUNT + DEV STORE (15 min)
=====================================================
1. Go to https://partners.shopify.com  -> Sign up (free)
2. In Partner Dashboard: Stores -> Add store -> Create development store
   Name it: VentureX Demo Store
3. Note your store URL: venturex-demo.myshopify.com

=====================================================
STEP 3 — GENERATE THE APP SCAFFOLD (20 min)
=====================================================
Run these commands one by one:

   npm init @shopify/app@latest

Answer the prompts exactly:
   ? App name:                venturex-sync
   ? Template:                Remix  (choose this)
Then:

   cd venturex-sync
   npm install
   npm run deploy             # creates the app in your Partner account
   npm run dev                # starts local dev server

When it opens the browser -> install the app on your development store.
If you see the app home screen inside Shopify admin: scaffold works.

=====================================================
STEP 4 — DROP IN THE VENTUREX SYNC FILES (30 min)
=====================================================
Copy the two provided module files into your generated project:

1. Copy web/gdpr.js  ->  into  app/services/gdpr.server.js
   (mandatory GDPR compliance handlers - Shopify rejects apps without these)

2. Copy web/sync.js   ->  into  app/services/venturex-sync.server.js
   (order + product sync engine that pushes data to your VentureX REST API)

3. Register the webhooks: edit shopify.app.toml and add the [webhooks]
   section shown in shopify.app.toml (provided file).

4. Add your VentureX connection: copy .env.example values into the
   app environment (npm will read .env automatically):
   VENTUREX_API_URL=https://your-venturex-install.com/api
   VENTUREX_API_TOKEN=<token created in VentureX > Settings > API>

=====================================================
STEP 5 — TEST THE FULL FLOW (30 min)
=====================================================
1. npm run dev  ->  open development store admin
2. Place a test order in the demo store
3. Confirm it appears in VentureX (Sales Orders list)
4. Add a product in Shopify -> confirm it lands in VentureX catalog
5. Uninstall the app -> confirm customers/redact webhook fires (check logs)

All 5 working = you are submission-ready.

=====================================================
STEP 6 — PREPARE LISTING ASSETS (1 hr)
=====================================================
Icon:      1200x1200 px square crop of VentureX logo on indigo gradient
Screens:   1600x900 px each, minimum 3 (use screenshots from
           C:/MY_ERP/screenshots/Product demo/, resized)
Listing text: copy from SHOPIFY-APP-STORE-LISTING.md
Support:   support@venturexerp.com  (must answer during review window)
Privacy:   host a simple privacy policy page (Blogger page works)

=====================================================
STEP 7 — SUBMIT FOR REVIEW (15 min)
=====================================================
1. Partner Dashboard -> Apps -> venturex-sync -> Distribution
2. Choose: Public distribution -> Shopify App Store
3. Fill every field (listing text from STEP 6 assets)
4. Pricing page: select "Free" (the ERP license is sold separately;
   disclose external purchase requirement in listing description)
5. Submit -> review takes 5-10 business days
6. Watch email daily; respond to reviewer questions within 24 hours

TOP 3 REJECTION CAUSES (avoid these):
  X Missing GDPR webhooks          -> solved by gdpr.js
  X Broken uninstall/redact        -> test Step 5.5 twice
  X Slow/no support responses      -> monitor inbox during review week

=====================================================
FILES PROVIDED IN THIS PACKAGE
=====================================================
README.md                      <- this guide
shopify.app.toml               <- add [webhooks] block to your scaffold
.env.example                   -> environment variables template
gdpr.js                        -> mandatory compliance webhook handlers
venturex-sync.server.js        -> order/product sync to VentureX REST API
