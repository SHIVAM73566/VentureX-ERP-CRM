# User Guide

Comprehensive guide for using all modules in VentureX ERP & CRM.

---

## Getting Started

### Logging In

1. Navigate to your application URL
2. Enter your email and password
3. If MFA is enabled, enter your authenticator code
4. You will be redirected to the Dashboard

### Navigation

The main navigation sidebar contains all modules. Click any module to expand its submenu. Your visible modules depend on your assigned role and permissions.

### Dashboard

The Dashboard provides an at-a-glance view of key metrics:
- Revenue and sales summary
- Pipeline status
- Outstanding invoices and payments
- Recent activities
- AI-generated insights

---

## CRM Module

### Customers

Manage your customer database.

**List View:**
- Browse all customers with search and filters
- Sort by name, email, phone, created date
- Export to CSV/Excel

**Create Customer:**
1. Click "New Customer"
2. Fill in required fields: Name, Email, Phone
3. Add company details, address, and notes
4. Assign tags for segmentation
5. Save

**Customer Details:**
- View contact information and addresses
- See related activities, opportunities, and invoices
- Track communication history
- Upload documents

### Leads

Track potential customers through the sales funnel.

**Lead Stages:**
1. New â€“ Initial contact
2. Contacted â€“ First communication made
3. Qualified â€“ Meets criteria
4. Unqualified â€“ Does not meet criteria

**Create Lead:**
1. Click "New Lead"
2. Enter lead name, source, and contact info
3. Set assigned sales rep
4. Add description and expected value
5. Save

### Opportunities

Manage deals through the sales pipeline.

**Pipeline Stages:**
- Prospecting
- Qualification
- Proposal
- Negotiation
- Closed Won
- Closed Lost

**Create Opportunity:**
1. Click "New Opportunity"
2. Link to existing customer or create new
3. Enter deal name, value, and expected close date
4. Assign to sales rep
5. Move through pipeline stages as deal progresses

### Contacts

Manage individual contacts within customer organizations.

- Link contacts to customers
- Store email, phone, job title
- Track contact activities and history

### Activities

Log and schedule interactions:
- Calls
- Meetings
- Emails
- Tasks
- Notes

**Create Activity:**
1. Click "New Activity"
2. Select activity type
3. Choose related customer/opportunity/lead
4. Set date, time, and assigned user
5. Add description and outcome
6. Save

---

## Sales Module

### Quotations

Create and send price quotations to customers.

**Create Quotation:**
1. Click "New Quotation"
2. Select or create customer
3. Add line items (products/services)
4. Set quantities, unit prices, and discounts
5. Add tax rates
6. Set validity period
7. Save as draft or send directly

**Quotation Statuses:**
- Draft â€“ Being prepared
- Sent â€“ Delivered to customer
- Accepted â€“ Customer approved
- Rejected â€“ Customer declined
- Expired â€“ Validity period passed

**Actions:**
- Convert to Sales Order
- Download as PDF
- Send via email
- Duplicate

### Sales Orders

Confirmed orders from accepted quotations.

**Workflow:**
1. Quotation accepted â†’ Sales Order created
2. Order confirmed by sales manager
3. Fulfillment initiated (inventory/shipping)
4. Invoice generated
5. Payment received

### Invoices

Generate invoices from sales orders.

**Create Invoice:**
1. Auto-generated from Sales Order or manually created
2. Verify line items, amounts, and tax
3. Set payment terms and due date
4. Send to customer

**Invoice Statuses:**
- Draft
- Sent
- Partially Paid
- Paid
- Overdue
- Void

### Payments

Record and track customer payments.

**Record Payment:**
1. Select invoice
2. Enter payment amount
3. Choose payment method (bank transfer, cash, check, card)
4. Enter reference number
5. Save

**Payment Tracking:**
- View payment history per invoice
- Track outstanding balances
- Generate payment receipts
- Reconcile with bank statements

---

## Procurement Module

### Suppliers

Manage your supplier/vendor database.

**Supplier Profile:**
- Company information and contacts
- Product categories supplied
- Rating and performance metrics
- Historical orders and delivery performance

### Supplier Offers

Request and compare supplier quotes.

**Workflow:**
1. Create offer request
2. Send to selected suppliers
3. Receive and compare responses
4. Select winning offer
5. Convert to Purchase Order

### RFQs (Request for Quotations)

Formal RFQ process for sourcing.

**Create RFQ:**
1. Specify required items with quantities
2. Set delivery requirements
3. Select target suppliers
4. Send RFQ
5. Receive and evaluate responses

### Purchase Requisitions

Internal purchase requests from departments.

**Approval Workflow:**
1. Employee submits requisition
2. Department manager reviews
3. Procurement manager approves
4. Converts to Purchase Order

### Purchase Orders

Formal orders placed with suppliers.

**Create PO:**
1. Select supplier
2. Add items from requisition or RFQ
3. Set quantities, prices, and delivery dates
4. Submit for approval
5. Send to supplier upon approval

**PO Statuses:**
- Draft
- Pending Approval
- Approved
- Sent to Supplier
- Partially Received
- Fully Received
- Closed

---

## Inventory Module

### Products

Manage your product catalog.

**Product Details:**
- SKU, name, description
- Category and type
- Unit of measure
- Cost and selling prices
- Stock levels per warehouse
- Minimum/maximum stock levels
- Reorder point

### Warehouses

Manage storage locations.

**Features:**
- Multiple warehouse support
- Stock level tracking per warehouse
- Transfer between warehouses
- Warehouse-specific settings

### Stock Movements

Track all inventory changes.

**Movement Types:**
- Purchase receipt
- Sales delivery
- Stock transfer
- Manual adjustment
- Return
- Write-off

**View:**
- Current stock levels
- Movement history
- Stock valuation reports

---

## Logistics Module

### Shipments

Track outbound shipments.

**Create Shipment:**
1. Select sales order
2. Choose items to ship
3. Enter shipping details (carrier, tracking)
4. Update status as shipment progresses

### Containers

Manage container shipments for international logistics.

- Container details and tracking
- Link to shipments
- Container status tracking

### Landed Costs

Calculate true product costs including shipping, duties, and fees.

**Allocate Landed Costs:**
1. Enter total landed costs
2. Allocate to received items
3. System calculates per-unit cost
4. Updates product cost basis

---

## Finance Module

### Finance Dashboard

Overview of financial health:
- Revenue vs. expenses
- Cash flow summary
- Outstanding receivables and payables
- Aging reports
- AI-generated financial insights

### Receivables

Track money owed by customers.

**Features:**
- Aging analysis (current, 30, 60, 90+ days)
- Customer balance summary
- Payment collection tracking
- Dunning management

### Payables

Track money owed to suppliers.

**Features:**
- Payment schedule management
- Supplier balance summary
- Due date tracking
- Payment prioritization

### Accounts

Chart of accounts for double-entry bookkeeping.

**Account Types:**
- Assets
- Liabilities
- Equity
- Revenue
- Expenses

### Journal Entries

Record all financial transactions.

**Create Entry:**
1. Select entry date
2. Add debit and credit lines
3. Reference source document
4. Verify balance
5. Post

---

## AI Module

### AI Assistant

Natural language query interface for business data.

**Usage:**
- Ask questions about sales, customers, inventory
- Get quick answers without running reports
- Context-aware responses based on your data

### AI Copilot

Intelligent workflow assistance.

**Features:**
- Suggest next actions
- Draft communications
- Identify anomalies
- Recommend optimizations

### Deep Analysis

Advanced data analysis powered by AI.

**Capabilities:**
- Trend analysis
- Forecasting
- Pattern recognition
- Anomaly detection
- Custom analysis queries

### Procurement AI

AI-powered procurement optimization.

**Features:**
- Supplier recommendation
- Price trend analysis
- Optimal order quantity suggestions
- Risk assessment

### Document Reader

AI-powered document processing.

**Supported Document Types:**
- Invoices
- Purchase orders
- Contracts
- Receipts

**Usage:**
1. Upload document
2. AI extracts key information
3. Review and confirm extracted data
4. Create records from extracted data

### Executive Review

AI-generated executive summaries.

**Features:**
- Periodic business summaries
- KPI tracking
- Alert generation
- Strategic recommendations

---

## Common Actions Across Modules

### Searching and Filtering

- Use the search bar at the top of list views
- Apply filters to narrow results
- Save custom filters for quick access

### Exporting Data

1. Navigate to any list view
2. Click "Export"
3. Choose format (CSV, Excel)
4. Download file

### Importing Data

1. Navigate to module
2. Click "Import"
3. Download template file
4. Fill in data
5. Upload completed file
6. Map columns
7. Confirm import

### Bulk Actions

1. Select multiple items using checkboxes
2. Choose action from bulk actions menu
3. Confirm action

### PDF Generation

Most documents (invoices, quotations, purchase orders) can be downloaded as PDF:
1. Open the document
2. Click "Download PDF"
3. File downloads to your computer
