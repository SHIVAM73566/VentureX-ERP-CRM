# Database Setup Guide

**VentureX ERP & CRM â€” AI-Powered CRM & ERP Business Operating System**

> Version 1.0.0 | MySQL 8.0 Configuration and Management

---

## Table of Contents

1. [MySQL Installation](#mysql-installation)
2. [Database Creation](#database-creation)
3. [Migration](#migration)
4. [Seeding](#seeding)
5. [Database Configuration](#database-configuration)
6. [Backup](#backup)
7. [Restore](#restore)
8. [Performance Tuning](#performance-tuning)
9. [Troubleshooting](#troubleshooting)

---

## MySQL Installation

### Windows

```powershell
# Using Chocolatey
choco install mysql -y

# Or download the MSI installer from https://dev.mysql.com/downloads/mysql/
# Run the installer and follow the MySQL Installer wizard

# Start the MySQL service
net start MySQL80
```

### Ubuntu/Debian

```bash
# Update package index
sudo apt update

# Install MySQL Server
sudo apt install -y mysql-server

# Secure the installation
sudo mysql_secure_installation

# Start and enable the service
sudo systemctl start mysql
sudo systemctl enable mysql
```

### macOS

```bash
# Using Homebrew
brew install mysql

# Start the service
brew services start mysql

# Secure the installation
mysql_secure_installation
```

### Docker

```bash
# Run MySQL in a container
docker run -d \
  --name mysql-erp \
  -e MYSQL_ROOT_PASSWORD=your_password \
  -e MYSQL_DATABASE=VENTUREX_ERP \
  -p 3306:3306 \
  mysql:8.0 \
  --character-set-server=utf8mb4 \
  --collation-server=utf8mb4_unicode_ci
```

---

## Database Creation

### Create the Database

```bash
# Login to MySQL
mysql -u root -p

# Create the database
CREATE DATABASE VENTUREX_ERP
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

# Create a dedicated user (recommended for production)
CREATE USER 'erp_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON VENTUREX_ERP.* TO 'erp_user'@'localhost';
FLUSH PRIVILEGES;

EXIT;
```

### Verify Creation

```bash
mysql -u root -p -e "SHOW DATABASES LIKE 'VENTUREX_ERP';"
```

Expected output:

```
+-----------------------+
| Database (VENTUREX_ERP)|
+-----------------------+
| VENTUREX_ERP           |
+-----------------------+
```

### Character Set Verification

```bash
mysql -u root -p -e "
  SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME
  FROM information_schema.SCHEMATA
  WHERE SCHEMA_NAME = 'VENTUREX_ERP';
"
```

Expected output:

```
+----------------------------+------------------------+
| DEFAULT_CHARACTER_SET_NAME | DEFAULT_COLLATION_NAME |
+----------------------------+------------------------+
| utf8mb4                    | utf8mb4_unicode_ci     |
+----------------------------+------------------------+
```

---

## Migration

### Running Migrations

```bash
# Run all pending migrations
php artisan migrate

# Run migrations with force (bypass production confirmation)
php artisan migrate --force

# Run migrations without seeders
php artisan migrate --no-interaction
```

### Migration Structure

The database is organized into the following migration groups:

#### Core Tables

| Table                  | Description                              |
|------------------------|------------------------------------------|
| companies              | Organization/company records              |
| users                  | System users                              |
| roles                  | Role definitions                          |
| permissions            | Permission definitions                    |
| role_user              | User-role assignments                     |
| model_has_roles        | Polymorphic role assignments              |
| model_has_permissions  | Polymorphic permission assignments        |

#### CRM Tables

| Table                  | Description                              |
|------------------------|------------------------------------------|
| customers              | Customer master records                   |
| contacts               | Customer contacts                        |
| leads                  | Sales leads                              |
| opportunities          | Sales opportunities                      |
| activities             | Activity logs and tasks                  |
| customer_addresses     | Customer address records                 |
| customer_tags          | Tag associations for customers           |

#### Sales Tables

| Table                  | Description                              |
|------------------------|------------------------------------------|
| quotations             | Sales quotations                         |
| quotation_items        | Line items in quotations                 |
| sales_orders           | Sales orders                             |
| sales_order_items      | Line items in sales orders               |
| invoices               | Customer invoices                        |
| invoice_items          | Line items in invoices                   |
| payments               | Payment records                          |
| payment_terms          | Payment term definitions                 |

#### Procurement Tables

| Table                  | Description                              |
|------------------------|------------------------------------------|
| suppliers              | Supplier master records                   |
| purchase_requisitions  | Internal purchase requests               |
| requisition_items      | Items in requisitions                    |
| purchase_orders        | Purchase orders to suppliers             |
| purchase_order_items   | Line items in purchase orders            |
| rfqs                   | Requests for quotation                   |
| rfq_items              | Items in RFQs                            |
| supplier_quotations    | Supplier quotation responses             |

#### Inventory Tables

| Table                  | Description                              |
|------------------------|------------------------------------------|
| products               | Product master records                   |
| product_variants       | Product variant records                  |
| warehouses             | Warehouse locations                      |
| stock_levels           | Current stock quantities                 |
| stock_movements        | Stock movement history                   |
| stock_adjustments      | Manual stock adjustments                 |
| stock_transfers        | Inter-warehouse transfers                |
| categories             | Product categories                       |
| units_of_measure       | UOM definitions                          |

#### Finance Tables

| Table                  | Description                              |
|------------------------|------------------------------------------|
| chart_of_accounts      | Account structure                        |
| journal_entries        | Accounting journal entries               |
| journal_lines          | Individual journal lines                 |
| bank_accounts          | Bank account records                     |
| bank_reconciliations   | Reconciliation records                   |
| expenses               | Expense records                          |
| expense_categories     | Expense categories                       |
| tax_rates              | Tax rate definitions                     |

#### Logistics Tables

| Table                  | Description                              |
|------------------------|------------------------------------------|
| shipments              | Shipment records                         |
| shipment_items         | Items in shipments                       |
| deliveries             | Delivery records                         |
| carriers               | Shipping carrier definitions             |

#### Document Tables

| Table                  | Description                              |
|------------------------|------------------------------------------|
| documents              | Document records                         |
| document_versions      | Document version history                 |
| document_categories    | Document categories                      |

#### System Tables

| Table                  | Description                              |
|------------------------|------------------------------------------|
| audit_logs             | Audit trail entries                      |
| settings               | System settings                         |
| notifications          | User notifications                       |
| approval_rules         | Approval workflow rules                  |
| approval_requests      | Pending approvals                        |
| number_sequences       | Auto-numbering sequences                 |
| ai_usage_logs          | AI query logs                            |
| ai_cache               | Cached AI responses                      |
| webhooks               | Webhook configurations                   |

### Checking Migration Status

```bash
php artisan migrate:status
```

### Rollback

```bash
# Rollback the last migration batch
php artisan migrate:rollback

# Rollback a specific number of batches
php artisan migrate:rollback --step=5

# Rollback all migrations (destructive)
php artisan migrate:reset

# Fresh start: drop all tables and re-run migrations
php artisan migrate:fresh --seed
```

---

## Seeding

### Running Seeders

```bash
# Run all seeders (included with migrate)
php artisan migrate --seed

# Run seeders only
php artisan db:seed

# Run a specific seeder
php artisan db:seed --class=CompanySeeder
php artisan db:seed --class=DemoDataSeeder
```

### What the Seeders Create

#### CompanySeeder

Creates the default company:

- **Jain Metal** â€” Primary demo company

#### UserSeeder

Creates demo accounts with random passwords for security. To create demo accounts with known passwords:

```bash
php artisan db:seed --class=DemoCredentialSeeder
```

| Role          | Email                       | Password          |
|---------------|-----------------------------|-------------------|
| Demo Admin    | demo_admin@example.com      | Demo_Admin_2026!  |
| Demo Manager  | demo_manager@example.com    | Demo_Manager_2026!|
| Demo Sales    | demo_sales@example.com      | Demo_Sales_2026!  |

> âš ï¸ Change all passwords before production use.

#### RoleSeeder

Creates all default roles:

- Super Admin, CEO, Sales Manager, Sales Representative
- Procurement Manager, Warehouse Manager, Accountant, Viewer

#### PermissionSeeder

Creates the complete permission matrix for all modules.

#### CRMSeeder

Creates demo data:

- 25 sample customers
- 50 sample contacts
- 15 sample leads at various pipeline stages
- 10 sample opportunities

#### SalesSeeder

Creates demo data:

- 20 sample quotations at various stages
- 15 sample sales orders
- 12 sample invoices
- 8 sample payments

#### ProcurementSeeder

Creates demo data:

- 10 sample suppliers
- 8 sample purchase orders
- 5 sample RFQs

#### InventorySeeder

Creates demo data:

- 5 sample warehouses
- 30 sample products with variants
- Stock levels for all products
- Stock movement history

#### FinanceSeeder

Creates demo data:

- Chart of accounts
- Sample journal entries
- Bank account records

### Re-Seeding

To reset and re-seed the database:

```bash
php artisan migrate:fresh --seed
```

> Warning: This destroys all existing data.

### Custom Seeders

Create custom seeders for your specific needs:

```bash
php artisan make:seeder YourCustomSeeder
```

Then edit `database/seeders/YourCustomSeeder.php` and run it:

```bash
php artisan db:seed --class=YourCustomSeeder
```

---

## Database Configuration

### Environment Configuration

In `.env`:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=VENTUREX_ERP
DB_USERNAME=erp_user
DB_PASSWORD=secure_password_here
DB_PREFIX=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

### Connection Pooling (Production)

For high-traffic deployments, configure connection pooling:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=VENTUREX_ERP
DB_USERNAME=erp_user
DB_PASSWORD=secure_password_here
DB_PERSISTENT=true
```

### Read/Write Splitting

For scaled deployments with separate read replicas:

```php
// config/database.php
'mysql' => [
    'read' => [
        'host' => ['192.168.1.2', '192.168.1.3'],
    ],
    'write' => [
        'host' => ['192.168.1.1'],
    ],
    // ... other config
],
```

### SSL/TLS Connection

For encrypted database connections:

```ini
DB_SSL_CA=/path/to/ca.pem
DB_SSL_CERT=/path/to/client-cert.pem
DB_SSL_KEY=/path/to/client-key.pem
```

---

## Backup

### Manual Backup via CLI

```bash
# Full database dump
mysqldump -u root -p VENTUREX_ERP > backup_$(date +%Y%m%d_%H%M%S).sql

# Dump with options (recommended for production)
mysqldump -u root -p \
  --single-transaction \
  --routines \
  --triggers \
  --events \
  --quick \
  --lock-tables=false \
  VENTUREX_ERP > backup_$(date +%Y%m%d_%H%M%S).sql

# Compressed backup
mysqldump -u root -p VENTUREX_ERP | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz
```

### Manual Backup via mysqldump

```bash
mysqldump -u username -p --single-transaction --routines --triggers my_erp > backup_$(date +%Y%m%d_%H%M%S).sql
```

Options:

```bash
# Backup specific tables
mysqldump -u username -p my_erp users customers orders > tables_backup.sql

# Backup to a specific path
mysqldump -u username -p --single-transaction --routines --triggers my_erp > /backups/custom/backup.sql

# Compress the backup
mysqldump -u username -p --single-transaction --routines --triggers my_erp | gzip > backup.sql.gz
```

### Automated Backups

Configure scheduled backups in `app/Console/Kernel.php` or via the admin panel at **Settings > Maintenance > Backups**.

The backup schedule and retention are configured in the application's scheduled tasks. See [BACKUP_AND_RESTORE.md](BACKUP_AND_RESTORE.md) for details on backup configuration and off-site storage options.

---

## Restore

### Restoring from a SQL File

```bash
# Create a fresh database
mysql -u root -p -e "DROP DATABASE IF EXISTS VENTUREX_ERP; CREATE DATABASE VENTUREX_ERP CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Restore from backup
mysql -u root -p VENTUREX_ERP < backup_file.sql

# For compressed backups
gunzip < backup_file.sql.gz | mysql -u root -p VENTUREX_ERP
```

### Alternative Restore via Command Line

```bash
# Using mysql command directly
mysql -u username -p my_erp < backup_file.sql

# For compressed backups
gunzip < backup_file.sql.gz | mysql -u username -p my_erp
```

### Restoring Specific Tables

```bash
# Extract a specific table from the backup
mysqldump -u root -p VENTUREX_ERP customers contacts > partial_backup.sql

# Restore only those tables
mysql -u root -p VENTUREX_ERP < partial_backup.sql
```

### Post-Restore Steps

After restoring a database:

```bash
# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear old cache
php artisan cache:clear

# Regenerate autoload files
composer dump-autoload

# Verify integrity
php artisan migrate:status
```

---

## Performance Tuning

### MySQL Configuration

Optimize MySQL for VentureX ERP & CRM by editing `my.cnf` or `my.ini`:

```ini
[mysqld]
# Buffer Pool
innodb_buffer_pool_size = 1G
innodb_buffer_pool_instances = 4

# Log Files
innodb_log_file_size = 256M
innodb_log_buffer_size = 64M

# Thread Configuration
thread_cache_size = 16
max_connections = 200

# Query Cache (MySQL 8.0 uses optimizer hints instead)
# Performance Schema
performance_schema = ON

# Character Set
character_set_server = utf8mb4
collation_server = utf8mb4_unicode_ci

# InnoDB Settings
innodb_file_per_table = ON
innodb_flush_log_at_trx_commit = 1
innodb_flush_method = O_DIRECT

# Slow Query Log
slow_query_log = ON
slow_query_log_file = /var/log/mysql/slow-query.log
long_query_time = 2
```

### Database Indexes

VentureX ERP & CRM includes optimized indexes for common queries. To verify index coverage:

```sql
SHOW INDEX FROM your_table_name;
EXPLAIN SELECT * FROM your_table_name WHERE your_column = 'value';
```

### Query Optimization

Enable query logging for development:

```ini
DB_LOG_QUERIES=true
```

Review slow queries in the application logs or enable MySQL's slow query log.

---

## Troubleshooting

### "Access denied for user"

```bash
# Reset the MySQL password
mysql -u root -p -e "
  ALTER USER 'erp_user'@'localhost' IDENTIFIED BY 'new_password';
  FLUSH PRIVILEGES;
"
```

### "Table doesn't exist"

```bash
# Check migration status
php artisan migrate:status

# Re-run migrations
php artisan migrate --force
```

### "Database is full"

```bash
# Check disk space
df -h

# Check database size
mysql -u root -p -e "
  SELECT table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
  FROM information_schema.TABLES
  WHERE table_schema = 'VENTUREX_ERP'
  GROUP BY table_schema;
"
```

### "Too many connections"

```bash
# Check current connections
mysql -u root -p -e "SHOW STATUS LIKE 'Threads_connected';"

# Increase max connections temporarily
mysql -u root -p -e "SET GLOBAL max_connections = 500;"
```

### "Lock wait timeout exceeded"

```bash
# Check for blocking transactions
mysql -u root -p -e "SHOW ENGINE INNODB STATUS\G"

# Kill blocking queries (use the process ID from the output)
mysql -u root -p -e "KILL <process_id>;"
```

### Connection Refused

```bash
# Check MySQL service status
sudo systemctl status mysql       # Linux
brew services list                # macOS
net start MySQL80                 # Windows

# Check if MySQL is listening on the correct port
netstat -tlnp | grep 3306        # Linux
```

---

**Next Steps:**

- Read [BACKUP_AND_RESTORE.md](BACKUP_AND_RESTORE.md) for comprehensive backup strategies
- Read [DEPLOYMENT.md](DEPLOYMENT.md) for production database configuration
- Read [SECURITY.md](SECURITY.md) for database security practices
