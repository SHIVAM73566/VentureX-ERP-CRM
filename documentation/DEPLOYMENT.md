# Deployment Guide

**VentureX ERP & CRM â€” AI-Powered CRM & ERP Business Operating System**

> Version 1.0.0 | Production Deployment Instructions

---

## Table of Contents

1. [Server Requirements](#server-requirements)
2. [Pre-Deployment Checklist](#pre-deployment-checklist)
3. [Apache Configuration](#apache-configuration)
4. [Nginx Configuration](#nginx-configuration)
5. [PHP Configuration](#php-configuration)
6. [Queue Workers](#queue-workers)
7. [Task Scheduler](#task-scheduler)
8. [HTTPS Setup](#https-setup)
9. [Docker Deployment](#docker-deployment)
10. [Post-Deployment Verification](#post-deployment-verification)
11. [Performance Optimization](#performance-optimization)
12. [Monitoring](#monitoring)

---

## Server Requirements

### Minimum Production Requirements

| Component      | Minimum                          | Recommended                    |
|----------------|----------------------------------|--------------------------------|
| OS             | Ubuntu 22.04 LTS                 | Ubuntu 24.04 LTS              |
| PHP            | 8.2.0                            | 8.3+                          |
| Extensions     | ctype, curl, dom, fileinfo, filter, hash, mbstring, openssl, pcre, pdo, tokenizer, xml, zip, bcmath, gd, intl | All minimum + opcache, redis |
| MySQL          | 8.0                              | 8.0+ with InnoDB              |
| Web Server     | Apache 2.4+ or Nginx 1.24+      | Nginx 1.26+                   |
| Node.js        | 20.0+                            | 20 LTS                        |
| Composer        | 2.8+                            | Latest stable                 |
| Disk Space     | 5 GB                             | 20 GB SSD                     |
| RAM            | 4 GB                             | 8 GB+                         |
| CPU            | 2 cores                          | 4+ cores                      |
| SSL/TLS        | Required                         | Required                      |

### Optional Services

| Service      | Purpose                          |
|--------------|----------------------------------|
| Redis        | Caching and sessions             |
| Supervisor   | Queue worker management          |
| Node Exporter| Performance monitoring           |
| Fail2ban     | Intrusion prevention             |

---

## Pre-Deployment Checklist

- [ ] Server meets minimum requirements
- [ ] PHP 8.2+ installed with all required extensions
- [ ] MySQL 8.0 installed and configured
- [ ] Web server installed and configured
- [ ] SSL certificate obtained and installed
- [ ] Domain name pointed to server IP
- [ ] Firewall configured (ports 80, 443, 22)
- [ ] SSH access configured with key-based authentication
- [ ] Code repository cloned to server
- [ ] `.env` file configured with production values
- [ ] `APP_DEBUG=false` in `.env`
- [ ] `APP_ENV=production` in `.env`
- [ ] Application key generated
- [ ] Migrations run successfully
- [ ] Seeders run (if needed for initial data)
- [ ] Frontend assets built
- [ ] Storage symlink created
- [ ] Cache warmed
- [ ] Queue workers running
- [ ] Scheduler configured
- [ ] Backup system configured

---

## Apache Configuration

### Virtual Host

Create `/etc/apache2/sites-available/VentureX-ERP.conf`:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    Redirect permanent / https://your-domain.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName your-domain.com
    ServerAlias www.your-domain.com

    DocumentRoot /var/www/VentureX-ERP/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/your-domain.crt
    SSLCertificateKeyFile /etc/ssl/private/your-domain.key
    SSLCertificateChainFile /etc/ssl/certs/your-domain-chain.crt

    <Directory /var/www/VentureX-ERP/public>
        AllowOverride All
        Require all granted
        FallbackResource /index.php
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/VentureX-ERP-error.log
    CustomLog ${APACHE_LOG_DIR}/VentureX-ERP-access.log combined

    # Security Headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:;"

    # Compression
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/plain text/css text/javascript application/javascript application/json
    </IfModule>

    # Caching
    <IfModule mod_expires.c>
        ExpiresActive On
        ExpiresByType text/css "access plus 1 year"
        ExpiresByType application/javascript "access plus 1 year"
        ExpiresByType image/png "access plus 1 year"
        ExpiresByType image/jpg "access plus 1 year"
        ExpiresByType image/jpeg "access plus 1 year"
        ExpiresByType image/gif "access plus 1 year"
        ExpiresByType image/svg+xml "access plus 1 year"
    </IfModule>
</VirtualHost>
```

### Enable Required Modules

```bash
sudo a2enmod ssl rewrite headers expires deflate
sudo a2ensite VentureX-ERP
sudo systemctl restart apache2
```

---

## Nginx Configuration

### Server Block

Create `/etc/nginx/sites-available/VentureX-ERP`:

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://your-domain.com$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com www.your-domain.com;

    root /var/www/VentureX-ERP/public;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers
    add_header X-Content-Type-Options nosniff always;
    add_header X-Frame-Options SAMEORIGIN always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Logging
    access_log /var/log/nginx/VentureX-ERP-access.log;
    error_log /var/log/nginx/VentureX-ERP-error.log;

    # Location routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }

    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Deny dotfiles
    location ~ /\.(?!well-known) {
        deny all;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/json application/xml application/rss+xml image/svg+xml;
}
```

### Enable the Site

```bash
sudo ln -s /etc/nginx/sites-available/VentureX-ERP /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## PHP Configuration

### Recommended php.ini Settings

Edit your `php.ini` file (location varies by OS):

```ini
; Memory and Execution
memory_limit = 512M
max_execution_time = 300
max_input_time = 300

; File Uploads
upload_max_filesize = 64M
post_max_size = 128M
max_file_uploads = 20

; OPcache (critical for production)
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.revalidate_freq=0
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.jit=1255
opcache.jit_buffer_size=128M

; Sessions
session.gc_maxlifetime = 120
session.cookie_httponly = 1
session.cookie_secure = 1
session.cookie_samesite = Lax

; Error Handling (production)
display_errors = Off
log_errors = On
error_log = /var/log/php/VentureX-ERP-error.log
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT

; Security
expose_php = Off
allow_url_fopen = On
allow_url_include = Off
disable_functions = exec,passthru,shell_exec,system,proc_open,popen
```

### Verify PHP Configuration

```bash
php -i | grep "Loaded Configuration File"
php -m | grep -E "bcmath|curl|dom|fileinfo|filter|gd|hash|intl|mbstring|openssl|pdo_mysql|tokenizer|xml|zip"
```

---

## Queue Workers

### Using Supervisor (Recommended)

Create `/etc/supervisor/conf.d/VentureX-ERP-worker.conf`:

```ini
[program:VentureX-ERP-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/VentureX-ERP/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --max-jobs=1000
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/VentureX-ERP-worker.log
stopwaitsecs=3600
```

### Manage Workers

```bash
# Reload supervisor configuration
sudo supervisorctl reread
sudo supervisorctl update

# Check worker status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart VentureX-ERP-worker:*

# Stop workers
sudo supervisorctl stop VentureX-ERP-worker:*
```

### Using systemd

Create `/etc/systemd/system/VentureX-ERP-worker.service`:

```ini
[Unit]
Description=VentureX ERP & CRM Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/VentureX-ERP/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
RestartSec=10

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable VentureX-ERP-worker
sudo systemctl start VentureX-ERP-worker
```

---

## Task Scheduler

### Crontab Configuration

Add the following crontab entry:

```bash
# Edit the crontab
crontab -e

# Add this line:
* * * * * cd /var/www/VentureX-ERP && php artisan schedule:run >> /dev/null 2>&1
```

### Scheduled Tasks

The following tasks are configured in `app/Console/Kernel.php`:

| Task                          | Frequency           | Description                        |
|-------------------------------|---------------------|------------------------------------|
| backup:run                    | Daily at 02:00      | Database and file backup           |
| backup:clean                  | Weekly on Sunday    | Remove old backups                 |
| ai:quota:reset                | Daily at 00:00      | Reset daily AI quotas              |
| notifications:send-digest     | Daily at 08:00      | Send daily email digests           |
| reminders:overdue             | Daily at 09:00      | Send overdue payment reminders     |
| inventory:low-stock-check     | Every 6 hours       | Check and alert low stock          |
| reports:generate-scheduled    | Varies per report   | Generate scheduled reports         |
| cache:clear-stale             | Daily at 03:00      | Clear expired cache entries        |
| audit:cleanup                 | Monthly             | Archive old audit logs             |
| sessions:gc                   | Hourly              | Garbage collect expired sessions   |

### Verify Scheduler

```bash
php artisan schedule:list
php artisan schedule:run --verbose
```

---

## HTTPS Setup

### Using Let's Encrypt (Free)

```bash
# Install Certbot
sudo apt install -y certbot

# For Apache
sudo apt install -y python3-certbot-apache
sudo certbot --apache -d your-domain.com -d www.your-domain.com

# For Nginx
sudo apt install -y python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal (already set up by Certbot, but verify)
sudo certbot renew --dry-run
```

### Using a Commercial Certificate

```bash
# Generate a CSR
openssl req -new -newkey rsa:2048 -nodes \
  -keyout your-domain.key \
  -out your-domain.csr

# Submit the CSR to your certificate provider
# Install the received certificate
sudo cp your-domain.crt /etc/ssl/certs/
sudo cp your-domain.key /etc/ssl/private/
sudo cp intermediate.crt /etc/ssl/certs/

# Restart web server
sudo systemctl restart apache2   # or nginx
```

### Force HTTPS Redirect

Ensure all HTTP traffic redirects to HTTPS. This is configured in the virtual host/server block examples above.

### Verify HTTPS

```bash
# Test SSL configuration
curl -I https://your-domain.com
openssl s_client -connect your-domain.com:443 -servername your-domain.com
```

---

## Docker Deployment

### Production Docker Compose

Create `docker-compose.prod.yml`:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: VentureX-ERP-app
    restart: always
    ports:
      - "8000:8000"
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - APP_URL=https://your-domain.com
    volumes:
      - ./storage:/var/www/html/storage
      - ./.env:/var/www/html/.env
    depends_on:
      - mysql
      - redis
    networks:
      - erp-network

  nginx:
    image: nginx:alpine
    container_name: VentureX-ERP-nginx
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
      - ./storage:/var/www/html/storage
      - /etc/letsencrypt:/etc/letsencrypt:ro
    depends_on:
      - app
    networks:
      - erp-network

  mysql:
    image: mysql:8.0
    container_name: VentureX-ERP-mysql
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
      MYSQL_DATABASE: VENTUREX_ERP
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
    volumes:
      - mysql-data:/var/lib/mysql
      - ./mysql.cnf:/etc/mysql/conf.d/custom.cnf
    ports:
      - "3306:3306"
    networks:
      - erp-network

  redis:
    image: redis:7-alpine
    container_name: VentureX-ERP-redis
    restart: always
    command: redis-server --appendonly yes --maxmemory 256mb --maxmemory-policy allkeys-lru
    volumes:
      - redis-data:/data
    ports:
      - "6379:6379"
    networks:
      - erp-network

  queue:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: VentureX-ERP-queue
    restart: always
    command: php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
    volumes:
      - ./storage:/var/www/html/storage
      - ./.env:/var/www/html/.env
    depends_on:
      - mysql
      - redis
      - app
    networks:
      - erp-network

  scheduler:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: VentureX-ERP-scheduler
    restart: always
    command: >
      sh -c "while true; do php artisan schedule:run --verbose --no-interaction & sleep 60; done"
    volumes:
      - ./storage:/var/www/html/storage
      - ./.env:/var/www/html/.env
    depends_on:
      - mysql
      - redis
      - app
    networks:
      - erp-network

volumes:
  mysql-data:
  redis-data:

networks:
  erp-network:
    driver: bridge
```

### Deploy with Docker

```bash
# Build and start all services
docker compose -f docker-compose.prod.yml up -d --build

# Run initial setup
docker compose -f docker-compose.prod.yml exec app php artisan key:generate
docker compose -f docker-compose.prod.yml exec app php artisan migrate --seed
docker compose -f docker-compose.prod.yml exec app php artisan storage:link
docker compose -f docker-compose.prod.yml exec app npm run build

# View logs
docker compose -f docker-compose.prod.yml logs -f
```

---

## Post-Deployment Verification

### Verification Checklist

```bash
# 1. Check application status
curl -I https://your-domain.com

# 2. Verify HTTPS
curl -I https://your-domain.com | grep "Strict-Transport-Security"

# 3. Check PHP version
php -v

# 4. Verify database connection
php artisan migrate:status

# 5. Check cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Verify queue worker
php artisan queue:status

# 7. Check scheduler
php artisan schedule:list

# 8. Run health check
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000

# 9. Verify frontend assets
ls -la public/build/

# 10. Test login
curl -X POST https://your-domain.com/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@jainmetal.example","password":"YOUR_SEEDED_PASSWORD"}'
```

### Performance Benchmark

```bash
# Install Apache Bench or use wrk
ab -n 1000 -c 50 https://your-domain.com/

# Or with wrk
wrk -t12 -c400 -d30s https://your-domain.com/
```

---

## Performance Optimization

### Application-Level

```bash
# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize autoloader
composer dump-autoload --optimize --no-dev

# Remove development dependencies
composer install --no-dev --optimize-autoloader

# Build production frontend assets
npm run build
```

### Server-Level

- Enable OPcache with recommended settings above
- Configure page caching (Varnish or Nginx FastCGI cache)
- Enable Gzip/Brotli compression
- Set appropriate cache headers for static assets
- Use a CDN for static file delivery

### Database-Level

- Enable InnoDB buffer pool sizing
- Configure query cache appropriately
- Add indexes for frequently queried columns
- Use connection pooling for high-traffic scenarios
- Enable slow query log and optimize regularly

---

## Monitoring

### Application Health

Monitor the following endpoints and services:

| Check              | Command                                    |
|--------------------|--------------------------------------------|
| HTTP response      | `curl -o /dev/null -s -w "%{http_code}" https://your-domain.com` |
| Database           | `mysqladmin -u username -p ping`           |
| Queue workers      | `php artisan queue:work --once`            |
| Disk space         | `df -h /var/www/VentureX-ERP/storage`       |
| Memory usage       | `free -m`                                  |
| CPU load           | `uptime`                                   |

### Log Monitoring

```bash
# Application logs
tail -f storage/logs/laravel.log

# Web server logs
tail -f /var/log/nginx/VentureX-ERP-error.log    # Nginx
tail -f /var/log/apache2/VentureX-ERP-error.log   # Apache

# PHP error log
tail -f /var/log/php/VentureX-ERP-error.log

# Queue worker log
tail -f /var/log/supervisor/VentureX-ERP-worker.log
```

### Alerting

Configure alerts for:

- HTTP 5xx errors exceeding threshold
- Queue worker failures
- Disk space below 20%
- Memory usage above 90%
- Database connection failures
- SSL certificate expiration (within 30 days)

---

**Next Steps:**

- Read [SECURITY.md](SECURITY.md) for production security hardening
- Read [BACKUP_AND_RESTORE.md](BACKUP_AND_RESTORE.md) for automated backup configuration
- Read [TROUBLESHOOTING.md](TROUBLESHOOTING.md) for deployment troubleshooting
