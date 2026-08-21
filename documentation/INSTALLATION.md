# Installation Guide — VentureX ERP & CRM

**AI-Powered CRM & ERP Business Operating System**

Complete, step-by-step instructions for installing VentureX ERP & CRM on any environment. Follow the numbered steps — each step includes a screenshot reference so you can verify you're on the right track.

---

> **ðŸ“¸ Screenshot Note:** This guide references screenshots in the `images/` folder. Available screenshots are SVG files showing key installation steps.

---

## Table of Contents

- [Quick Start (5 Minutes)](#quick-start-5-minutes)
- [What You Need Before Starting](#what-you-need-before-starting)
- [Method 1 — Local Computer (Easiest)](#method-1--local-computer-easiest)
- [Method 2 — VPS / Cloud Server](#method-2--vps--cloud-server)
- [Method 3 — Shared Hosting (cPanel)](#method-3--shared-hosting-cpanel)
- [Method 4 — Docker](#method-4--docker)
- [Method 5 — Laravel Herd (Fastest)](#method-5--laravel-herd-fastest)
- [After Installation](#after-installation)
- [Troubleshooting](#troubleshooting)
- [Screenshots Checklist](#screenshots-checklist)

---

## Quick Start (5 Minutes)

If you just want to get it running fast, here's the 5-step summary:

```
1. Extract the ZIP file
2. Run: composer install
3. Run: cp .env.example .env && php artisan key:generate
4. Create a MySQL database, update .env, run: php artisan migrate --seed
5. Run: npm install && npm run build && php artisan serve
```

Open **http://localhost:8000** and log in with demo credentials:

| Field | Value |
|-------|-------|
| Email | `demo_admin@example.com` |
| Password | `Demo_Admin_2026!` |

> âš ï¸ **Security Notice**: Change these credentials before production use.

> ðŸ“¸ **Screenshot:** Login screen after installation.

---

## What You Need Before Starting

Before installing, make sure you have these installed on your computer:

| Software | What It Does | Download |
|----------|-------------|----------|
| **PHP 8.3+** | Runs the application | https://windows.php.net/download/ |
| **MySQL 8.0+** | Stores your data | https://dev.mysql.com/downloads/mysql/ |
| **Composer** | Installs PHP packages | https://getcomposer.org/download/ |
| **Node.js 20+** | Builds frontend assets | https://nodejs.org/ |
| **Git** (optional) | Version control | https://git-scm.com/download/win |

### Check If You Have Everything

Open your terminal (Command Prompt or PowerShell) and run these commands one by one:

```bash
php -v
```
You should see something like `PHP 8.3.x` or higher.

```bash
mysql --version
```
You should see `MySQL 8.0.x` or higher.

```bash
composer --version
```
You should see `Composer version 2.x.x`.

```bash
node -v
```
You should see `v20.x.x` or higher.

> ðŸ“¸ **Screenshot:** Terminal showing all version numbers.

### Required PHP Extensions

Make sure these PHP extensions are enabled. Run this command to check:

```bash
php -m
```

You need these extensions to appear in the list:
- `mbstring`
- `xml`
- `curl`
- `zip`
- `bcmath`
- `gd`
- `pdo_mysql`

If any are missing, enable them in your `php.ini` file by removing the `;` from the extension line.

---

## Method 1 — Local Computer (Easiest)

This is the simplest way to install VentureX ERP & CRM on your own Windows, Mac, or Linux computer.

### Step 1 — Extract the Files

1. Unzip the `VentureX-ERP-UPLOAD.zip` file
2. Move the extracted folder to your projects folder (e.g., `C:\projects\VentureX-ERP` or `~/projects/VentureX-ERP`)

> ðŸ“¸ **Screenshot:** `images/step-01-extract.svg` — The extracted folder structure showing all files.

### Step 2 — Install PHP Dependencies

Open your terminal in the project folder and run:

```bash
cd /path/to/VentureX-ERP
composer install
```

This installs all the PHP packages the app needs. Wait for it to finish (takes 1-2 minutes).

> ðŸ“¸ **Screenshot:** `images/step-02-composer.svg` — Composer installing packages (green checkmarks).

### Step 3 — Set Up Environment File

Run these commands to create your configuration file and generate a security key:

```bash
cp .env.example .env
php artisan key:generate
```

> ðŸ“¸ **Screenshot:** `images/step-03-configure.svg` — Terminal showing "Application key set successfully."

### Step 4 — Create Your Database

Open MySQL and create a database:

```bash
mysql -u root -p
```

Enter your MySQL password when prompted, then run:

```sql
CREATE DATABASE VENTUREX_ERP CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'VENTUREX_ERP_user'@'127.0.0.1' IDENTIFIED BY 'your_password_here';
GRANT ALL PRIVILEGES ON VENTUREX_ERP.* TO 'VENTUREX_ERP_user'@'127.0.0.1';
FLUSH PRIVILEGES;
EXIT;
```

> ðŸ“¸ **Screenshot:** MySQL terminal showing "Query OK" messages.

### Step 5 — Configure Database Connection

Open the `.env` file in a text editor and update these lines:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=VENTUREX_ERP
DB_USERNAME=VENTUREX_ERP_user
DB_PASSWORD=your_password_here
```

> ðŸ“¸ **Screenshot:** The .env file with database settings highlighted.

### Step 6 — Run Migrations (Creates All Tables)

```bash
php artisan migrate --seed
```

This creates all the database tables and fills them with default data including your admin user.

Type `yes` when asked about running pending migrations.

> ðŸ“¸ **Screenshot:** `images/step-04-migrate.svg` — Terminal showing all migrations running successfully.

### Step 7 — Install JavaScript Dependencies & Build

```bash
npm install
npm run build
```

> ðŸ“¸ **Screenshot:** npm installing packages and building assets.

### Step 8 — Start the Application

```bash
php artisan serve
```

The server starts at **http://localhost:8000**.

> ðŸ“¸ **Screenshot:** Terminal showing "Server running on http://127.0.0.1:8000".

### Step 9 — Open in Browser & Log In

1. Open your browser and go to **http://localhost:8000**
2. You'll see the login page
3. Enter the demo credentials:

| Field | Value |
|-------|-------|
| Email | `demo_admin@example.com` |
| Password | `Demo_Admin_2026!` |

4. Click **Login**

> ðŸ“¸ **Screenshot:** The main dashboard after logging in.

### Step 10 — Change Admin Password

1. Go to **Settings** or your **Profile**
2. Change the default password to something secure
3. Update the admin email to your real email

> ðŸ“¸ **Screenshot:** Password change screen.

---

## Method 2 — VPS / Cloud Server

Works with DigitalOcean, Linode, AWS EC2, Vultr, Hetzner, or any VPS running Ubuntu 22.04/24.04.

### Step 1 — SSH Into Your Server

```bash
ssh root@your_server_ip
```

### Step 2 — Update System

```bash
apt update && apt upgrade -y
```

### Step 3 — Install PHP 8.3

```bash
apt install -y software-properties-common
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y php8.3 php8.3-cli php8.3-fpm php8.3-mbstring php8.3-xml php8.3-curl \
  php8.3-zip php8.3-bcmath php8.3-gd php8.3-mysql php8.3-intl php8.3-redis php8.3-opcache
```

### Step 4 — Install MySQL 8.0

```bash
apt install -y mysql-server
mysql_secure_installation
```

Answer the prompts:
- Set up VALIDATE PASSWORD: **Yes**
- Set root password: choose a strong password
- Remove anonymous users: **Yes**
- Disallow root login remotely: **Yes**
- Remove test database: **Yes**
- Reload privilege tables: **Yes**

### Step 5 — Install Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs
```

Verify:
```bash
node -v
npm -v
```

### Step 6 — Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

### Step 7 — Upload & Install Files

```bash
mkdir -p /var/www/VentureX-ERP
cd /var/www
# Upload your ZIP via SCP or SFTP, then:
unzip VentureX-ERP-UPLOAD.zip -d VentureX-ERP
cd VentureX-ERP
chown -R www-data:www-data /var/www/VentureX-ERP

sudo -u www-data composer install --optimize-autoloader --no-dev
sudo -u www-data npm install
sudo -u www-data npm run build
```

### Step 8 — Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE VENTUREX_ERP CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'VENTUREX_ERP_user'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON VENTUREX_ERP.* TO 'VENTUREX_ERP_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 9 — Configure Environment

```bash
cd /var/www/VentureX-ERP
sudo -u www-data cp .env.example .env
sudo -u www-data php artisan key:generate
```

Edit `.env`:

```ini
APP_NAME="VentureX ERP & CRM"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=VENTUREX_ERP
DB_USERNAME=VENTUREX_ERP_user
DB_PASSWORD=YOUR_STRONG_PASSWORD

SESSION_DRIVER=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Step 10 — Configure Nginx

```bash
apt install -y nginx
```

Create `/etc/nginx/sites-available/VentureX-ERP`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/VentureX-ERP/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 64M;
}
```

```bash
ln -s /etc/nginx/sites-available/VentureX-ERP /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
nginx -t
systemctl restart nginx
```

### Step 11 — SSL Certificate (Required)

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d yourdomain.com -d www.yourdomain.com
certbot renew --dry-run
```

### Step 12 — Set Permissions & Run Migrations

```bash
cd /var/www/VentureX-ERP
chown -R www-data:www-data storage bootstrap/cache
find storage -type d -exec chmod 775 {} \;
find bootstrap/cache -type d -exec chmod 775 {} \;

sudo -u www-data php artisan migrate --seed
```

### Step 13 — Set Up Cron Job

```bash
crontab -u www-data -e
```

Add:
```
* * * * * cd /var/www/VentureX-ERP && php artisan schedule:run >> /dev/null 2>&1
```

### Step 14 — Verify

Open **https://yourdomain.com** and log in with the default credentials.

> ðŸ“¸ **Screenshot:** Dashboard running on your live domain.

---

## Method 3 — Shared Hosting (cPanel)

### Step 1 — Build Locally First

On your local computer:

```bash
cd /path/to/VentureX-ERP
composer install --optimize-autoloader --no-dev
npm install && npm run build
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your hosting database details (you'll create the database in Step 3).

### Step 2 — Upload Files

1. Log in to **cPanel**
2. Open **File Manager**
3. Navigate to `public_html/` (or your subdomain directory)
4. Upload the entire `VentureX-ERP` folder contents

> ðŸ“¸ **Screenshot:** cPanel File Manager showing uploaded files.

### Step 3 — Create Database in cPanel

1. Go to **cPanel â†’ MySQL Databases**
2. Create database: `yourcpaneluser_VENTUREX_ERP`
3. Create database user with a strong password
4. Add user to database with **All Privileges**

> ðŸ“¸ **Screenshot:** cPanel database creation screen.

### Step 4 — Configure .env

Edit `.env` with your hosting details:

```ini
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=yourcpaneluser_VENTUREX_ERP
DB_USERNAME=yourcpaneluser_dbuser
DB_PASSWORD=your_database_password
```

### Step 5 — Set Permissions

In cPanel File Manager:
- Right-click `storage/` â†’ Permissions â†’ **777** (recursive)
- Right-click `bootstrap/cache/` â†’ Permissions â†’ **777** (recursive)

### Step 6 — Import Database

If SSH is available:
```bash
php artisan migrate --seed
```

If SSH is NOT available:
1. Export database from local: `mysqldump -u root -p VENTUREX_ERP > database.sql`
2. In cPanel, go to **phpMyAdmin**
3. Select your database
4. Click **Import** â†’ Upload `database.sql`

> ðŸ“¸ **Screenshot:** phpMyAdmin import screen.

### Step 7 — Configure Document Root

Your hosting must point to the `public/` folder. In cPanel:
1. Go to **Addon Domains** or **Subdomains**
2. Set document root to `public_html/public`

If you can't change document root, move `public/index.php` to `public_html/` and edit the paths inside it.

### Step 8 — Verify

Open **https://yourdomain.com** and log in.

---

## Method 4 — Docker

### Step 1 — Create Dockerfile

Create a `Dockerfile` in the project root:

```dockerfile
FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev libicu-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring xml curl zip bcmath gd intl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build
RUN cp .env.example .env && php artisan key:generate
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
```

### Step 2 — Create docker-compose.yml

```yaml
version: "3.9"

services:
  app:
    build: .
    container_name: VentureX-ERP-app
    restart: unless-stopped
    working_dir: /var/www/html
    ports:
      - "8000:8000"
    volumes:
      - .:/var/www/html
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_PORT=3306
      - DB_DATABASE=VENTUREX_ERP
      - DB_USERNAME=VENTUREX_ERP_user
      - DB_PASSWORD=CHANGEME_strong_password_here
    networks:
      - VentureX-ERP-network

  db:
    image: mysql:8.0
    container_name: VentureX-ERP-db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: VENTUREX_ERP
      MYSQL_USER: VENTUREX_ERP_user
      MYSQL_PASSWORD: CHANGEME_strong_password_here
      MYSQL_ROOT_PASSWORD: CHANGEME_strong_root_password_here
    volumes:
      - db_data:/var/lib/mysql
    ports:
      - "3306:3306"
    networks:
      - VentureX-ERP-network

volumes:
  db_data:

networks:
  VentureX-ERP-network:
```

### Step 3 — Run

```bash
docker compose up -d
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan serve --host=0.0.0.0 --port=8000
```

Open **http://localhost:8000**.

> ðŸ“¸ **Screenshot:** Docker containers running successfully.

---

## Method 5 — Laravel Herd (Fastest)

Laravel Herd is the easiest way to run PHP apps on macOS and Windows.

### Step 1 — Install Herd

Download from https://herd.laravel.com and install.

### Step 2 — Install MySQL via Herd

1. Open Herd
2. Go to **Services**
3. Click **Install** next to MySQL
4. Start the MySQL service

### Step 3 — Add Your Project

```bash
herd clone <repository-url> VentureX-ERP
```

Or place the project in `~/Herd/VentureX-ERP`.

### Step 4 — Configure

```bash
cd ~/Herd/VentureX-ERP
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```ini
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=VENTUREX_ERP
DB_USERNAME=root
DB_PASSWORD=
```

### Step 5 — Create Database & Migrate

```bash
mysql -u root -e "CREATE DATABASE VENTUREX_ERP CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed
npm install && npm run build
```

### Step 6 — Open

Herd automatically assigns a `.test` domain. Open **http://VentureX-ERP.test** in your browser.

> ðŸ“¸ **Screenshot:** Dashboard running via Laravel Herd.

---

## After Installation

### Change Default Credentials

| What | Default Value | Action |
|------|--------------|--------|
| Admin Email | `demo_admin@example.com` | Change to your email |
| Admin Password | `Demo_Admin_2026!` | Change immediately |
| App Name | `VentureX ERP & CRM` | Customize in `.env` |
| App URL | `http://localhost:8000` | Update to your domain |

### Post-Installation Checklist

- [ ] Changed admin password
- [ ] Updated `APP_URL` in `.env` to your domain
- [ ] Set `APP_DEBUG=false` in production
- [ ] Configured mail settings in `.env`
- [ ] Set up automated backups
- [ ] Verified SSL certificate is active (production)
- [ ] Set up cron job (production)
- [ ] Configured Redis caching (production)

### Default User Roles

| Role | Description |
|------|-------------|
| super_admin | Full system access |
| company_admin | Company-level administration |
| ceo | Executive dashboard and reports |
| cfo | Financial oversight |
| sales_manager | Sales team management |
| sales_rep | Sales representative access |
| procurement_manager | Procurement operations |
| operations_manager | Logistics and inventory |
| hr_manager | Human resources |
| support_agent | Customer support |
| viewer | Read-only access |

---

## Troubleshooting

### Common Issues

| Problem | Cause | Solution |
|---------|-------|----------|
| **"No application encryption key"** | `.env` missing APP_KEY | Run `php artisan key:generate` |
| **"Connection refused"** | MySQL not running | Start MySQL: `systemctl start mysql` |
| **"Access denied for user"** | Wrong credentials | Verify username/password in `.env` |
| **"Could not find driver"** | Missing PHP extension | Install `php8.3-mysql` extension |
| **Permission denied on storage/** | Wrong file permissions | Run `chmod -R 775 storage bootstrap/cache` |
| **Blank white screen** | PHP error | Check `storage/logs/laravel.log` |
| **500 error in production** | Debug mode on | Set `APP_DEBUG=false` in `.env` |
| **Assets not loading (404)** | Missing build | Run `npm install && npm run build` |
| **"Class not found"** | Autoloader issue | Run `composer dump-autoload` |

### Useful Commands

```bash
php artisan about          # Show app info
php artisan route:list     # List all routes
php artisan tinker         # Interactive REPL
php artisan cache:clear    # Clear cache
php artisan view:clear     # Clear views
php artisan config:clear   # Clear config
tail -100 storage/logs/laravel.log  # Check errors
```

---

## Screenshots Checklist

When setting up your marketplace listing, take these screenshots from a running installation:

### Required Screenshots (Place in `images/` folder)

| Filename | What to Capture |
|----------|----------------|
| `step-01-extract.svg` | Extracted folder structure |
| `step-02-composer.svg` | Composer install completing |
| `step-03-configure.svg` | Environment file setup |
| `step-04-migrate.svg` | Migration running |
| `step-overview.svg` | Installation overview diagram |

### Additional Feature Screenshots

| Filename | What to Capture |
|----------|----------------|
| `feature-crm.png` | CRM module — customers list |
| `feature-sales.png` | Sales — quotations or invoices |
| `feature-inventory.png` | Inventory — products list |
| `feature-finance.png` | Finance — accounts dashboard |
| `feature-ai.png` | AI Copilot or Assistant |
| `feature-support.png` | Support ticket system |
| `feature-pipeline.png` | Sales pipeline board |
| `feature-mobile.png` | Mobile responsive view |

---

## Need Help?

If you need installation help, contact us at **support@venturexerp.com**

We also offer paid installation services for $49 — includes full server setup, database configuration, SSL, and optimization.

---

*Last updated: August 2026*
