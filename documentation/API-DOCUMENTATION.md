# VentureX ERP & CRM — REST API Documentation

## Base URL

```
http://your-domain.com/api
```

All responses are in JSON format.

---

## Authentication

VentureX uses **Laravel Sanctum** for API token authentication.

### Obtaining a Token

```
POST /api/tokens
Content-Type: application/json

{
  "name": "my-app-token",
  "abilities": ["*"],
  "expires_at": "2026-12-31T23:59:59Z"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Token created",
  "data": {
    "id": 1,
    "name": "my-app-token",
    "token": "1|abc123...",
    "abilities": ["*"],
    "expires_at": "2026-12-31T23:59:59Z",
    "created_at": "2026-08-20T10:00:00Z"
  }
}
```

### Using the Token

Include the token in the `Authorization` header for all protected requests:

```
Authorization: Bearer 1|abc123...
```

### Managing Tokens

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/tokens` | Create a new token |
| `GET` | `/api/tokens` | List all tokens |
| `DELETE` | `/api/tokens/{id}` | Revoke a token |

---

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Success message",
  "data": { ... }
}
```

### Paginated Response
```json
{
  "success": true,
  "message": "Success",
  "data": [ ... ],
  "pagination": {
    "total": 100,
    "per_page": 15,
    "current_page": 1,
    "last_page": 7,
    "from": 1,
    "to": 15
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }
}
```

---

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| `200` | OK — Success |
| `201` | Created — Resource created |
| `204` | No Content — Deleted |
| `400` | Bad Request — Invalid input |
| `401` | Unauthorized — Missing/invalid token |
| `403` | Forbidden — Insufficient permissions |
| `404` | Not Found — Resource doesn't exist |
| `422` | Unprocessable Entity — Validation error |
| `429` | Too Many Requests — Rate limit exceeded |
| `500` | Server Error — Internal error |

---

## Rate Limiting

API requests are rate-limited via the `ApiProtection` middleware:

| Tier | Limit | Applies To |
|------|-------|------------|
| `public` | 30 req/min | Unauthenticated endpoints |
| `authenticated` | 120 req/min | Standard API endpoints |
| `admin` | 300 req/min | Admin endpoints |
| `ai` | 20 req/min | AI endpoints |
| `export` | 5 req/min | Export endpoints |

Rate limit headers are included in every response:
- `X-RateLimit-Limit`
- `X-RateLimit-Remaining`
- `Retry-After` (on 429)

---

## Endpoints

### User Profile

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/user` | Yes | Get authenticated user |

---

### CRM Module

#### Customers

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/crm/customers` | Yes | List customers |
| `POST` | `/api/crm/customers` | Yes | Create customer |
| `GET` | `/api/crm/customers/{id}` | Yes | Get customer |
| `PUT` | `/api/crm/customers/{id}` | Yes | Update customer |
| `DELETE` | `/api/crm/customers/{id}` | Yes | Delete customer |

**Query Parameters (List):**
- `q` — Search by name, email, phone
- `status` — Filter by status (active, inactive)
- `per_page` — Results per page (default: 15)
- `page` — Page number

**Create/Update Fields:**
- `name` (required), `email`, `phone`, `tax_id`, `industry`, `website`
- `address_line1`, `city`, `state`, `postal_code`, `country_id`
- `currency_code`, `status`, `notes`, `branch_id`

#### Contacts

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/crm/contacts` | Yes | List contacts |
| `POST` | `/api/crm/contacts` | Yes | Create contact |
| `GET` | `/api/crm/contacts/{id}` | Yes | Get contact |
| `PUT` | `/api/crm/contacts/{id}` | Yes | Update contact |
| `DELETE` | `/api/crm/contacts/{id}` | Yes | Delete contact |

**Query Parameters (List):**
- `customer_id` — Filter by customer
- `q` — Search by name, email

**Create/Update Fields:**
- `customer_id` (required), `first_name` (required), `last_name`, `title`
- `email`, `phone`, `mobile`, `is_primary`, `is_active`

#### Leads

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/crm/leads` | Yes | List leads |
| `POST` | `/api/crm/leads` | Yes | Create lead |
| `GET` | `/api/crm/leads/{id}` | Yes | Get lead |
| `PUT` | `/api/crm/leads/{id}` | Yes | Update lead |
| `DELETE` | `/api/crm/leads/{id}` | Yes | Delete lead |

**Query Parameters (List):**
- `q` — Search by company, contact, email
- `status` — Filter (new, contacted, qualified, proposal, won, lost)

**Create/Update Fields:**
- `company_name` (required), `contact_name`, `email`, `phone`, `source`
- `industry`, `product_interest`, `estimated_value`, `currency_code`
- `status`, `score`, `assigned_to`, `website`, `next_follow_up`, `notes`
- `country_id`, `branch_id`

#### Opportunities

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/crm/opportunities` | Yes | List opportunities |
| `POST` | `/api/crm/opportunities` | Yes | Create opportunity |
| `GET` | `/api/crm/opportunities/{id}` | Yes | Get opportunity |
| `PUT` | `/api/crm/opportunities/{id}` | Yes | Update opportunity |
| `DELETE` | `/api/crm/opportunities/{id}` | Yes | Delete opportunity |

**Query Parameters (List):**
- `q` — Search by name
- `stage` — Filter (qualification, needs_analysis, proposal, negotiation, won, lost)

**Create/Update Fields:**
- `name` (required), `customer_id` (required), `lead_id`, `expected_value`
- `currency_code`, `stage`, `probability`, `expected_close_date`
- `assigned_to`, `source`, `notes`, `branch_id`

---

### Sales Module

#### Quotations

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/sales/quotations` | Yes | List quotations |
| `POST` | `/api/sales/quotations` | Yes | Create quotation |
| `GET` | `/api/sales/quotations/{id}` | Yes | Get quotation |
| `PUT` | `/api/sales/quotations/{id}` | Yes | Update quotation |
| `DELETE` | `/api/sales/quotations/{id}` | Yes | Delete quotation |

**Query Parameters (List):**
- `q` — Search by quotation number
- `status` — Filter (draft, sent, accepted, rejected, expired, converted)

**Create/Update Fields:**
- `customer_id` (required), `currency_code`, `valid_until`
- `notes`, `terms`, `subtotal`, `discount`, `tax`, `total`, `status`

#### Sales Orders

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/sales/orders` | Yes | List orders |
| `POST` | `/api/sales/orders` | Yes | Create order |
| `GET` | `/api/sales/orders/{id}` | Yes | Get order |
| `PUT` | `/api/sales/orders/{id}` | Yes | Update order |
| `DELETE` | `/api/sales/orders/{id}` | Yes | Delete order |

**Query Parameters (List):**
- `q` — Search by order number
- `status` — Filter (draft, confirmed, processing, shipped, completed, cancelled)

**Create/Update Fields:**
- `customer_id` (required), `quotation_id`, `order_date`, `delivery_date`
- `subtotal`, `discount`, `tax`, `shipping`, `total`, `notes`, `status`, `payment_status`

#### Invoices

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/sales/invoices` | Yes | List invoices |
| `POST` | `/api/sales/invoices` | Yes | Create invoice |
| `GET` | `/api/sales/invoices/{id}` | Yes | Get invoice |
| `PUT` | `/api/sales/invoices/{id}` | Yes | Update invoice |
| `DELETE` | `/api/sales/invoices/{id}` | Yes | Delete invoice |

**Query Parameters (List):**
- `q` — Search by invoice number
- `status` — Filter (draft, sent, partial, paid, overdue, cancelled)

**Create/Update Fields:**
- `customer_id` (required), `sales_order_id`, `issue_date`, `due_date`
- `subtotal`, `discount`, `tax`, `total`, `paid_amount`, `notes`, `status`

#### Payments

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/sales/payments` | Yes | List payments |
| `POST` | `/api/sales/payments` | Yes | Create payment |
| `GET` | `/api/sales/payments/{id}` | Yes | Get payment |
| `PUT` | `/api/sales/payments/{id}` | Yes | Update payment |
| `DELETE` | `/api/sales/payments/{id}` | Yes | Delete payment |

**Query Parameters (List):**
- `q` — Search by payment number, reference
- `status` — Filter (pending, completed, failed, refunded)

**Create/Update Fields:**
- `customer_id` (required), `invoice_id`, `payment_date`, `amount` (required)
- `method` (required: cash, bank, cheque, card, upi, other), `reference`, `notes`, `status`

---

### Inventory Module

#### Products

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/inventory/products` | Yes | List products |
| `POST` | `/api/inventory/products` | Yes | Create product |
| `GET` | `/api/inventory/products/{id}` | Yes | Get product |
| `PUT` | `/api/inventory/products/{id}` | Yes | Update product |
| `DELETE` | `/api/inventory/products/{id}` | Yes | Delete product |

**Query Parameters (List):**
- `q` — Search by name, SKU
- `status` — Filter (active, inactive)
- `category` — Filter by category

**Create/Update Fields:**
- `sku` (required, unique), `name` (required), `category`, `description`
- `unit_id`, `purchase_price`, `selling_price`, `tax_rate_id`
- `reorder_level`, `supplier_id`, `status`, `notes`

**Show Response (extra fields):**
- `available_stock` — Current stock quantity
- `is_low_stock` — Boolean

#### Warehouses

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/inventory/warehouses` | Yes | List warehouses |
| `POST` | `/api/inventory/warehouses` | Yes | Create warehouse |
| `GET` | `/api/inventory/warehouses/{id}` | Yes | Get warehouse |
| `PUT` | `/api/inventory/warehouses/{id}` | Yes | Update warehouse |
| `DELETE` | `/api/inventory/warehouses/{id}` | Yes | Delete warehouse |

**Create/Update Fields:**
- `code` (required, unique), `name` (required), `location`
- `manager_id`, `capacity`, `is_default`, `status`

#### Stock Movements

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/inventory/stock-movements` | Yes | List movements |
| `POST` | `/api/inventory/stock-movements` | Yes | Record movement |
| `GET` | `/api/inventory/stock-movements/{id}` | Yes | Get movement |
| `DELETE` | `/api/inventory/stock-movements/{id}` | Yes | Delete movement |

**Query Parameters (List):**
- `product_id`, `warehouse_id`, `type` (in, out, transfer, adjustment)

**Create Fields:**
- `product_id` (required), `warehouse_id` (required), `type` (required)
- `quantity` (required), `unit_cost`, `note`

---

### Procurement Module

#### Suppliers

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/procurement/suppliers` | Yes | List suppliers |
| `POST` | `/api/procurement/suppliers` | Yes | Create supplier |
| `GET` | `/api/procurement/suppliers/{id}` | Yes | Get supplier |
| `PUT` | `/api/procurement/suppliers/{id}` | Yes | Update supplier |
| `DELETE` | `/api/procurement/suppliers/{id}` | Yes | Delete supplier |

**Query Parameters (List):**
- `q` — Search by name, email, code
- `status` — Filter (pending, verified, approved, rejected, blocked)

**Create/Update Fields:**
- `name` (required), `supplier_code`, `tax_id`, `contact_person`
- `email`, `phone`, `website`, `address_line1`, `city`, `state`
- `postal_code`, `country_id`, `currency_code`, `payment_terms`, `notes`, `status`

#### Purchase Orders

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/procurement/purchase-orders` | Yes | List purchase orders |
| `POST` | `/api/procurement/purchase-orders` | Yes | Create purchase order |
| `GET` | `/api/procurement/purchase-orders/{id}` | Yes | Get purchase order |
| `PUT` | `/api/procurement/purchase-orders/{id}` | Yes | Update purchase order |
| `DELETE` | `/api/procurement/purchase-orders/{id}` | Yes | Delete purchase order |

**Query Parameters (List):**
- `q` — Search by PO number
- `status` — Filter (draft, pending, approved, ordered, partially_received, received, cancelled)

**Create/Update Fields:**
- `supplier_id` (required), `rfq_id`, `order_date`, `expected_date`
- `subtotal`, `discount`, `tax`, `shipping`, `total`
- `payment_terms`, `notes`, `status`, `payment_status`

---

### Finance Module

#### Accounts (Chart of Accounts)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/finance/accounts` | Yes | List accounts |
| `POST` | `/api/finance/accounts` | Yes | Create account |
| `GET` | `/api/finance/accounts/{id}` | Yes | Get account |
| `PUT` | `/api/finance/accounts/{id}` | Yes | Update account |
| `DELETE` | `/api/finance/accounts/{id}` | Yes | Delete account |

**Query Parameters (List):**
- `type` — Filter (asset, liability, equity, income, expense)
- `is_active` — Filter by active status

**Create/Update Fields:**
- `code` (required, unique), `name` (required), `type` (required)
- `parent_id`, `is_active`, `description`

**Show Response (extra fields):**
- `balance` — Current account balance

**Delete Restrictions:**
- Cannot delete accounts with journal entries (422)

#### Journal Entries

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/finance/journal-entries` | Yes | List journal entries |
| `POST` | `/api/finance/journal-entries` | Yes | Create journal entry |
| `GET` | `/api/finance/journal-entries/{id}` | Yes | Get journal entry |
| `PUT` | `/api/finance/journal-entries/{id}` | Yes | Update journal entry |
| `DELETE` | `/api/finance/journal-entries/{id}` | Yes | Delete journal entry |

**Query Parameters (List):**
- `q` — Search by entry number, description
- `status` — Filter (draft, posted)

**Create Fields:**
- `date` (required), `description`
- `lines` (required, min 2 items):
  - `account_id` (required), `debit`, `credit`, `description`

**Validation Rules:**
- Debit total must equal credit total (balanced entry)
- Posted entries cannot be updated or deleted

---

### AI Module

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/ai/conversations` | Yes | List conversations |
| `POST` | `/api/ai/conversations` | Yes | Create conversation |
| `GET` | `/api/ai/conversations/{id}` | Yes | Get conversation |
| `POST` | `/api/ai/conversations/{id}/messages` | Yes | Send message |
| `GET` | `/api/ai/insights` | Yes | Get AI insights |

**Create Conversation Fields:**
- `title` (required), `skill_slug`, `context` (array)

**Send Message Fields:**
- `content` (required, max 10000 chars)

**Insights Response:**
```json
{
  "success": true,
  "message": "Insights retrieved",
  "data": {
    "total_customers": 42,
    "total_leads": 15,
    "total_invoices": 87,
    "total_products": 23,
    "open_tickets": 3
  }
}
```

---

### Support Module

#### Tickets

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/support/tickets` | Yes | List tickets |
| `POST` | `/api/support/tickets` | Yes | Create ticket |
| `GET` | `/api/support/tickets/{id}` | Yes | Get ticket |
| `PUT` | `/api/support/tickets/{id}` | Yes | Update ticket |
| `DELETE` | `/api/support/tickets/{id}` | Yes | Delete ticket |

**Query Parameters (List):**
- `q` — Search by ticket number, subject
- `status` — Filter (open, investigating, in_progress, resolved, closed)
- `priority` — Filter (low, medium, high, urgent)

**Create/Update Fields:**
- `subject` (required), `description` (required), `module`
- `priority` (low, medium, high, urgent)
- `category` (bug, feature_request, question, installation, other)
- `assigned_to`, `resolved_at`, `closed_at`, `customer_satisfaction` (1-5)

---

## Example Usage

### cURL — Create a Token

```bash
curl -X POST http://localhost/api/tokens \
  -H "Content-Type: application/json" \
  -d '{"name": "my-api-token", "abilities": ["*"]}'
```

### cURL — List Customers

```bash
curl -X GET http://localhost/api/crm/customers \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### cURL — Create a Customer

```bash
curl -X POST http://localhost/api/crm/customers \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Acme Corp",
    "email": "info@acme.com",
    "phone": "+1-555-0100",
    "industry": "Technology"
  }'
```

### cURL — Search Products

```bash
curl -X GET "http://localhost/api/inventory/products?q=laptop&status=active" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### cURL — Create a Journal Entry

```bash
curl -X POST http://localhost/api/finance/journal-entries \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "date": "2026-08-20",
    "description": "Office supplies purchase",
    "lines": [
      {"account_id": 1, "debit": 100.00, "credit": 0},
      {"account_id": 5, "debit": 0, "credit": 100.00}
    ]
  }'
```

---

## Error Codes

| Error | HTTP Status | Description |
|-------|-------------|-------------|
| `Validation error` | 422 | Request data failed validation |
| `Unauthorized` | 401 | No or invalid bearer token |
| `Forbidden` | 403 | Token lacks required abilities |
| `Not found` | 404 | Resource does not exist |
| `Rate limit exceeded` | 429 | Too many requests |
| `Cannot delete account with journal entries` | 422 | Account has transaction history |
| `Cannot update/delete a posted journal entry` | 422 | Entry is already posted |

---

## Architecture

```
app/Http/Controllers/Api/
  ApiController.php          — Base controller with shared response helpers
  TokenController.php        — Sanctum token management
  CustomerController.php     — CRM: customers
  ContactController.php      — CRM: contacts
  LeadController.php         — CRM: leads
  OpportunityController.php  — CRM: opportunities
  QuotationController.php    — Sales: quotations
  SalesOrderController.php   — Sales: orders
  InvoiceController.php      — Sales: invoices
  PaymentController.php      — Sales: payments
  ProductController.php      — Inventory: products
  WarehouseController.php    — Inventory: warehouses
  StockMovementController.php — Inventory: stock movements
  SupplierController.php     — Procurement: suppliers
  PurchaseOrderController.php — Procurement: purchase orders
  AccountController.php      — Finance: chart of accounts
  JournalEntryController.php — Finance: journal entries
  AiController.php           — AI: conversations & insights
  SupportTicketController.php — Support: tickets

routes/api.php               — All API route definitions
config/sanctum.php           — Sanctum configuration
```
