# API Documentation

VentureX ERP & CRM is a server-rendered application built with Laravel Blade, Alpine.js, and Livewire. There is no public REST API.

This document describes the internal application architecture, routing structure, and integration points.

---

## Application Type

VentureX ERP & CRM uses a **monolithic server-rendered architecture**:

- **Server-side rendering** via Laravel Blade templates
- **Client-side interactivity** via Alpine.js and Livewire
- **No public REST/GraphQL API endpoints**
- **Session-based authentication** (not token-based)

All data operations occur through Blade routes and Livewire components, not through API endpoints.

---

## Routing Architecture

### Web Routes

All routes are defined in `routes/web.php` and protected by authentication middleware.

**Route Pattern:**

```
/{module}/{resource}/{action}
```

**Examples:**

| URL Pattern | Description |
|-------------|-------------|
| `/dashboard` | Main dashboard |
| `/crm/customers` | Customer list |
| `/crm/customers/create` | Create customer |
| `/crm/customers/{id}` | View customer |
| `/crm/customers/{id}/edit` | Edit customer |
| `/sales/quotations` | Quotation list |
| `/sales/invoices` | Invoice list |
| `/procurement/purchase-orders` | Purchase order list |

### Middleware Stack

All routes pass through these middleware layers:

1. **TrustProxies** â€“ Handles proxy headers
2. **PreventRequestsDuringMaintenance** â€“ Maintenance mode
3. **ValidatePostSize** â€“ Request size limits
4. **TrimStrings** â€“ Input trimming
5. **ConvertEmptyStringsToNull** â€“ Null conversion
6. **Authenticate** â€“ Session authentication
7. **Authorize** â€“ Role/permission checking (Spatie Permission)

### Role-Based Route Protection

Routes are protected using Spatie Permission middleware:

```php
Route::middleware(['role:super_admin'])->group(function () {
    // Admin-only routes
});
```

---

## Livewire Components

Livewire provides dynamic, server-rendered interactive components without writing JavaScript.

### Component Locations

```
app/Livewire/
â”œâ”€â”€ CRM/
â”‚   â”œâ”€â”€ CustomerList.php
â”‚   â”œâ”€â”€ CustomerForm.php
â”‚   â””â”€â”€ ...
â”œâ”€â”€ Sales/
â”‚   â”œâ”€â”€ QuotationList.php
â”‚   â””â”€â”€ ...
â”œâ”€â”€ Procurement/
â”‚   â””â”€â”€ ...
â”œâ”€â”€ Inventory/
â”‚   â””â”€â”€ ...
â”œâ”€â”€ Logistics/
â”‚   â””â”€â”€ ...
â”œâ”€â”€ Finance/
â”‚   â””â”€â”€ ...
â”œâ”€â”€ AI/
â”‚   â””â”€â”€ ...
â””â”€â”€ Admin/
    â””â”€â”€ ...
```

### Livewire Events

Components communicate via Livewire events:

```php
// Dispatch event
$this->dispatch('customer-created', ['id' => $customer->id]);

// Listen for event
protected $listeners = ['customer-created' => 'refreshList'];
```

### Livewire AJAX Requests

Livewire makes AJAX calls to `/livewire/update` for component updates. These are:
- Protected by session authentication
- CSRF-protected
- Rate-limited

---

## Blade Directives

Custom Blade directives used throughout the application:

| Directive | Purpose |
|-----------|---------|
| `@can('permission')` | Check user permission |
| `@role('role_name')` | Check user role |
| `@livewire('component')` | Render Livewire component |
| `@json($data)` | Encode data as JSON for Alpine.js |
| `@csrf` | CSRF token for forms |

---

## Alpine.js Integration

Alpine.js provides client-side interactivity within Blade templates.

**Common Patterns:**

```html
<!-- Toggle visibility -->
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open">Content</div>
</div>

<!-- Form handling -->
<div x-data="{ submitting: false }">
    <form @submit.prevent="submitting = true; $wire.submitForm()">
        <button :disabled="submitting">Submit</button>
    </form>
</div>
```

---

## Data Flow

### Request Lifecycle

```
Browser â†’ Nginx/Apache â†’ PHP-FPM â†’ Laravel Router
â†’ Middleware â†’ Controller/Livewire â†’ Eloquent â†’ MySQL
â†’ Response â†’ Blade Template â†’ HTML â†’ Browser
```

### Form Submission Flow

```
User fills form â†’ Alpine.js validates â†’ POST to Laravel route
â†’ Controller validates â†’ Eloquent saves to database
â†’ Redirect with flash message â†’ Blade renders success
```

### Livewire Update Flow

```
User interaction â†’ Alpine.js triggers â†’ Livewire AJAX to /livewire/update
â†’ Livewire component processes â†’ Eloquent queries
â†’ Component re-renders â†’ HTML diff sent to browser â†’ DOM updated
```

---

## CSRF Protection

All forms and Livewire requests include CSRF tokens:

- Blade forms: `@csrf` directive
- Livewire: Automatic CSRF token inclusion
- Alpine.js: Manual form submission includes token

---

## Authentication Flow

1. User submits credentials via login form
2. Laravel validates against `users` table
3. Session created and stored
4. User redirected to dashboard
5. All subsequent requests authenticated via session cookie
6. MFA check (if enabled) after initial authentication
7. Step-up auth required for sensitive operations

---

## Integration Points

While no public API exists, VentureX ERP & CRM supports:

### File Imports

- CSV/Excel import for bulk data operations
- Document upload for AI processing
- Template-based data import

### File Exports

- CSV/Excel export from any list view
- PDF generation for documents
- Bulk export with filtering

### Webhook Support (Future)

Webhook support is planned for future versions to enable integration with external systems.

---

## Frontend Asset Pipeline

### Build Process

```
resources/css/app.css â†’ Tailwind CSS â†’ PostCSS â†’ public/build/app.css
resources/js/app.js â†’ Vite â†’ public/build/app.js
```

### Vite Configuration

- Development: HMR (Hot Module Replacement)
- Production: Minified, hashed, optimized

### Asset Manifest

Production builds generate a manifest for cache busting:

```php
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

---

## Database Query Patterns

### Eloquent Models

All models follow standard Eloquent patterns:
- Relationships defined in models
- Scopes for common queries
- Accessors/mutators for computed properties
- Soft deletes where applicable

### Query Optimization

- Eager loading for relationship data
- Database indexes on frequently queried columns
- Cursor-based pagination for large datasets
- Query caching for static data

---

## Error Handling

- Laravel exception handler catches all errors
- 404 errors render custom not-found page
- 403 errors render unauthorized page
- 500 errors render generic error page (detailed in production logs)
- Validation errors return to form with error messages
- AJAX errors handled by frontend JavaScript

---

## Future API Support

A REST API layer is planned for future releases to support:
- Third-party integrations
- Mobile applications
- Webhook delivery
- External system connectivity

This will be implemented as a separate API module with token-based authentication (Laravel Sanctum).
