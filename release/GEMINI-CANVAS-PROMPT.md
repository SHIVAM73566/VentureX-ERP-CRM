# ============================================================
# GOOGLE AI STUDIO / GEMINI CANVAS PROMPT
# Paste this into aistudio.google.com or Gemini Canvas
# ============================================================

## PROMPT:

Create a professional landing page for "VentureX ERP & CRM" — a self-hosted Laravel ERP system.

PAGE STRUCTURE:
1. Hero section with dark gradient background (#0F172A), product name "VentureX ERP & CRM" with gradient text, tagline: "AI-powered business operating system. Full source code. Self-hosted. One-time purchase. Unlimited users."
2. Stats bar: 8 Modules, 339 Tests, 249 PHP Files, 157 Views
3. 8 module cards in a responsive grid: CRM, Sales, Inventory, Procurement, Finance, Logistics, Support, Admin — each with an icon, name, and 1-line description
4. Screenshot gallery: 6 images in a 2-column grid with hover zoom effect and overlay labels
5. Pricing card: $89.99 one-time, 10 feature checkmarks, "Buy Now" CTA button
6. Tech stack tags: Laravel 13, PHP 8.4, MySQL 8.0, Bootstrap 5, jQuery, Docker, REST API, PHPUnit 12
7. Final CTA section with gradient background
8. Footer with GitHub and Blog links

DESIGN REQUIREMENTS:
- Dark theme (background #0F172A, cards #1E293B)
- Gradient accents: #4F46E5 to #7C3AED
- Font: Inter from Google Fonts
- Smooth hover animations on cards and screenshots
- Responsive: works on mobile and desktop
- CSS-only (no JavaScript needed)
- All code in a single HTML file with inline styles

SCREENSHOTS SECTION:
- Use placeholder SVG images with text labels
- Labels: "Main Dashboard", "CRM Lead Pipeline", "Finance Dashboard", "AI Support Assistant", "Invoicing System", "Inventory Management"
- On hover: slight scale (1.02) + overlay with label

PRICING CARD:
- Bordered with primary gradient
- "BEST VALUE" badge on top
- Features list with checkmarks: 8 Modules, AI Copilot, 157 Views, REST API, Docker, 339 Tests, Import/Export, Docs, Commercial License, Lifetime Updates

CTA BUTTONS:
- Primary: "Buy Now — $89.99" → links to https://shivamverse418.gumroad.com/l/arrgpcw
- Secondary: "View Screenshots" → scrolls to screenshot section

MODULE DESCRIPTIONS:
- CRM: "Lead pipeline, contact management, opportunity tracking, AI-powered scoring"
- Sales: "Quotations, sales orders, order pipeline, fulfillment tracking"
- Inventory: "Multi-warehouse stock, reorder alerts, lot tracking, real-time levels"
- Procurement: "Purchase orders, supplier management, RFQ workflows, landed costs"
- Finance: "Invoicing, payment tracking, chart of accounts, bank reconciliation"
- Logistics: "Shipment tracking, delivery management, route optimization"
- Support: "Ticket system, knowledge base, SLA management, AI response drafts"
- Admin: "User roles, audit logs, system settings, import/export center"

OUTPUT: Single self-contained HTML file with all CSS inline. No external dependencies except Google Fonts Inter.


# ============================================================
# ALTERNATIVE SHORT PROMPT (if the above is too long)
# ============================================================

Create a dark-themed landing page for "VentureX ERP & CRM" — a Laravel ERP product priced at $89.99.

Include:
- Hero with gradient text title and tagline
- Stats bar (8 modules, 339 tests, 249 files, 157 views)
- 8 product feature cards in a grid (CRM, Sales, Inventory, Procurement, Finance, Logistics, Support, Admin)
- 6 screenshot placeholders with hover effects
- Pricing card with checkmark feature list
- Tech stack badges
- CTA button linking to https://shivamverse418.gumroad.com/l/arrgpcw
- Footer with GitHub link

Dark background (#0F172A), gradient accents (#4F46E5 → #7C3AED), Inter font, responsive, single HTML file.


# ============================================================
# HOW TO USE ON BLOGGER
# ============================================================

STEP 1: Upload screenshots to Blogger
- Go to Blogger Dashboard → Posts → New Post
- Click "Insert image" → Upload from computer
- Upload all 17 screenshots from "screenshots/Product demo/" folder
- After upload, right-click each image → "Copy image address"
- Save these URLs (you'll need them later)
- DELETE the draft post (don't publish it)

STEP 2: Create the demo page
- Go to Blogger Dashboard → Pages → New Page
- Click "HTML" tab (not Compose)
- Paste the content of "blogger-demo-page.html"
- Replace "YOUR_SCREENSHOT_URL_1" through "YOUR_SCREENSHOT_URL_6" with the actual image URLs from Step 1
- Click Preview to check
- Click Publish

STEP 3: Set as homepage (optional)
- Go to Settings → Static Pages
- Set "VentureX ERP & CRM Demo" as the homepage
