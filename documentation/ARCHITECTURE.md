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
| Application | PHP 8.2+ | Business logic |
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
â”œâ”€â”€ app/
â”‚   â”œâ”€â”€ Console/              # Artisan commands
â”‚   â”œâ”€â”€ Exceptions/           # Exception handlers
â”‚   â”œâ”€â”€ Http/
â”‚   â”‚   â”œâ”€â”€ Controllers/      # Route controllers
â”‚   â”‚   â”‚   â”œâ”€â”€ CRM/
â”‚   â”‚   â”‚   â”œâ”€â”€ Sales/
â”‚   â”‚   â”‚   â”œâ”€â”€ Procurement/
â”‚   â”‚   â”‚   â”œâ”€â”€ Inventory/
â”‚   â”‚   â”‚   â”œâ”€â”€ Logistics/
â”‚   â”‚   â”‚   â”œâ”€â”€ Finance/
â”‚   â”‚   â”‚   â”œâ”€â”€ AI/
â”‚   â”‚   â”‚   â””â”€â”€ Admin/
â”‚   â”‚   â”œâ”€â”€ Middleware/        # Request middleware
â”‚   â”‚   â””â”€â”€ Requests/         # Form request validation
â”‚   â”œâ”€â”€ Livewire/             # Livewire components
â”‚   â”‚   â”œâ”€â”€ CRM/
â”‚   â”‚   â”œâ”€â”€ Sales/
â”‚   â”‚   â”œâ”€â”€ Procurement/
â”‚   â”‚   â”œâ”€â”€ Inventory/
â”‚   â”‚   â”œâ”€â”€ Logistics/
â”‚   â”‚   â”œâ”€â”€ Finance/
â”‚   â”‚   â”œâ”€â”€ AI/
â”‚   â”‚   â””â”€â”€ Admin/
â”‚   â”œâ”€â”€ Models/               # Eloquent models
â”‚   â””â”€â”€ Providers/            # Service providers
â”œâ”€â”€ bootstrap/                # Application bootstrap
â”œâ”€â”€ config/                   # Configuration files
â”œâ”€â”€ database/
â”‚   â”œâ”€â”€ migrations/           # Database migrations
â”‚   â””â”€â”€ seeders/              # Database seeders
â”œâ”€â”€ docs/                     # Documentation
â”œâ”€â”€ lang/                     # Language files
â”œâ”€â”€ public/                   # Web root
â”‚   â”œâ”€â”€ build/                # Compiled assets (Vite)
â”‚   â””â”€â”€ storage/              # Public storage symlink
â”œâ”€â”€ resources/
â”‚   â”œâ”€â”€ css/                  # CSS source (Tailwind)
â”‚   â”œâ”€â”€ js/                   # JavaScript source
â”‚   â””â”€â”€ views/                # Blade templates
â”‚       â”œâ”€â”€ components/       # Reusable components
â”‚       â”œâ”€â”€ layouts/          # Layout templates
â”‚       â”œâ”€â”€ crm/              # CRM views
â”‚       â”œâ”€â”€ sales/            # Sales views
â”‚       â”œâ”€â”€ procurement/      # Procurement views
â”‚       â”œâ”€â”€ inventory/        # Inventory views
â”‚       â”œâ”€â”€ logistics/        # Logistics views
â”‚       â”œâ”€â”€ finance/          # Finance views
â”‚       â”œâ”€â”€ ai/               # AI views
â”‚       â””â”€â”€ admin/            # Admin views
â”œâ”€â”€ routes/                   # Route definitions
â”‚   â””â”€â”€ web.php               # All web routes
â”œâ”€â”€ storage/                  # Application storage
â”‚   â”œâ”€â”€ app/                  # Private uploads
â”‚   â”œâ”€â”€ framework/            # Framework cache
â”‚   â””â”€â”€ logs/                 # Application logs
â”œâ”€â”€ tests/                    # Test files
â”œâ”€â”€ .env.example              # Environment template
â”œâ”€â”€ composer.json             # PHP dependencies
â”œâ”€â”€ package.json              # Node.js dependencies
â”œâ”€â”€ vite.config.js            # Vite configuration
â””â”€â”€ tailwind.config.js        # Tailwind configuration
```

---

## Database Schema Overview

### Core Tables

#### Authentication & Users

```
users
â”œâ”€â”€ id (bigint, PK)
â”œâ”€â”€ name (string)
â”œâ”€â”€ email (string, unique)
â”œâ”€â”€ password (string, hashed)
â”œâ”€â”€ mfa_secret (string, nullable)
â”œâ”€â”€ mfa_enabled (boolean)
â”œâ”€â”€ status (enum: active, inactive)
â”œâ”€â”€ remember_token (string, nullable)
â”œâ”€â”€ created_at (timestamp)
â”œâ”€â”€ updated_at (timestamp)
â””â”€â”€ deleted_at (timestamp, nullable)

roles (Spatie)
â”œâ”€â”€ id (bigint, PK)
â”œâ”€â”€ name (string, unique)
â”œâ”€â”€ guard_name (string)
â””â”€â”€ created_at, updated_at

permissions (Spatie)
â”œâ”€â”€ id (bigint, PK)
â”œâ”€â”€ name (string, unique)
â”œâ”€â”€ guard_name (string)
â””â”€â”€ created_at, updated_at

model_has_roles (Spatie)
â”œâ”€â”€ role_id (bigint, FK)
â””â”€â”€ model_id (bigint, FK)

model_has_permissions (Spatie)
â”œâ”€â”€ permission_id (bigint, FK)
â””â”€â”€ model_id (bigint, FK)
```

#### Companies

```
companies
â”œâ”€â”€ id (bigint, PK)
â”œâ”€â”€ name (string)
â”œâ”€â”€ legal_name (string, nullable)
â”œâ”€â”€ registration_number (string, nullable)
â”œâ”€â”€ address (text, nullable)
â”œâ”€â”€ city (string, nullable)
â”œâ”€â”€ state (string, nullable)
â”œâ”€â”€ country (string, nullable)
â”œâ”€â”€ postal_code (string, nullable)
â”œâ”€â”€ phone (string, nullable)
â”œâ”€â”€ email (string, nullable)
â”œâ”€â”€ website (string, nullable)
â”œâ”€â”€ tax_id (string, nullable)
â”œâ”€â”€ vat_number (string, nullable)
â”œâ”€â”€ currency (string, default: USD)
â”œâ”€â”€ fiscal_year_start (date, nullable)
â”œâ”€â”€ status (enum: active, inactive)
â”œâ”€â”€ created_at, updated_at, deleted_at
```

#### CRM Module

```
customers
â”œâ”€â”€ id, company_id, name, email, phone, address, city, state, country
â”œâ”€â”€ postal_code, website, notes, tags, status
â”œâ”€â”€ created_at, updated_at, deleted_at

leads
â”œâ”€â”€ id, company_id, customer_id, name, email, phone, source
â”œâ”€â”€ status (new, contacted, qualified, unqualified)
â”œâ”€â”€ assigned_to, value, description
â”œâ”€â”€ created_at, updated_at, deleted_at

opportunities
â”œâ”€â”€ id, company_id, customer_id, lead_id, name, stage
â”œâ”€â”€ value, expected_close_date, probability
â”œâ”€â”€ assigned_to, description
â”œâ”€â”€ created_at, updated_at, deleted_at

contacts
â”œâ”€â”€ id, company_id, customer_id, name, email, phone
â”œâ”€â”€ job_title, department, notes
â”œâ”€â”€ created_at, updated_at, deleted_at

activities
â”œâ”€â”€ id, company_id, type, subject, description
â”œâ”€â”€ related_type, related_id (polymorphic)
â”œâ”€â”€ assigned_to, due_date, completed_at
â”œâ”€â”€ created_at, updated_at, deleted_at
```

#### Sales Module

```
quotations
â”œâ”€â”€ id, company_id, customer_id, quotation_number
â”œâ”€â”€ status (draft, sent, accepted, rejected, expired)
â”œâ”€â”€ subtotal, tax_amount, total, discount
â”œâ”€â”€ valid_until, notes
â”œâ”€â”€ created_by, created_at, updated_at, deleted_at

quotation_items
â”œâ”€â”€ id, quotation_id, product_id, description
â”œâ”€â”€ quantity, unit_price, discount, tax_rate
â”œâ”€â”€ total, created_at, updated_at

sales_orders
â”œâ”€â”€ id, company_id, customer_id, quotation_id, order_number
â”œâ”€â”€ status, subtotal, tax_amount, total
â”œâ”€â”€ notes, created_by
â”œâ”€â”€ created_at, updated_at, deleted_at

invoices
â”œâ”€â”€ id, company_id, customer_id, sales_order_id, invoice_number
â”œâ”€â”€ status (draft, sent, partially_paid, paid, overdue, void)
â”œâ”€â”€ subtotal, tax_amount, total, amount_paid
â”œâ”€â”€ due_date, notes, created_by
â”œâ”€â”€ created_at, updated_at, deleted_at

payments
â”œâ”€â”€ id, company_id, invoice_id, amount, payment_method
â”œâ”€â”€ reference_number, notes, paid_at
â”œâ”€â”€ created_by, created_at, updated_at, deleted_at
```

#### Procurement Module

```
suppliers
â”œâ”€â”€ id, company_id, name, email, phone, address
â”œâ”€â”€ city, state, country, website, notes
â”œâ”€â”€ rating, status
â”œâ”€â”€ created_at, updated_at, deleted_at

supplier_offers
â”œâ”€â”€ id, company_id, supplier_id, offer_number
â”œâ”€â”€ status, subtotal, tax_amount, total
â”œâ”€â”€ valid_until, notes
â”œâ”€â”€ created_at, updated_at, deleted_at

rfqs
â”œâ”€â”€ id, company_id, rfq_number, title, description
â”œâ”€â”€ status, due_date, created_by
â”œâ”€â”€ created_at, updated_at, deleted_at

purchase_requisitions
â”œâ”€â”€ id, company_id, requisition_number, title, description
â”œâ”€â”€ status (draft, pending_approval, approved, rejected)
â”œâ”€â”€ requested_by, approved_by, department
â”œâ”€â”€ created_at, updated_at, deleted_at

purchase_orders
â”œâ”€â”€ id, company_id, supplier_id, po_number
â”œâ”€â”€ status (draft, pending_approval, approved, sent, received, closed)
â”œâ”€â”€ subtotal, tax_amount, total
â”œâ”€â”€ expected_delivery_date, notes
â”œâ”€â”€ created_by, approved_by
â”œâ”€â”€ created_at, updated_at, deleted_at
```

#### Inventory Module

```
products
â”œâ”€â”€ id, company_id, sku, name, description
â”œâ”€â”€ category, type, unit_of_measure
â”œâ”€â”€ cost_price, selling_price
â”œâ”€â”€ minimum_stock, maximum_stock, reorder_point
â”œâ”€â”€ status, created_at, updated_at, deleted_at

warehouses
â”œâ”€â”€ id, company_id, name, code, address
â”œâ”€â”€ city, state, country, status
â”œâ”€â”€ created_at, updated_at, deleted_at

stock_movements
â”œâ”€â”€ id, company_id, product_id, warehouse_id
â”œâ”€â”€ type (purchase, sale, transfer, adjustment, return, write_off)
â”œâ”€â”€ quantity, reference_type, reference_id
â”œâ”€â”€ notes, created_by
â”œâ”€â”€ created_at, updated_at
```

#### Logistics Module

```
shipments
â”œâ”€â”€ id, company_id, sales_order_id, shipment_number
â”œâ”€â”€ status, carrier, tracking_number
â”œâ”€â”€ shipped_at, delivered_at
â”œâ”€â”€ notes, created_by
â”œâ”€â”€ created_at, updated_at, deleted_at

containers
â”œâ”€â”€ id, company_id, container_number, shipment_id
â”œâ”€â”€ type, status, carrier
â”œâ”€â”€ departure_date, arrival_date
â”œâ”€â”€ created_at, updated_at, deleted_at

landed_costs
â”œâ”€â”€ id, company_id, purchase_order_id
â”œâ”€â”€ shipping_cost, duty, insurance, other_fees
â”œâ”€â”€ total_landed_cost, allocated
â”œâ”€â”€ created_at, updated_at, deleted_at
```

#### Finance Module

```
accounts
â”œâ”€â”€ id, company_id, code, name, type (asset, liability, equity, revenue, expense)
â”œâ”€â”€ description, status, parent_id
â”œâ”€â”€ created_at, updated_at, deleted_at

journal_entries
â”œâ”€â”€ id, company_id, entry_number, date
â”œâ”€â”€ description, reference_type, reference_id
â”œâ”€â”€ status (draft, posted, void)
â”œâ”€â”€ created_by, posted_by
â”œâ”€â”€ created_at, updated_at, deleted_at

journal_entry_lines
â”œâ”€â”€ id, journal_entry_id, account_id
â”œâ”€â”€ debit, credit, description
â”œâ”€â”€ created_at, updated_at
```

#### Admin Module

```
audit_logs
â”œâ”€â”€ id, company_id, user_id, event
â”œâ”€â”€ auditable_type, auditable_id
â”œâ”€â”€ old_values (json), new_values (json)
â”œâ”€â”€ ip_address, user_agent
â”œâ”€â”€ created_at

approvals
â”œâ”€â”€ id, company_id, approvable_type, approvable_id
â”œâ”€â”€ status, level, approver_id
â”œâ”€â”€ notes, approved_at
â”œâ”€â”€ created_at, updated_at

documents
â”œâ”€â”€ id, company_id, name, file_path
â”œâ”€â”€ file_type, file_size, mime_type
â”œâ”€â”€ documentable_type, documentable_id
â”œâ”€â”€ uploaded_by, created_at, updated_at
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
â”œâ”€â”€ CRM/
â”‚   â”œâ”€â”€ CustomerList.php      # List with search/filter
â”‚   â”œâ”€â”€ CustomerForm.php      # Create/edit form
â”‚   â”œâ”€â”€ CustomerDetail.php    # Detail view
â”‚   â”œâ”€â”€ PipelineBoard.php     # Drag-and-drop pipeline
â”‚   â””â”€â”€ ActivityLog.php       # Activity feed
â”œâ”€â”€ Sales/
â”‚   â”œâ”€â”€ QuotationForm.php
â”‚   â”œâ”€â”€ InvoiceList.php
â”‚   â””â”€â”€ PaymentForm.php
â”œâ”€â”€ Procurement/
â”‚   â”œâ”€â”€ PurchaseOrderForm.php
â”‚   â”œâ”€â”€ ApprovalWorkflow.php
â”‚   â””â”€â”€ RFQManager.php
â”œâ”€â”€ Inventory/
â”‚   â”œâ”€â”€ ProductList.php
â”‚   â””â”€â”€ StockAdjustment.php
â”œâ”€â”€ Finance/
â”‚   â”œâ”€â”€ JournalEntryForm.php
â”‚   â””â”€â”€ AccountList.php
â”œâ”€â”€ AI/
â”‚   â”œâ”€â”€ ChatInterface.php
â”‚   â””â”€â”€ AnalysisPanel.php
â””â”€â”€ Admin/
    â”œâ”€â”€ UserManagement.php
    â”œâ”€â”€ RolePermissions.php
    â””â”€â”€ SystemSettings.php
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
â”œâ”€â”€ app/
â”‚   â””â”€â”€ public/              # Publicly accessible uploads
â”‚       â”œâ”€â”€ avatars/         # User profile pictures
â”‚       â”œâ”€â”€ documents/       # Uploaded documents
â”‚       â”œâ”€â”€ imports/         # Imported files
â”‚       â””â”€â”€ exports/         # Generated exports
â”œâ”€â”€ framework/
â”‚   â”œâ”€â”€ cache/               # Application cache
â”‚   â”œâ”€â”€ sessions/            # Session files
â”‚   â””â”€â”€ views/               # Compiled Blade views
â””â”€â”€ logs/
    â”œâ”€â”€ laravel.log          # Application log
    â””â”€â”€ worker.log           # Queue worker log
```

---

## Error Handling

### Exception Flow

```
Exception thrown â†’ Laravel Exception Handler
â”œâ”€â”€ HTTP Exception â†’ Render appropriate HTTP response
â”œâ”€â”€ Validation Exception â†’ Redirect with errors
â”œâ”€â”€ Authentication Exception â†’ Redirect to login
â”œâ”€â”€ Authorization Exception â†’ 403 response
â””â”€â”€ Uncaught Exception â†’ 500 response + log
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
