# cPanel Deployment Guide for VentureX ERP & CRM

This guide walks you through deploying VentureX ERP & CRM on a cPanel hosting environment.

## Prerequisites

- cPanel hosting with:
  - PHP 8.1 or higher (PHP 8.3 recommended)
  - MySQL 5.7 or higher (MySQL 8.0 recommended)
  - Composer (or ability to run via SSH)
  - SSH access (recommended) or File Manager access
  - Minimum 512MB PHP memory limit
  - SSL certificate (Let's Encrypt or purchased)

## Step 1: Prepare Your Local Environment

Before uploading, prepare the application locally:

```bash
# Clone the repository
git clone https://github.com/your-repo/venturex-erp.git
cd venturex-erp

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build
```

## Step 2: Create MySQL Database in cPanel

1. Log into cPanel
2. Navigate to **MySQL® Databases**
3. Create a new database:
   - Database Name: `venturex_db`
   - Click **Create Database**
4. Create a database user:
   - Username: `venturex_user`
   - Password: Generate a strong password
   - Click **Create User**
5. Add user to database:
   - Select the user and database
   - Check **ALL PRIVILEGES**
   - Click **Make Changes**

### Database Credentials
Note these for later:
- Database Name: `yourprefix_venturex_db`
- Database User: `yourprefix_venturex_user`
- Database Password: (the password you created)
- Host: `localhost`

## Step 3: Upload Files via File Manager

### Option A: File Manager (Recommended for small projects)

1. Navigate to **File Manager** in cPanel
2. Go to `public_html` (or your subdomain directory)
3. Click **Upload** in the toolbar
4. Upload the entire application folder as a ZIP file
5. Once uploaded, select the ZIP file and click **Extract**
6. Move all files from the extracted folder to the root directory

### Option B: FTP Upload

1. Use an FTP client (FileZilla, WinSCP)
2. Connect using cPanel FTP credentials
3. Navigate to `public_html`
4. Upload all application files

### Option C: Git Deployment (If supported)

```bash
cd ~/public_html
git clone https://github.com/your-repo/venturex-erp.git .
```

## Step 4: Configure .env File

1. Copy `.env.example` to `.env` in the root directory
2. Edit the `.env` file with your settings:

```env
APP_NAME=VentureX
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=yourprefix_venturex_db
DB_USERNAME=yourprefix_venturex_user
DB_PASSWORD=your-database-password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### Generate Application Key

If you have SSH access:
```bash
cd ~/public_html
php artisan key:generate
```

If you don't have SSH, manually generate a key:
1. Go to [https://dispatched.dev/app/key-gen](https://dispatched.dev/app/key-gen)
2. Copy the generated key
3. Add it to `.env` as `APP_KEY=base64:your-key-here`

## Step 5: Install Composer Dependencies

### Via SSH (Recommended)

```bash
cd ~/public_html
composer install --no-dev --optimize-autoloader
```

### Via cPanel Terminal

1. Navigate to **Terminal** in cPanel
2. Run the same commands as above

### Without SSH Access

If SSH is not available:
1. Run `composer install` locally with `--no-dev`
2. Upload the `vendor/` directory via File Manager or FTP
3. This may take longer depending on your upload speed

## Step 6: Set Directory Permissions

Via SSH or cPanel Terminal:

```bash
cd ~/public_html

# Set directory permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 755 public

# Set file permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Specific permissions for storage
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

Via File Manager:
1. Right-click on `storage` folder → **Change Permissions**
2. Set to **755** (or **775** if needed)
3. Repeat for `bootstrap/cache`

## Step 7: Configure Document Root

### Option A: Public Directory (Recommended)

1. In cPanel, go to **Domains** or **Addon Domains**
2. Set Document Root to `public_html/public`
3. This is the most secure configuration

### Option B: Rewrite Rules (If can't change document root)

Create or edit `.htaccess` in `public_html`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

## Step 8: Run Database Migrations

Via SSH:
```bash
cd ~/public_html
php artisan migrate --force
```

If you have a seeder to run:
```bash
php artisan db:seed --force
```

## Step 9: Configure Cron Jobs

1. Navigate to **Cron Jobs** in cPanel
2. Set cron to run every minute:
```
* * * * * cd /home/yourusername/public_html && php artisan schedule:run >> /dev/null 2>&1
```

For queue workers (if using database queue):
```
* * * * * cd /home/yourusername/public_html && php artisan queue:work --sleep=3 --tries=3 >> /dev/null 2>&1
```

## Step 10: SSL Configuration

### Option A: Auto SSL (cPanel Built-in)

1. Navigate to **SSL/TLS Status** in cPanel
2. Select your domain
3. Click **Run AutoSSL**
4. Wait for the certificate to be issued

### Option B: Let's Encrypt (via cPanel)

1. Navigate to **Let's Encrypt™ SSL** or **SSL/TLS**
2. Select your domain
3. Click **Issue** to install the certificate

### Option C: Upload Custom Certificate

1. Navigate to **SSL/TLS** → **Manage SSL Hosts**
2. Upload your certificate files:
   - Certificate (CRT)
   - Private Key (KEY)
   - Certificate Authority Bundle (CABUNDLE)

## Step 11: Configure PHP Version

1. Navigate to **MultiPHP Manager** in cPanel
2. Select your domain
3. Choose PHP 8.1 or higher
4. Click **Apply**

## Step 12: Optimize Performance

### PHP Settings (via MultiPHP INI Editor)

1. Navigate to **MultiPHP INI Editor**
2. Select your domain
3. Set recommended values:

```ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 100M
post_max_size = 100M
max_input_vars = 3000
```

### OPcache (If available)

Add to PHP configuration:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
```

## Common Issues & Fixes

### 500 Internal Server Error

**Causes:**
- Incorrect `.env` configuration
- Missing application key
- Permission issues
- PHP version incompatibility

**Fixes:**
1. Check `.env` file for errors
2. Verify `APP_KEY` is set
3. Set correct permissions on `storage/` and `bootstrap/cache/`
4. Check PHP version is 8.1+

### Permission Denied Errors

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R yourusername:yourusername storage bootstrap/cache
```

### Database Connection Failed

1. Verify database credentials in `.env`
2. Check database name includes cPanel prefix
3. Ensure database user has all privileges
4. Confirm MySQL host is `localhost`

### Composer Not Found

Install Composer in your home directory:
```bash
cd ~
curl -sS https://getcomposer.org/installer | php
mv composer.phar ~/public_html/composer
alias composer="php ~/public_html/composer"
```

### Memory Limit Exceeded

Increase in MultiPHP INI Editor:
```ini
memory_limit = 512M
```

### Asset Not Found (404 for CSS/JS)

1. Run `npm run build` and upload the `public/build` directory
2. Or upload pre-built assets from local build

## Post-Deployment Checklist

- [ ] `.env` file configured with correct values
- [ ] Application key generated
- [ ] Database migrations run
- [ ] Storage directory writable
- [ ] SSL certificate installed
- [ ] Cron job configured
- [ ] PHP version 8.1+ set
- [ ] OPcache enabled (if available)
- [ ] Test application functionality
- [ ] Remove installation files (if any)
- [ ] Set `APP_DEBUG=false`
- [ ] Configure backup schedule

## Support

For issues specific to your hosting provider, contact their support team. For application issues, refer to the main documentation or GitHub issues.
