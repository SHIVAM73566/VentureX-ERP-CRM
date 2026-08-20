# Administrator Guide

Guide for system administrators managing users, roles, settings, and configuration in VentureX ERP & CRM.

---

## Access Requirements

Administrative functions require the `super_admin` or `company_admin` role. Some settings are restricted to `super_admin` only.

## User Management

### Viewing Users

Navigate to **Admin > Users** to see all system users.

**List Features:**
- Search by name, email, or role
- Filter by role, status, or company
- Sort by any column
- View user activity status

### Creating Users

1. Click "New User"
2. Fill in required fields:
   - Full Name
   - Email (must be unique)
   - Password (or generate random)
3. Assign role(s)
4. Set status (active/inactive)
5. Optionally link to a company
6. Save

### Editing Users

1. Click on user name in list
2. Edit profile information
3. Change role assignments
4. Enable/disable account
5. Reset password if needed
6. Save changes

### Deactivating Users

1. Open user profile
2. Toggle status to "Inactive"
3. User can no longer log in
4. Historical data and audit logs are preserved

### Password Reset

Admins can force a password reset:
1. Open user profile
2. Click "Reset Password"
3. System generates new password or sends reset email
4. User must change password on next login

---

## Role Management

### Default Roles

| Role | Description | Key Permissions |
|------|-------------|-----------------|
| super_admin | Full system access | All modules, admin functions |
| company_admin | Company administration | Company-scoped admin |
| ceo | Executive access | Dashboard, reports, approvals |
| cfo | Financial oversight | All finance modules |
| finance_manager | Finance operations | Finance module CRUD |
| sales_manager | Sales team lead | Sales module + team management |
| sales_rep | Sales representative | Sales module (limited scope) |
| procurement_manager | Procurement lead | Procurement module + approvals |
| operations_manager | Operations oversight | Inventory, logistics modules |
| hr_manager | Human resources | HR-related functions |
| support_agent | Customer support | Support ticket management |
| viewer | Read-only access | View all assigned modules |

### Creating Custom Roles

1. Navigate to **Admin > Roles**
2. Click "New Role"
3. Enter role name and display name
4. Select permissions from the tree:
   - Module-level access (view, create, edit, delete)
   - Specific action permissions
   - Admin function access
5. Save

### Assigning Permissions

Permissions follow a hierarchical structure:

```
Module
â”œâ”€â”€ View (list, read)
â”œâ”€â”€ Create
â”œâ”€â”€ Edit
â”œâ”€â”€ Delete
â””â”€â”€ Export
```

**Permission Groups:**
- CRM: customers, leads, opportunities, contacts, activities
- Sales: quotations, sales_orders, invoices, payments
- Procurement: suppliers, supplier_offers, rfqs, purchase_requisitions, purchase_orders
- Inventory: products, warehouses, stock_movements
- Logistics: shipments, containers, landed_costs
- Finance: finance_dashboard, receivables, payables, accounts, journal_entries
- AI: ai_assistant, ai_copilot, deep_analysis, procurement_ai, document_reader, executive_review
- Admin: companies, users, roles, settings, security, documents, exports, audit_logs, approvals, ai_skills, ai_quotas

### Role Hierarchy

Roles can be assigned with priority levels for approval workflows:

1. super_admin
2. company_admin
3. ceo / cfo
4. Department managers
5. Individual contributors
6. viewer

---

## Company Management

### Multi-Company Setup

For Professional and Enterprise plans:

1. Navigate to **Admin > Companies**
2. Click "New Company"
3. Enter company details:
   - Company name
   - Legal name and registration
   - Address and contact information
   - Tax ID / VAT number
   - Currency
   - Fiscal year settings
4. Configure company-specific settings
5. Assign users to company

### Company Settings

Each company can configure:
- Default currency
- Tax rates
- Invoice templates
- Email templates
- Fiscal year period
- Business rules

---

## System Settings

### General Settings

Navigate to **Admin > Settings**:

- **Application Name**: Displayed in header and emails
- **Application URL**: Base URL for links and emails
- **Default Language**: System default language
- **Default Timezone**: For date/time display
- **Date Format**: Display format for dates
- **Currency**: Default system currency

### Mail Settings

Configure email delivery:

- **Mail Driver**: SMTP, Mailgun, SES, etc.
- **SMTP Host**: Mail server address
- **SMTP Port**: Server port
- **SMTP Username**: Authentication username
- **SMTP Password**: Authentication password
- **Encryption**: TLS, SSL, or none
- **From Address**: Sender email address
- **From Name**: Sender display name

### Storage Settings

Configure file storage:

- **Driver**: local, s3, or compatible
- **S3 Bucket**: AWS S3 bucket name (if using S3)
- **S3 Region**: AWS region
- **Max Upload Size**: Maximum file size in MB

### Notification Settings

Configure system notifications:

- **Email Notifications**: Enable/disable
- **Notification Events**: Select which events trigger notifications
- **Email Templates**: Customize notification templates

---

## Security Settings

### Password Policy

Navigate to **Admin > Security**:

- **Minimum Length**: Minimum password characters (default: 8)
- **Require Uppercase**: Require uppercase letters
- **Require Numbers**: Require numeric characters
- **Require Special Characters**: Require special characters
- **Password Expiry**: Days before password expires (0 = never)
- **Password History**: Number of previous passwords to prevent reuse

### Session Settings

- **Session Lifetime**: Minutes before session expires (default: 120)
- **Max Concurrent Sessions**: Maximum simultaneous sessions per user
- **Idle Timeout**: Minutes of inactivity before logout

### Rate Limiting

- **Login Attempts**: Max failed login attempts before lockout (default: 5)
- **Lockout Duration**: Minutes to lock account after max attempts (default: 15)
- **API Rate Limit**: Requests per minute for sensitive endpoints

### IP Restrictions

- Whitelist specific IP ranges for admin access
- Block known malicious IPs
- Log all access attempts

### Two-Factor Authentication (MFA)

**Enforce MFA:**
1. Navigate to **Admin > Security**
2. Enable "Require MFA for all users"
3. Users must set up MFA on next login

**Supported MFA Methods:**
- TOTP (Time-based One-Time Password) via authenticator apps
- Email verification codes

**User MFA Setup:**
1. User navigates to Profile > Security
2. Click "Enable Two-Factor Authentication"
3. Scan QR code with authenticator app
4. Enter verification code to confirm
5. Save backup recovery codes

### Step-Up Authentication

For sensitive operations (financial transactions, user management):

1. User initiates sensitive action
2. System prompts for re-authentication
3. User enters password or MFA code
4. Action proceeds after verification

---

## Audit Logs

Navigate to **Admin > Audit Logs** to review system activity.

**Tracked Events:**
- User login/logout
- Failed login attempts
- Record creation, modification, deletion
- Configuration changes
- Permission changes
- Data exports
- MFA changes
- Password changes

**Log Details:**
- Timestamp
- User
- Action type
- Affected module and record
- IP address
- User agent
- Before/after values for changes

**Filters:**
- Date range
- User
- Module
- Action type
- IP address

---

## Document Management

Navigate to **Admin > Documents**:

- View all uploaded documents
- Manage storage settings
- Set retention policies
- Monitor storage usage

---

## Export Management

Navigate to **Admin > Exports**:

- View all data exports
- Monitor export jobs
- Set export permissions
- Configure export formats

---

## Approval Workflows

Navigate to **Admin > Approvals**:

Configure approval chains for:
- Purchase Requisitions
- Purchase Orders
- Sales Orders
- Journal Entries
- Expense Reports

**Approval Chain Configuration:**
1. Select module/entity type
2. Define approval levels
3. Assign approvers per level
4. Set escalation rules
5. Configure notification triggers

---

## AI Configuration

### AI Skills

Navigate to **Admin > AI Skills**:

Configure AI capabilities:
- Enable/disable specific AI features
- Set model preferences
- Configure analysis parameters
- Set response templates

### AI Quotas

Navigate to **Admin > AI Quotas**:

Manage AI usage limits:
- Set monthly token limits per user
- Set monthly request limits per module
- Monitor usage against quotas
- Configure overage handling

---

## System Monitoring

### Application Logs

View application logs via:
- Admin > Settings > Logs
- Or directly: `storage/logs/laravel.log`

### Queue Monitoring

Check queue status:
```bash
php artisan queue:status
```

Or via Supervisor:
```bash
sudo supervisorctl status
```

### Cache Management

Clear system caches:
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Maintenance Mode

To put the system in maintenance mode:

```bash
php artisan down
```

To take it out:

```bash
php artisan up
```

With a custom message:

```bash
php artisan down --message="Scheduled maintenance in progress"
```

---

## Backup Configuration

See [BACKUP_AND_RESTORE.md](documentation/BACKUP_AND_RESTORE.md) for automated backup setup.

## Troubleshooting

See [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for common issues and solutions.
