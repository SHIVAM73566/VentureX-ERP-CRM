# Security Documentation

Overview of security features, configurations, and best practices in VentureX ERP & CRM.

---

## Authentication

### Login System

- Email and password authentication
- bcrypt password hashing (cost factor 12)
- Account lockout after failed attempts
- Login attempt logging

### Multi-Factor Authentication (MFA/2FA)

**Supported Methods:**
- TOTP (Time-based One-Time Password) compatible with Google Authenticator, Authy, and similar apps
- Email verification codes

**Setup Process:**
1. User navigates to Profile > Security
2. Clicks "Enable Two-Factor Authentication"
3. Scans QR code with authenticator app
4. Enters verification code to confirm
5. Saves backup recovery codes

**Recovery:**
- Backup codes generated during MFA setup
- Each code is single-use
- Admin can reset MFA for locked-out users

### Step-Up Authentication

For sensitive operations requiring additional verification:
- Financial transaction approval
- User role changes
- System configuration changes
- Data deletion requests

Step-up prompts for password re-entry or MFA code before proceeding.

### Session Management

- Server-side session storage
- Configurable session lifetime (default: 120 minutes)
- Session invalidation on password change
- Concurrent session limiting
- Session fixation protection

---

## Password Security

### Password Policy (Configurable)

| Setting | Default | Description |
|---------|---------|-------------|
| Minimum Length | 8 characters | Enforced minimum length |
| Require Uppercase | Yes | At least one uppercase letter |
| Require Numbers | Yes | At least one digit |
| Require Special Characters | Yes | At least one special character |
| Password Expiry | 90 days | Days before forced reset |
| Password History | 5 | Previous passwords prevented |

### Password Hashing

- Algorithm: bcrypt
- Cost Factor: 12
- Automatic rehashing on login if cost factor changes

---

## Authorization

### Role-Based Access Control (RBAC)

Powered by Spatie Laravel Permission:

- Roles define permission sets
- Permissions assigned to roles
- Users assigned one or more roles
- Permission checks on all routes and views

### Permission Structure

```
Module.Action (e.g., sales.create, finance.edit)
```

**Module-Specific Permissions:**

| Module | Permissions |
|--------|-------------|
| CRM | customers.view, customers.create, customers.edit, customers.delete, leads.*, opportunities.*, contacts.*, activities.* |
| Sales | quotations.*, sales_orders.*, invoices.*, payments.* |
| Procurement | suppliers.*, supplier_offers.*, rfqs.*, purchase_requisitions.*, purchase_orders.* |
| Inventory | products.*, warehouses.*, stock_movements.* |
| Logistics | shipments.*, containers.*, landed_costs.* |
| Finance | finance_dashboard.view, receivables.*, payables.*, accounts.*, journal_entries.* |
| AI | ai_assistant.use, ai_copilot.use, deep_analysis.use, procurement_ai.use, document_reader.use, executive_review.use |
| Admin | companies.*, users.*, roles.*, settings.*, security.*, documents.*, exports.*, audit_logs.*, approvals.*, ai_skills.*, ai_quotas.* |

### Permission Enforcement

- **Routes:** Middleware checks permissions before controller execution
- **Views:** `@can` directives hide unauthorized UI elements
- **Livewire:** Permission checks in component methods
- **Data:** Query scopes filter data by user permissions

---

## Data Protection

### CSRF Protection

- Laravel CSRF token on all forms
- Token verification on all POST/PUT/DELETE requests
- Livewire automatic CSRF handling

### XSS Prevention

- Blade automatic HTML escaping (`{{ }}`)
- Content Security Policy (CSP) headers
- Input sanitization on user-generated content

### SQL Injection Prevention

- Eloquent ORM parameterized queries
- Query Builder parameter binding
- No raw SQL with user input
- Database user with minimal privileges

### Request Validation

- Server-side validation on all form submissions
- Type checking and sanitization
- File upload validation (type, size)
- Request size limits

---

## Rate Limiting

### Login Rate Limiting

- Maximum 5 failed attempts per 15-minute window
- Account locked for 15 minutes after threshold
- Progressive lockout for repeated violations

### Form Submission Rate Limiting

- Configurable per-route limits
- Default: 60 requests per minute per user

### Sensitive Endpoint Rate Limiting

- Password reset: 3 attempts per hour
- MFA verification: 5 attempts per 15 minutes
- Data export: 10 per hour

---

## Transport Security

### HTTPS/SSL

- Required for production deployments
- HSTS (HTTP Strict Transport Security) header
- Secure cookie flags
- TLS 1.2+ enforcement

### Security Headers

Configured in web server and application:

```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self'
Strict-Transport-Security: max-age=31536000; includeSubDomains
Permissions-Policy: camera=(), microphone=(), geolocation=()
```

---

## Content Security Policy (CSP)

CSP headers restrict resource loading:

- Scripts: Only from self and inline (Alpine.js requirement)
- Styles: Only from self and inline (Tailwind requirement)
- Images: Only from self and data URIs
- Fonts: Only from self
- Connections: Only to self
- Frames: Blocked

---

## Data Encryption

### At Rest

- Application key used for encrypted values
- Laravel encryption for cookies and sessions
- Database-level encryption for sensitive columns (if configured)

### In Transit

- TLS encryption for all HTTP traffic
- Encrypted database connections (if configured)
- Redis AUTH for cache/queue connections

### File Storage

- Uploaded files stored with restricted access
- Storage directory not web-accessible
- File type validation on upload

---

## Audit Logging

### Tracked Events

All significant actions are logged:

| Category | Events |
|----------|--------|
| Authentication | Login, logout, failed login, password change, MFA change |
| User Management | User created, edited, deactivated, role changed |
| Data Operations | Record created, modified, deleted |
| Financial | Invoice created, payment recorded, journal entry posted |
| Admin | Settings changed, permission modified, export generated |
| Security | Lockout triggered, MFA enforced, session expired |

### Log Details

Each audit entry includes:
- Timestamp
- User ID and name
- Action type
- Module and record affected
- IP address
- User agent
- Before/after values (for changes)

### Log Retention

- Default: 365 days
- Configurable in admin settings
- Logs can be exported for compliance

---

## GDPR Compliance

### Data Subject Rights

VentureX ERP & CRM supports GDPR requirements:

**Right to Access:**
- Users can view all personal data stored about them
- Admin can generate data export for any user

**Right to Rectification:**
- Users can update their profile information
- Admin can edit any user's data

**Right to Erasure (Right to be Forgotten):**
- GDPR Data Deletion feature in Authentication module
- Users can request account deletion
- Admin can process deletion requests
- Soft-delete preserves data for audit compliance
- Configurable data retention periods

**Right to Data Portability:**
- Export user data in standard formats (CSV, JSON)
- Include all personal data and activity history

### Data Processing

- All data processing is logged
- User consent tracked where applicable
- Data minimization: only necessary data collected
- Purpose limitation: data used only for stated purposes

### Data Retention

- Configurable retention periods per data type
- Automatic data purge for expired records
- Audit logs retained separately for compliance

### Privacy Controls

- Personal data fields identified and labeled
- Access to personal data restricted by role
- Data encryption for sensitive fields
- Regular access reviews via audit logs

---

## Secure Configuration

### Environment Variables

- `.env` file excluded from version control
- Sensitive values not logged
- Environment variables not exposed to frontend

### Debug Mode

- `APP_DEBUG=false` in production
- Error details hidden from users in production
- Errors logged to file only

### API Security (Future)

When API support is added:
- Token-based authentication (Laravel Sanctum)
- Scoped API tokens
- Rate limiting per token
- Token rotation and revocation

---

## Security Best Practices for Administrators

1. **Change default passwords** immediately after installation
2. **Enable MFA** for all administrative users
3. **Use strong passwords** meeting policy requirements
4. **Limit admin access** to necessary personnel only
5. **Review audit logs** regularly
6. **Keep software updated** to latest version
7. **Monitor login attempts** for suspicious activity
8. **Use HTTPS** exclusively
9. **Restrict IP access** for admin functions
10. **Regular backups** per BACKUP_AND_RESTORE.md

---

## Vulnerability Reporting

If you discover a security vulnerability:

1. Email: security@venturexerp.com
2. Include description and reproduction steps
3. Allow reasonable time for response
4. Do not publicly disclose until fix is available

---

## Security Checklist

- [ ] APP_DEBUG set to false in production
- [ ] Default admin password changed
- [ ] MFA enabled for admin users
- [ ] HTTPS configured and enforced
- [ ] Security headers configured
- [ ] File permissions restricted
- [ ] Database user has minimal privileges
- [ ] Audit logging enabled
- [ ] Rate limiting configured
- [ ] Backup schedule active
- [ ] Session lifetime appropriate
- [ ] Password policy enforced
