# Video 8: Inventory & Procurement
**Duration:** 4-5 minutes

## Pre-Recording Checklist

- [ ] VentureX running at http://localhost:8000
- [ ] Logged in as demo_admin@example.com
- [ ] CRM data from Video 7 present (Acme Corporation, Sarah Mitchell)
- [ ] Sample product images ready (optional)
- [ ] Browser window at 1920×1080, zoom at 100%
- [ ] Notifications dismissed
- [ ] Screen recording software active at 1080p 60fps
- [ ] Default measurement units configured in Settings
- [ ] At least one vendor/supplier will be created during recording

---

## Script

### [0:00 - 0:15] INTRO
**On Screen:** Title card "Video 8: Inventory & Procurement" over blurred inventory dashboard
**Voiceover:** "Welcome back to the VentureX tutorial series. In this video, we're tackling Inventory and Procurement — how to manage your products, track stock levels, handle purchase orders, and keep your supply chain running smoothly. Let's get into it."
**Action:** Fade in title card, hold 3 seconds, fade to Inventory dashboard

---

### [0:15 - 0:35] INVENTORY MODULE OVERVIEW
**On Screen:** VentureX sidebar, Inventory module expanded showing Products, Categories, Stock Levels, Transfers, Adjustments
**Voiceover:** "The Inventory module in VentureX is built around five key areas: Products — your entire catalog, Categories — how you organize products, Stock Levels — real-time inventory quantities, Transfers — moving stock between locations, and Adjustments — correcting counts when reality doesn't match the system. Let's start with categories."
**Action:** Click Inventory in sidebar, hover over each submenu item as mentioned

---

### [0:35 - 1:10] CATEGORIES AND ADDING PRODUCTS
**On Screen:** Categories page, showing empty state or default categories
**Voiceover:** "Categories help you organize your products logically. Let's create one. I'll click Add Category and name it 'Software Licenses'. Parent category can be left blank for top-level, or you can create hierarchies like Electronics > Laptops > Gaming. Let's keep it simple and save."
**Action:** Click "Categories", click "Add Category", type "Software Licenses" in Name, type "Software products and licenses" in Description, click "Save Category"

**On Screen:** Products page, empty state
**Voiceover:** "Now let's add a product. Navigate to Products and click Add Product."
**Action:** Click "Products" in sidebar, click "Add Product"

**On Screen:** Product creation form
**Voiceover:** "Product name: 'VentureX Pro — Annual License'. SKU: VNTX-PRO-001 — VentureX auto-generates SKUs if you prefer, but I like being explicit. Category: Software Licenses. Description: full product description for internal reference. Product type — is this a physical product you ship, a digital product you deliver, or a service? I'll choose Digital since this is a software license."
**Action:** Type "VentureX Pro — Annual License" in Product Name, type "VNTX-PRO-001" in SKU, select "Software Licenses" from Category dropdown, type "Annual license for VentureX Pro tier with full CRM, ERP, and AI features" in Description, select "Digital" from Product Type

**On Screen:** Pricing section of product form
**Voiceover:** "Pricing — let's set the regular price at $2,400 per year. Cost price — what it costs you to deliver — let's say $0 for digital. Tax class: Standard. The selling price field lets you set promotional or tiered pricing. I'll leave it at the regular price for now."
**Action:** Type "2400" in Regular Price, type "0" in Cost Price, select "Standard" from Tax Class dropdown

**On Screen:** Inventory tracking section
**Voiceover:** "Inventory tracking — toggle this on if you want VentureX to manage stock quantities. For digital products, you'd typically set this to unlimited or high stock. Let me enable it and set stock quantity to 9999. Low stock threshold: 100 — we'll get an alert when we dip below that. Manage stock by location: enabled if you have multiple warehouses. I'll keep it simple with one location for now."
**Action:** Toggle "Manage Stock" to ON, type "9999" in Stock Quantity, type "100" in Low Stock Threshold, toggle "Manage Stock by Location" OFF

**On Screen:** Additional fields — weight, dimensions, images
**Voiceover:** "Weight and dimensions — optional for digital products but required if you're shipping physical goods. Product images — you can upload multiple images that appear in the catalog. Let me save this product."
**Action:** Click "Save Product"

---

### [1:10 - 1:40] STOCK MANAGEMENT
**On Screen:** Products list showing "VentureX Pro — Annual License" with stock of 9999
**Voiceover:** "There's our product in the list with a stock quantity of 9999. Let's add a physical product too for demonstration. I'll quickly create 'USB-C Docking Station' as a physical product with a stock of 50 units."
**Action:** Click "Add Product", quickly fill in "USB-C Docking Station", SKU "DOCK-001", Category "Hardware" (create if needed), Type "Physical", Price "189.99", Cost "95.00", Stock Quantity "50", Low Stock Threshold "10", Save Product

**On Screen:** Stock Levels page
**Voiceover:** "The Stock Levels page gives you a real-time view of every product's inventory status. You can see current stock, reserved stock — that's items in pending orders — available stock, and the low stock flag. Let me show you what happens when we sell something."
**Action:** Click "Stock Levels" in sidebar, show the inventory dashboard

**On Screen:** Stock Adjustment form
**Voiceover:** "Stock Adjustments are for correcting discrepancies. Maybe you did a physical count and found 48 docking stations instead of 50. Let's log an adjustment."
**Action:** Click "Adjustments" in sidebar, click "Add Adjustment"

**On Screen:** Adjustment form
**Voiceover:** "Product: USB-C Docking Station. Adjustment type: Decrease. Quantity: 2. Reason: 'Physical count variance — 2 units unaccounted for'. Reference: 'Count 2026-08-20'. This keeps a full audit trail of every stock change."
**Action:** Select "USB-C Docking Station" from Product dropdown, select "Decrease" from Type, type "2" in Quantity, type "Physical count variance — 2 units unaccounted for" in Reason, type "Count 2026-08-20" in Reference, click "Save Adjustment"

**On Screen:** Stock Levels showing updated quantity (48)
**Voiceover:** "Stock is now at 48 units. Every adjustment is logged with who made it, when, and why — full traceability."
**Action:** Navigate back to Stock Levels, point to updated quantity

---

### [1:40 - 2:05] STOCK TRANSFERS
**On Screen:** Transfers page
**Voiceover:** "If you operate multiple locations or warehouses, Stock Transfers let you move inventory between them. Let's say we have a main warehouse and a satellite office. I'll create a transfer."
**Action:** Click "Transfers" in sidebar, click "Add Transfer"

**On Screen:** Transfer form
**Voiceover:** "From Location: Main Warehouse. To Location: Satellite Office. Product: USB-C Docking Station. Quantity: 5. We're shipping 5 docking stations to the satellite office. Add a note: 'Restocking satellite office for local fulfillment.' Save the transfer."
**Action:** Select "Main Warehouse" from From Location, select "Satellite Office" from To Location, select "USB-C Docking Station", type "5" in Quantity, type "Restocking satellite office for local fulfillment" in Notes, click "Save Transfer"

**On Screen:** Transfer listed as pending or completed
**Voiceover:** "The transfer shows as pending until received. When the satellite office confirms receipt, they mark it complete, and stock is deducted from the main warehouse and added to the satellite. Full audit trail, zero guesswork."
**Action:** Point to transfer status column

---

### [2:05 - 2:40] LOW STOCK ALERTS
**On Screen:** Notifications or Alerts section, or Settings > Notifications
**Voiceover:** "Remember our low stock threshold of 10 for the docking station? When available stock drops below that number, VentureX triggers a low stock alert. Let me simulate this by adjusting stock to 8 units."
**Action:** Click "Adjustments", click "Add Adjustment", select "USB-C Docking Station", select "Decrease", type "40" in Quantity, type "Simulated low stock for demo" in Reason, save adjustment

**On Screen:** Alert notification or badge
**Voiceover:** "There it is — a low stock alert badge appeared in the Inventory module and in the notification center. Users with the right permissions get emailed alerts too. You can configure alert thresholds per product and per user role in Settings."
**Action:** Point to notification badge, click to show alert details, navigate to Settings > Notifications to show configuration options

---

### [2:40 - 3:25] PROCUREMENT — PURCHASE ORDERS
**On Screen:** Procurement module sidebar expanded
**Voiceover:** "Now let's flip to the other side of the coin: Procurement. This module handles purchasing from your suppliers — creating purchase orders, receiving goods, and managing vendor relationships."
**Action:** Click "Procurement" in sidebar

**On Screen:** Vendors page, empty state
**Voiceover:** "First, let's add a vendor. Navigate to Vendors and click Add Vendor."
**Action:** Click "Vendors", click "Add Vendor"

**On Screen:** Vendor creation form
**Voiceover:** "Vendor name: 'TechSupply Global'. Contact person: 'Mike Chen'. Email: purchasing@techsupply-global.com. Phone: 555-0300. Address: their shipping address. Payment terms: Net 30. Tax ID: optional but useful for accounting. I'll also add the tag 'Preferred Vendor'. Save."
**Action:** Type "TechSupply Global" in Vendor Name, "Mike Chen" in Contact, "purchasing@techsupply-global.com" in Email, "555-0300" in Phone, fill in address, select "Net 30" from Payment Terms, type "Preferred Vendor" tag, click "Save Vendor"

**On Screen:** Purchase Orders page
**Voiceover:** "Now let's create a purchase order. Navigate to Purchase Orders and click Create PO."
**Action:** Click "Purchase Orders" in sidebar, click "Create Purchase Order"

**On Screen:** Purchase Order form
**Voiceover:** "Vendor: TechSupply Global. PO number auto-generates but I'll set it manually to PO-2026-001. Order date: today. Expected delivery: two weeks out. Now the line items — I'm ordering 100 USB-C Docking Stations at $95 each, that's our cost price. Add another line: 50 units of a Wireless Mouse at $22 each."
**Action:** Select "TechSupply Global" from Vendor, type "PO-2026-001" in PO Number, set Expected Delivery to two weeks out, click "Add Line Item", select "USB-C Docking Station", type "100" in Quantity, type "95.00" in Unit Cost, click "Add Line Item" again, type "Wireless Mouse" as new product (create inline or pre-create), type "50" in Quantity, type "22.00" in Unit Cost

**On Screen:** PO with line items, total calculated
**Voiceover:** "The total updates automatically — $9,500 plus $1,100, totaling $10,600. You can add notes, attach files, and set approval workflows if your organization requires PO approval before sending to vendors."
**Action:** Point to calculated total, add note "Standard restocking order — Q3 inventory replenishment", click "Save Purchase Order"

**On Screen:** PO detail view showing status as "Sent" or "Pending"
**Voiceover:** "The PO is saved. Status is Pending. When you send it to the vendor, you change it to Sent. When goods arrive, you receive them."
**Action:** Show PO detail view, click status dropdown showing options

---

### [3:25 - 3:55] RECEIVING INVENTORY
**On Screen:** Purchase Order detail for PO-2026-001
**Voiceover:** "Let's simulate receiving this order. I'll open the PO and click Receive Items."
**Action:** Click "Receive Items" button on PO detail

**On Screen:** Receiving form
**Voiceover:** "The receiving screen shows each line item. I ordered 100 docking stations — all 100 arrived in good condition, so I'll confirm 100 received. Wireless mice — let's say 48 arrived, 2 were damaged in transit. I'll enter 48 as received and note the discrepancy. When I save this, stock levels update automatically — 100 docking stations and 48 mice are added to inventory."
**Action:** Enter "100" for USB-C Docking Station received quantity, enter "48" for Wireless Mouse received quantity, type "2 units damaged in transit — filed claim with carrier" in discrepancy notes, click "Confirm Receipt"

**On Screen:** Stock Levels showing updated quantities
**Voiceover:** "Check the Stock Levels page — our docking station stock is now at 148, and we have 48 wireless mice. The PO status changes to Partially Received or Fully Received depending on what came in. If there's a discrepancy with the vendor, you can log it and track the resolution."
**Action:** Navigate to Stock Levels, point to updated quantities for both products

---

### [3:55 - 4:25] INVENTORY REPORTS
**On Screen:** Reports > Inventory Reports
**Voiceover:** "VentureX gives you solid inventory reporting. You can view stock valuation — what your inventory is worth at cost and at retail. Stock movement history — every adjustment, transfer, and receipt logged chronologically. Low stock reports — which products need reordering. Purchase order history — what you've ordered, from whom, and when. And supplier performance — lead times, fill rates, and defect rates."
**Action:** Click "Reports" in sidebar, click "Inventory Reports", hover over each report type: Stock Valuation, Stock Movement, Low Stock, PO History, navigate through each briefly

**On Screen:** Stock valuation report
**Voiceover:** "Our stock valuation shows $18,915 in inventory at cost — 148 docking stations at $95, 48 mice at $22, and 9999 software licenses at $0 cost. This feeds directly into your accounting module for balance sheet reporting."
**Action:** Point to valuation figures in the report

---

### [4:25 - 4:40] OUTRO
**On Screen:** Inventory dashboard with data populated, VentureX logo
**Voiceover:** "That's Inventory and Procurement in VentureX. You've seen how to manage products, categories, stock levels, adjustments, transfers, purchase orders, and vendor relationships. In the next video, we'll explore the AI features and how VentureX routes tasks across multiple AI providers. See you there."
**Action:** Show populated inventory dashboard, fade to end card with "Next: Video 9 — AI Features & Multi-Provider Setup"

---

## Notes for Editor

- Use zoom-in effects when showing form fields and stock numbers
- Highlight mouse cursor with yellow circle for all click actions
- When showing stock level changes, use a brief highlight animation on the quantity numbers
- Add subtle transitions between sections (crossfade, 0.5s)
- Background music: low corporate/tech ambient, duck during voiceover
- Consider adding a small counter animation when showing stock quantities updating
