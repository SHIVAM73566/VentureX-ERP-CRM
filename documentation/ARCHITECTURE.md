# Architecture Documentation

Technical architecture overview of VentureX ERP & CRM.

---

## Application Overview

VentureX ERP & CRM is a monolithic, server-rendered web application built on the Laravel framework. It uses Blade templates for server-side rendering, Alpine.js for client-side interactivity, and Livewire for dynamic components without custom JavaScript.

### Design Principles

- **Server-rendered:** All pages rendered on the server via Blade
- **Progressive enhancement:** Alpine.js adds interactivity without requiring JavaScript
- **Component-based:** Livewire provides reusable, reactive components
- **Convention over configuration:** Follows Laravel conventions
- **Role-based access:** All features gated by permissions

---

## Technology Stack

| Layer | Technology | Purpose |
|-------|------------|---------|
| HTTP Server | Nginx/Apache | Request handling, static files |
| Application | PHP 8.3+ | Business logic |
| Framework | Laravel 13 | MVC, routing, ORM, queue |
| Templating | Blade | Server-side HTML rendering |
| Interactivity | Alpine.js 3 | Client-side JavaScript |
| Dynamic UI | Livewire | Server-driven components |
| Styling | Tailwind CSS v4 | Utility-first CSS |
| Build | Vite 8 | Asset compilation |
| Database | MySQL 8.0+ | Data storage |
| Cache | Redis | Session, cache, queue |
| Queue | Redis/Laravel Queue | Background job processing |
| Permissions | Spatie Permission | RBAC |

---

## Directory Structure

```
VentureX-ERP/
—œ—€—€ app/
—‚   —œ—€—€ Console/              # Artisan commands
—‚   —œ—€—€ Exceptions/           # Exception handlers
—‚   —œ—€—€ Http/
—‚   —‚   —œ—€—€ Controllers/      # Route controllers
—‚   —‚   —‚   —œ—€—€ CRM/
—‚   —‚   —‚   —œ—€—€ Sales/
—‚   —‚   —‚   —œ—€—€ Procurement/
—‚   —‚   —‚   —œ—€—€ Inventory/
—‚   —‚   —‚   —œ—€—€ Logistics/
—‚   —‚   —‚   —œ—€—€ Finance/
—‚   —‚   —‚   —œ—€—€ AI/
—‚   —‚   —‚   —”—€—€ Admin/
—‚   —‚   —œ—€—€ Middleware/        # Request middleware
—‚   —‚   —”—€—€ Requests/         # Form request validation
—‚   —œ—€—€ Livewire/             # Livewire components
—‚   —‚   —œ—€—€ CRM/
—‚   —‚   —œ—€—€ Sales/
—‚   —‚   —œ—€—€ Procurement/
—‚   —‚   —œ—€—€ Inventory/
—‚   —‚   —œ—€—€ Logistics/
—‚   —‚   —œ—€—€ Finance/
—‚   —‚   —œ—€—€ AI/
—‚   —‚   —”—€—€ Admin/
—‚   —œ—€—€ Models/               # Eloquent models
—‚   —”—€—€ Providers/            # Service providers
—œ—€—€ bootstrap/                # Application bootstrap
—œ—€—€ config/                   # Configuration files
—œ—€—€ database/
—‚   —œ—€—€ migrations/           # Database migrations
—‚   —”—€—€ seeders/              # Database seeders
—œ—€—€ docs/                     # Documentation
—œ—€—€ lang/                     # Language files
—œ—€—€ public/                   # Web root
—‚   —œ—€—€ build/                # Compiled assets (Vite)
—‚   —”—€—€ storage/              # Public storage symlink
—œ—€—€ resources/
—‚   —œ—€—€ css/                  # CSS source (Tailwind)
—‚   —œ—€—€ js/                   # JavaScript source
—‚   —”—€—€ views/                # Blade templates
—‚       —œ—€—€ components/       # Reusable components
—‚       —œ—€—€ layouts/          # Layout templates
—‚       —œ—€—€ crm/              # CRM views
—‚       —œ—€—€ sales/            # Sales views
—‚       —œ—€—€ procurement/      # Procurement views
—‚       —œ—€—€ inventory/        # Inventory views
—‚       —œ—€—€ logistics/        # Logistics views
—‚       —œ—€—€ finance/          # Finance views
—‚       —œ—€—€ ai/               # AI views
—‚       —”—€—€ admin/            # Admin views
—œ—€—€ routes/                   # Route definitions
—‚   —”—€—€ web.php               # All web routes
—œ—€—€ storage/                  # Application storage
—‚   —œ—€—€ app/                  # Private uploads
—‚   —œ—€—€ framework/            # Framework cache
—‚   —”—€—€ logs/                 # Application logs
—œ—€—€ tests/                    # Test files
—œ—€—€ .env.example              # Environment template
—œ—€—€ composer.json             # PHP dependencies
—œ—€—€ package.json              # Node.js dependencies
—œ—€—€ vite.config.js            # Vite configuration
—”—€—€ tailwind.config.js        # Tailwind configuration
```

---

## Database Schema Overview

### Core Tables

#### Authentication & Users

```
users
—œ—€—€ id (bigint, PK)
—œ—€—€ name (string)
—œ—€—€ email (string, unique)
—œ—€—€ password (string, hashed)
—œ—€—€ mfa_secret (string, nullable)
—œ—€—€ mfa_enabled (boolean)
—œ—€—€ status (enum: active, inactive)
—œ—€—€ remember_token (string, nullable)
—œ—€—€ created_at (timestamp)
—œ—€—€ updated_at (timestamp)
—”—€—€ deleted_at (timestamp, nullable)

roles (Spatie)
—œ—€—€ id (bigint, PK)
—œ—€—€ name (string, unique)
—œ—€—€ guard_name (string)
—”—€—€ created_at, updated_at

permissions (Spatie)
—œ—€—€ id (bigint, PK)
—œ—€—€ name (string, unique)
—œ—€—€ guard_name (string)
—”—€—€ created_at, updated_at

model_has_roles (Spatie)
—œ—€—€ role_id (bigint, FK)
—”—€—€ model_id (bigint, FK)

model_has_permissions (Spatie)
—œ—€—€ permission_id (bigint, FK)
—”—€—€ model_id (bigint, FK)
```

#### Companies

```
companies
—œ—€—€ id (bigint, PK)
—œ—€—€ name (string)
—œ—€—€ legal_name (string, nullable)
—œ—€—€ registration_number (string, nullable)
—œ—€—€ address (text, nullable)
—œ—€—€ city (string, nullable)
—œ—€—€ state (string, nullable)
—œ—€—€ country (string, nullable)
—œ—€—€ postal_code (string, nullable)
—œ—€—€ phone (string, nullable)
—œ—€—€ email (string, nullable)
—œ—€—€ website (string, nullable)
—œ—€—€ tax_id (string, nullable)
—œ—€—€ vat_number (string, nullable)
—œ—€—€ currency (string, default: USD)
—œ—€—€ fiscal_year_start (date, nullable)
—œ—€—€ status (enum: active, inactive)
—œ—€—€ created_at, updated_at, deleted_at
```

#### CRM Module

```
customers
—œ—€—€ id, company_id, name, email, phone, address, city, state, country
—œ—€—€ postal_code, website, notes, tags, status
—œ—€—€ created_at, updated_at, deleted_at

leads
—œ—€—€ id, company_id, customer_id, name, email, phone, source
—œ—€—€ status (new, contacted, qualified, unqualified)
—œ—€—€ assigned_to, value, description
—œ—€—€ created_at, updated_at, deleted_at

opportunities
—œ—€—€ id, company_id, customer_id, lead_id, name, stage
—œ—€—€ value, expected_close_date, probability
—œ—€—€ assigned_to, description
—œ—€—€ created_at, updated_at, deleted_at

contacts
—œ—€—€ id, company_id, customer_id, name, email, phone
—œ—€—€ job_title, department, notes
—œ—€—€ created_at, updated_at, deleted_at

activities
—œ—€—€ id, company_id, type, subject, description
—œ—€—€ related_type, related_id (polymorphic)
—œ—€—€ assigned_to, due_date, completed_at
—œ—€—€ created_at, updated_at, deleted_at
```

#### Sales Module

```
quotations
—œ—€—€ id, company_id, customer_id, quotation_number
—œ—€—€ status (draft, sent, accepted, rejected, expired)
—œ—€—€ subtotal, tax_amount, total, discount
—œ—€—€ valid_until, notes
—œ—€—€ created_by, created_at, updated_at, deleted_at

quotation_items
—œ—€—€ id, quotation_id, product_id, description
—œ—€—€ quantity, unit_price, discount, tax_rate
—œ—€—€ total, created_at, updated_at

sales_orders
—œ—€—€ id, company_id, customer_id, quotation_id, order_number
—œ—€—€ status, subtotal, tax_amount, total
—œ—€—€ notes, created_by
—œ—€—€ created_at, updated_at, deleted_at

invoices
—œ—€—€ id, company_id, customer_id, sales_order_id, invoice_number
—œ—€—€ status (draft, sent, partially_paid, paid, overdue, void)
—œ—€—€ subtotal, tax_amount, total, amount_paid
—œ—€—€ due_date, notes, created_by
—œ—€—€ created_at, updated_at, deleted_at

payments
—œ—€—€ id, company_id, invoice_id, amount, payment_method
—œ—€—€ reference_number, notes, paid_at
—œ—€—€ created_by, created_at, updated_at, deleted_at
```

#### Procurement Module

```
suppliers
—œ—€—€ id, company_id, name, email, phone, address
—œ—€—€ city, state, country, website, notes
—œ—€—€ rating, status
—œ—€—€ created_at, updated_at, deleted_at

supplier_offers
—œ—€—€ id, company_id, supplier_id, offer_number
—œ—€—€ status, subtotal, tax_amount, total
—œ—€—€ valid_until, notes
—œ—€—€ created_at, updated_at, deleted_at

rfqs
—œ—€—€ id, company_id, rfq_number, title, description
—œ—€—€ status, due_date, created_by
—œ—€—€ created_at, updated_at, deleted_at

purchase_requisitions
—œ—€—€ id, company_id, requisition_number, title, description
—œ—€—€ status (draft, pending_approval, approved, rejected)
—œ—€—€ requested_by, approved_by, department
—œ—€—€ created_at, updated_at, deleted_at

purchase_orders
—œ—€—€ id, company_id, supplier_id, po_number
—œ—€—€ status (draft, pending_approval, approved, sent, received, closed)
—œ—€—€ subtotal, tax_amount, total
—œ—€—€ expected_delivery_date, notes
—œ—€—€ created_by, approved_by
—œ—€—€ created_at, updated_at, deleted_at
```

#### Inventory Module

```
products
—œ—€—€ id, company_id, sku, name, description
—œ—€—€ category, type, unit_of_measure
—œ—€—€ cost_price, selling_price
—œ—€—€ minimum_stock, maximum_stock, reorder_point
—œ—€—€ status, created_at, updated_at, deleted_at

warehouses
—œ—€—€ id, company_id, name, code, address
—œ—€—€ city, state, country, status
—œ—€—€ created_at, updated_at, deleted_at

stock_movements
—œ—€—€ id, company_id, product_id, warehouse_id
—œ—€—€ type (purchase, sale, transfer, adjustment, return, write_off)
—œ—€—€ quantity, reference_type, reference_id
—œ—€—€ notes, created_by
—œ—€—€ created_at, updated_at
```

#### Logistics Module

```
shipments
—œ—€—€ id, company_id, sales_order_id, shipment_number
—œ—€—€ status, carrier, tracking_number
—œ—€—€ shipped_at, delivered_at
—œ—€—€ notes, created_by
—œ—€—€ created_at, updated_at, deleted_at

containers
—œ—€—€ id, company_id, container_number, shipment_id
—œ—€—€ type, status, carrier
—œ—€—€ departure_date, arrival_date
—œ—€—€ created_at, updated_at, deleted_at

landed_costs
—œ—€—€ id, company_id, purchase_order_id
—œ—€—€ shipping_cost, duty, insurance, other_fees
—œ—€—€ total_landed_cost, allocated
—œ—€—€ created_at, updated_at, deleted_at
```

#### Finance Module

```
accounts
—œ—€—€ id, company_id, code, name, type (asset, liability, equity, revenue, expense)
—œ—€—€ description, status, parent_id
—œ—€—€ created_at, updated_at, deleted_at

journal_entries
—œ—€—€ id, company_id, entry_number, date
—œ—€—€ description, reference_type, reference_id
—œ—€—€ status (draft, posted, void)
—œ—€—€ created_by, posted_by
—œ—€—€ created_at, updated_at, deleted_at

journal_entry_lines
—œ—€—€ id, journal_entry_id, account_id
—œ—€—€ debit, credit, description
—œ—€—€ created_at, updated_at
```

#### Admin Module

```
audit_logs
—œ—€—€ id, company_id, user_id, event
—œ—€—€ auditable_type, auditable_id
—œ—€—€ old_values (json), new_values (json)
—œ—€—€ ip_address, user_agent
—œ—€—€ created_at

approvals
—œ—€—€ id, company_id, approvable_type, approvable_id
—œ—€—€ status, level, approver_id
—œ—€—€ notes, approved_at
—œ—€—€ created_at, updated_at

documents
—œ—€—€ id, company_id, name, file_path
—œ—€—€ file_type, file_size, mime_type
—œ—€—€ documentable_type, documentable_id
—œ—€—€ uploaded_by, created_at, updated_at
```

---

## Request Lifecycle

```
1. Browser sends HTTP request
2. Nginx receives request
3. Forwards PHP requests to PHP-FPM
4. Laravel Router matches URL to route
5. Middleware chain executes:
   - TrustProxies
   - PreventRequestsDuringMaintenance
   - ValidatePostSize
   - TrimStrings
   - ConvertEmptyStringsToNull
   - Authenticate (session check)
   - Authorize (permission check)
6. Controller or Livewire component executes
7. Eloquent queries MySQL database
8. Data returned to controller
9. Blade template renders HTML
10. Alpine.js initializes client-side behavior
11. HTML response sent to browser
12. Browser renders page
```

---

## Livewire Architecture

Livewire components handle dynamic UI without custom JavaScript:

```
Browser Event â†’ Alpine.js â†’ Livewire AJAX â†’ /livewire/update
â†’ Livewire Component Method â†’ Eloquent Query
â†’ Component Re-render â†’ HTML Diff â†’ Browser DOM Update
```

**Component Organization:**

```
app/Livewire/
—œ—€—€ CRM/
—‚   —œ—€—€ CustomerList.php      # List with search/filter
—‚   —œ—€—€ CustomerForm.php      # Create/edit form
—‚   —œ—€—€ CustomerDetail.php    # Detail view
—‚   —œ—€—€ PipelineBoard.php     # Drag-and-drop pipeline
—‚   —”—€—€ ActivityLog.php       # Activity feed
—œ—€—€ Sales/
—‚   —œ—€—€ QuotationForm.php
—‚   —œ—€—€ InvoiceList.php
—‚   —”—€—€ PaymentForm.php
—œ—€—€ Procurement/
—‚   —œ—€—€ PurchaseOrderForm.php
—‚   —œ—€—€ ApprovalWorkflow.php
—‚   —”—€—€ RFQManager.php
—œ—€—€ Inventory/
—‚   —œ—€—€ ProductList.php
—‚   —”—€—€ StockAdjustment.php
—œ—€—€ Finance/
—‚   —œ—€—€ JournalEntryForm.php
—‚   —”—€—€ AccountList.php
—œ—€—€ AI/
—‚   —œ—€—€ ChatInterface.php
—‚   —”—€—€ AnalysisPanel.php
—”—€—€ Admin/
    —œ—€—€ UserManagement.php
    —œ—€—€ RolePermissions.php
    —”—€—€ SystemSettings.php
```

---

## Caching Strategy

| Cache Target | Driver | TTL | Invalidation |
|-------------|--------|-----|--------------|
| Configuration | File | Permanent | Manual clear |
| Routes | File | Permanent | Manual clear |
| Views | File | Permanent | Manual clear |
| Sessions | Redis | 120 min | Session end |
| Query cache | Redis | 5 min | Model save |
| Permission cache | Redis | 24 hours | Role/permission change |

---

## Queue Architecture

| Queue | Purpose | Worker Config |
|-------|---------|---------------|
| default | General background tasks | --tries=3 --sleep=3 |
| mail | Email sending | --tries=3 --max-time=3600 |
| imports | CSV/Excel imports | --tries=1 --timeout=300 |
| exports | Data exports | --tries=1 --timeout=300 |

---

## File Storage Structure

```
storage/
—œ—€—€ app/
—‚   —”—€—€ public/              # Publicly accessible uploads
—‚       —œ—€—€ avatars/         # User profile pictures
—‚       —œ—€—€ documents/       # Uploaded documents
—‚       —œ—€—€ imports/         # Imported files
—‚       —”—€—€ exports/         # Generated exports
—œ—€—€ framework/
—‚   —œ—€—€ cache/               # Application cache
—‚   —œ—€—€ sessions/            # Session files
—‚   —”—€—€ views/               # Compiled Blade views
—”—€—€ logs/
    —œ—€—€ laravel.log          # Application log
    —”—€—€ worker.log           # Queue worker log
```

---

## Error Handling

### Exception Flow

```
Exception thrown â†’ Laravel Exception Handler
—œ—€—€ HTTP Exception â†’ Render appropriate HTTP response
—œ—€—€ Validation Exception â†’ Redirect with errors
—œ—€—€ Authentication Exception â†’ Redirect to login
—œ—€—€ Authorization Exception â†’ 403 response
—”—€—€ Uncaught Exception â†’ 500 response + log
```

### Logging

- Application logs: `storage/logs/laravel.log`
- Queue worker logs: `storage/logs/worker.log`
- Nginx access/error logs: `/var/log/nginx/`
- PHP-FPM logs: `/var/log/php-fpm/`

---

## Security Layers

```
Request â†’ Nginx (SSL, security headers)
â†’ PHP (input validation)
â†’ Laravel Middleware (CSRF, auth, permissions)
â†’ Controller (form validation, authorization)
â†’ Eloquent (parameterized queries)
â†’ Database (user privileges)
```

---

## Scaling Considerations

### Horizontal Scaling

- Stateless application servers behind load balancer
- Shared Redis for sessions/cache/queue
- Shared file storage (S3 or NFS)
- Database replication (read replicas)

### Vertical Scaling

- Increase PHP-FPM workers
- Increase Redis memory
- Increase MySQL connections
- Add OPcache

### Current Architecture Limits

- Single database (no sharding)
- File-based sessions (not distributed)
- Synchronous rendering (no CDN for HTML)

---

## Future Architecture Plans

- REST API layer with Laravel Sanctum
- WebSocket support for real-time updates
- Microservice extraction for AI module
- Elasticsearch integration for advanced search
- Multi-tenant architecture improvements
