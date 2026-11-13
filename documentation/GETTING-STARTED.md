# Getting Started with VentureX ERP & CRM

This guide is for the first person who logs in — usually the owner or operations lead. It explains what the product does, gives you a working example of each core flow, and shows how a real company uses it day to day. The same tour is available inside the app at **Getting Started** in the sidebar.

---

## 1. What VentureX is

VentureX ERP & CRM is one shared workspace for the commercial cycle of a company:

```
Lead → Customer → Quotation → Sales Order → Invoice → Payment
Supplier → Requisition → Purchase Order → Goods Receipt → Stock
Shipment → Container → Landed Cost → True margin
Everything → Finance, Audit Logs, AI answers
```

Instead of keeping customers in a spreadsheet, quotes in Word, stock in a register, invoices in another tool and reports in email, every department works on the same record. A change made by sales is immediately visible to finance and management.

### Who it is for

| Business type | Typical use |
|---|---|
| Wholesale distributor | Quote → order → pick → invoice → payment, with live stock |
| Manufacturer | Products, warehouses, purchasing, landed cost, sales |
| Service agency | Leads, pipeline, quotations, milestone invoices, receivables |
| Import/export trader | Supplier offers, POs, containers, landed cost, margin analysis |
| Owner / finance | One dashboard for revenue, receivables, payables and audit trail |

### What it replaces

| Module | What you do there | Usually replaces |
|---|---|---|
| CRM | Customers, contacts, leads, activities, pipeline | Spreadsheets + WhatsApp follow-ups |
| Sales | Quotations, sales orders, invoices, payments | Word quotes + invoice templates |
| Procurement | Supplier offers, requisitions, RFQs, purchase orders | Email threads + purchase registers |
| Inventory | Products, warehouses, stock, reorder alerts | Stock registers + physical counts |
| Logistics | Shipments, containers, landed cost | Freight forwarder spreadsheets |
| Finance | Chart of accounts, journals, receivables, payables | Standalone accounting tools |
| AI Center | Assistant, copilot, document reader, 80+ RAG pipelines | Manual reporting and searching |
| Support & Admin | Help center, tickets, users, roles, audit logs, exports | Shared inboxes + ad-hoc admin |

---

## 2. Your first 7 steps

Each step takes 1–3 minutes. Use the demo data first, then replace it with your own.

### Step 1 — Set up your company

Go to **Administration → Settings** (owner account only).

- Legal name, currency, timezone
- Invoice prefix (for example `INV`)
- Fiscal year and tax defaults

**Example:** Acme Trading LLC · INR · Asia/Kolkata · prefix `INV`.

### Step 2 — Add one customer and one supplier

- **CRM → Customers → New**
- **Purchase & Procurement → Suppliers → New**

**Example:** Customer *Bluewave Retail*; supplier *Shenzhen Components Ltd*.

### Step 3 — Add a product and opening stock

Go to **Inventory → Products → New**, then **Inventory → Stock** to set opening quantity per warehouse.

**Example:** SKU `VC-1001`, Bluetooth Headset, selling price ₹2,499, reorder level 20, opening stock 120 units in the main warehouse.

### Step 4 — Create a quotation and convert it

1. **Sales → Quotations → New**
2. Choose the customer and add 10 × `VC-1001`
3. Save, send, and mark the deal as won
4. Convert to a **Sales Order**

**Example:** 10 × ₹2,499 = ₹24,990 → converted to `SO-2026-0087`.

### Step 5 — Raise an invoice and record payment

1. Open the sales order and create an **Invoice**
2. Record full or partial payment under **Sales → Payments**

**Example:** `INV-2026-0042`, ₹24,990, paid by UPI, balance ₹0. The receivable disappears from the finance dashboard automatically.

### Step 6 — Buy stock end-to-end

1. **Supplier Offers** — compare quotes from suppliers
2. **Purchase Requisitions** — raise and approve the request
3. **Purchase Orders** — generate the PO
4. Receive goods against the PO so stock increases
5. **Logistics → Landed Costs** — allocate freight and duty

**Example:** `PR-2026-0031` → `PO-2026-0114` → 200 units received → landed cost ₹1,890/unit.

### Step 7 — Ask AI about your own data

Open **AI Center → AI Assistant** (quick answers) or **AI Center → Enterprise RAG** (answers with sources, confidence and latency).

Try:

- Which customers have overdue invoices this month?
- Show products below reorder level in each warehouse.
- Summarise procurement spend by supplier this quarter.
- Which leads have had no activity in the last 14 days?
- What is the landed cost and margin of container CONT-2026-014?
- Compare quotation Q-1042 with this customer's last order.
- Which sales orders are pending shipment for more than 5 days?
- Explain the gap between finance revenue and invoiced sales.

---

## 3. How companies use it

### Wholesale distributor (10–50 staff)

1. Sales creates a quotation for 10 units.
2. The deal is won and converted to a sales order.
3. Stock is reserved and picked from the warehouse.
4. An invoice is raised and payment is recorded.
5. Finance sees the receivable clear automatically.

**Daily habit:** check **Sales → Orders** for pending shipments and **Finance → Receivables** for overdue balances.

### Services agency

1. A lead arrives from the website or a referral.
2. The owner moves it through the pipeline stages.
3. A quotation is sent with a clear scope.
4. The project is invoiced in milestones.
5. The receivables dashboard shows what is unpaid.

**Daily habit:** check **CRM → Pipeline** and **CRM → Activities** every morning.

### Import / export trader

1. Compare supplier offers side by side.
2. Raise a purchase order and track the shipment and container.
3. Allocate freight and duty as landed cost.
4. See true cost per unit before selling.
5. Ask AI which SKUs have the thinnest margin.

**Daily habit:** check **Logistics → Landed Costs** and **Inventory → Stock** before pricing.

---

## 4. What to do first, by role

| Role | First task | Where |
|---|---|---|
| Owner / Admin | Configure company, invite users, assign roles | Administration → Settings / Users |
| Sales | Add leads and work the pipeline | CRM → Leads / Pipeline |
| Procurement | Compare offers and raise requisitions | Purchase & Procurement |
| Warehouse | Maintain products, stock and reorder levels | Inventory → Products / Stock |
| Finance | Review journals, receivables and payables | Finance → Dashboard |
| Support | Answer tickets and update the help center | Support → My Tickets |

---

## 5. Onboarding checklist

Copy this into your project tracker, or use the interactive version on the in-app **Getting Started** page (saved in your browser).

- [ ] Company profile, currency and invoice prefix configured
- [ ] Team invited and roles assigned
- [ ] First 10 customers imported or added
- [ ] Key suppliers added
- [ ] Products and opening stock entered
- [ ] First quotation sent
- [ ] First sales order created
- [ ] First invoice raised and paid
- [ ] First purchase order approved
- [ ] First AI question answered from live data

---

## 6. Bringing existing data

- **Admin → Import Data** for CSV imports (customers, suppliers, products, opening stock).
- **Admin → Export Center** for controlled exports with approval.
- Roles and permissions decide who can import, export or approve.

Recommended order: customers → suppliers → products → opening stock → open invoices. Import master data before transactions so references resolve correctly.

---

## 7. Everyday habits that keep the system clean

1. **One record per entity.** Search before creating a second customer or supplier.
2. **Close the loop.** Every order should end in an invoice and a payment; every PO in a goods receipt.
3. **Use landed cost.** If you import, allocate freight and duty so margins are real.
4. **Review audit logs weekly.** Administration → Audit Logs shows who changed what.
5. **Ask AI with specifics.** Mention the module, period and entity for better answers.

---

## 8. Troubleshooting

| Symptom | Fix |
|---|---|
| Cannot log in | Use the exact URL and credentials from the README; clear cookies for the site or use Incognito |
| "Page expired" (419) | Refresh the page once (`Ctrl+F5`) and submit again |
| AI says no provider configured | Local intelligence still works; add an API key in Settings for enhanced answers |
| Empty dashboard | Add customers, products and at least one order — widgets read live data |
| Import fails | Check column headers against the template and import master data first |

---

## 9. Next steps

- Read the [User Guide](USER_GUIDE.md) for module-by-module detail.
- Read the [Admin Guide](ADMIN_GUIDE.md) for roles, permissions and settings.
- Read the [Installation Guide](INSTALLATION.md) for production deployment.
- Open the in-app **Help Center** for FAQs, tickets and documentation.

---

*VentureX ERP & CRM — one workspace for sales, stock, purchasing, finance, logistics and AI.*
