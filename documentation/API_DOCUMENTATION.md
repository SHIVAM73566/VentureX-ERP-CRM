# API Documentation

VentureX ERP & CRM is a server-rendered application built with Laravel Blade, Alpine.js, and Livewire, complemented by a full RESTful API authenticated via Laravel Sanctum tokens. See `routes/api.php` for the complete route definitions.

This document describes the internal application architecture, routing structure, and integration points.

---

## Application Type

VentureX ERP & CRM uses a **monolithic server-rendered architecture**:

- **Server-side rendering** via Laravel Blade templates
- **Client-side interactivity** via Alpine.js and Livewire
- **Full REST API** under `/api` (defined in `routes/api.php` with 18 controllers)
- **Session-based authentication** for web routes; **Laravel Sanctum token authentication** for API routes

Web UI data operations occur through Blade routes and Livewire components, while external integrations use the REST API.

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

1. **TrustProxies** — Handles proxy headers
2. **PreventRequestsDuringMaintenance** — Maintenance mode
3. **ValidatePostSize** — Request size limits
4. **TrimStrings** — Input trimming
5. **ConvertEmptyStringsToNull** — Null conversion
6. **Authenticate** — Session authentication
7. **Authorize** — Role/permission checking (Spatie Permission)

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
—œ—€—€ CRM/
—‚   —œ—€—€ CustomerList.php
—‚   —œ—€—€ CustomerForm.php
—‚   —”—€—€ ...
—œ—€—€ Sales/
—‚   —œ—€—€ QuotationList.php
—‚   —”—€—€ ...
—œ—€—€ Procurement/
—‚   —”—€—€ ...
—œ—€—€ Inventory/
—‚   —”—€—€ ...
—œ—€—€ Logistics/
—‚   —”—€—€ ...
—œ—€—€ Finance/
—‚   —”—€—€ ...
—œ—€—€ AI/
—‚   —”—€—€ ...
—”—€—€ Admin/
    —”—€—€ ...
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

In addition to the REST API (see `routes/api.php` and the root `API-SETUP.md` for endpoint details), VentureX ERP & CRM supports:

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

## API Availability

The REST API described above is available today, implemented in `routes/api.php` with token-based authentication via Laravel Sanctum (the `TokenController` issues and manages personal access tokens). It supports:

- Third-party integrations
- Mobile applications
- External system connectivity

Planned enhancements include webhook delivery. See the root `API-SETUP.md` for authentication examples and endpoint listings.
