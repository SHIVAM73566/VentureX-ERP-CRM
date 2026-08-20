# VPS Deployment Guide for VentureX ERP & CRM

This guide covers deploying VentureX ERP & CRM on a VPS running Ubuntu 22.04 or 24.04.

## Prerequisites

- VPS with minimum:
  - 2 vCPU cores
  - 4GB RAM
  - 40GB SSD storage
  - Ubuntu 22.04 LTS or 24.04 LTS
  - Root or sudo access
  - Static IP address
  - Domain name pointed to server IP

## Step 1: Initial Server Setup

### Connect to Server

```bash
ssh root@your_server_ip
```

### Update System

```bash
apt update && apt upgrade -y
```

### Create Deploy User

```bash
adduser deploy
usermod -aG sudo deploy
```

### Setup SSH Key Authentication

```bash
# On your local machine
ssh-copy-id deploy@your_server_ip
```

### Basic Security

```bash
# Disable root login
sed -i 's/PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config
sed -i 's/PasswordAuthentication yes/PasswordAuthentication no/' /etc/ssh/sshd_config
systemctl restart sshd
```

## Step 2: Install LAMP Stack

### Install Nginx

```bash
apt install nginx -y
systemctl enable nginx
systemctl start nginx
```

### Install PHP 8.3

```bash
# Add PHP repository
apt install software-properties-common -y
add-apt-repository ppa:ondrej/php -y
apt update

# Install PHP 8.3 and extensions
apt install php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml \
    php8.3-curl php8.3-gd php8.3-zip php8.3-bcmath php8.3-intl \
    php8.3-opcache php8.3-redis -y
```

### Configure PHP

Edit PHP configuration:
```bash
nano /etc/php/8.3/fpm/php.ini
```

Update these settings:
```ini
memory_limit = 256M
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
max_input_vars = 3000
date.timezone = UTC
```

Restart PHP-FPM:
```bash
systemctl restart php8.3-fpm
```

### Install MySQL 8

```bash
apt install mysql-server -y
mysql_secure_installation
```

Follow the prompts:
- Set root password: Yes (use strong password)
- Remove anonymous users: Yes
- Disallow root login remotely: Yes
- Remove test database: Yes
- Reload privilege tables: Yes

### Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE venturex_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'venturex_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON venturex_db.* TO 'venturex_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## Step 3: Install Composer and Node.js

### Install Composer

```bash
cd /tmp
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

### Install Node.js (via NVM)

```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
source ~/.bashrc
nvm install 20
nvm use 20
```

## Step 4: Deploy Application

### Clone Repository

```bash
cd /var/www
git clone https://github.com/your-repo/venturex-erp.git venturex
cd venturex
```

### Install Dependencies

```bash
# PHP dependencies
composer install --no-dev --optimize-autoloader

# Frontend assets
npm install
npm run build
```

### Configure Environment

```bash
cp .env.example .env
nano .env
```

Update `.env` with:
```env
APP_NAME=VentureX
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=venturex_db
DB_USERNAME=venturex_user
DB_PASSWORD=your_secure_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Generate Application Key

```bash
php artisan key:generate
```

### Run Migrations

```bash
php artisan migrate --force
```

### Set Permissions

```bash
chown -R www-data:www-data /var/www/venturex
chmod -R 755 /var/www/venturex/storage
chmod -R 755 /var/www/venturex/bootstrap/cache
```

## Step 5: Configure Nginx

### Create Server Block

```bash
nano /etc/nginx/sites-available/venturex
```

Add configuration:
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;

    root /var/www/venturex/public;
    index index.php index.html;

    # SSL will be configured later with Certbot

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }

    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
}
```

### Enable Site

```bash
ln -s /etc/nginx/sites-available/venturex /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
```

## Step 6: SSL with Certbot

### Install Certbot

```bash
apt install certbot python3-certbot-nginx -y
```

### Obtain Certificate

```bash
certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### Auto-Renewal

Test renewal:
```bash
certbot renew --dry-run
```

Certbot sets up automatic renewal via systemd timer.

## Step 7: Redis Setup

### Install Redis

```bash
apt install redis-server -y
systemctl enable redis-server
systemctl start redis-server
```

### Configure Redis

```bash
nano /etc/redis/redis.conf
```

Set:
```
bind 127.0.0.1
requirepass your_redis_password
```

Update `.env`:
```env
REDIS_PASSWORD=your_redis_password
```

## Step 8: Queue Worker with Supervisor

### Install Supervisor

```bash
apt install supervisor -y
```

### Create Worker Configuration

```bash
nano /etc/supervisor/conf.d/venturex-worker.conf
```

Add:
```ini
[program:venturex-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/venturex/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/venturex-worker.log
stopwaitsecs=3600
```

### Start Workers

```bash
supervisorctl reread
supervisorctl update
supervisorctl start "venturex-worker:*"
```

## Step 9: Cron Jobs

### Application Scheduler

```bash
crontab -e -u www-data
```

Add:
```
* * * * * cd /var/www/venturex && php artisan schedule:run >> /dev/null 2>&1
```

### Database Backup (Daily)

```bash
crontab -e
```

Add:
```
0 2 * * * mysqldump -u venturex_user -p'your_password' venturex_db | gzip > /backups/venturex_$(date +\%Y\%m\%d).sql.gz
```

Create backup directory:
```bash
mkdir -p /backups
chmod 700 /backups
```

## Step 10: Firewall Configuration

### Install and Configure UFW

```bash
apt install ufw -y

# Default policies
ufw default deny incoming
ufw default allow outgoing

# Allow SSH
ufw allow 22/tcp

# Allow HTTP and HTTPS
ufw allow 80/tcp
ufw allow 443/tcp

# Enable firewall
ufw enable
ufw status verbose
```

### Optional: Restrict MySQL Access

```bash
ufw allow from 127.0.0.1 to any port 3306
```

## Step 11: Performance Tuning

### PHP-FPM Pool Configuration

```bash
nano /etc/php/8.3/fpm/pool.d/www.conf
```

Optimize:
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

### Nginx Optimization

```bash
nano /etc/nginx/nginx.conf
```

Update:
```nginx
worker_processes auto;
worker_rlimit_nofile 65535;

events {
    worker_connections 1024;
    multi_accept on;
    use epoll;
}

http {
    # ... existing config ...
    
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;
    client_max_body_size 100M;
    
    # Gzip
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
}
```

### MySQL Optimization

```bash
nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Add/modify:
```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 1
innodb_flush_method = O_DIRECT
max_connections = 100
query_cache_type = 1
query_cache_size = 64M
```

Restart MySQL:
```bash
systemctl restart mysql
```

## Step 12: Monitoring

### Install Basic Monitoring

```bash
apt install htop iotop nethogs -y
```

### Log Files

Monitor logs:
```bash
# Nginx
tail -f /var/log/nginx/error.log

# PHP-FPM
tail -f /var/log/php8.3-fpm.log

# Application
tail -f /var/www/venturex/storage/logs/laravel.log
```

### Set Up Log Rotation

```bash
nano /etc/logrotate.d/venturex
```

Add:
```
/var/www/venturex/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

## Post-Deployment Checklist

- [ ] Server updated and secured
- [ ] LAMP stack installed and configured
- [ ] Application deployed and dependencies installed
- [ ] `.env` configured with correct values
- [ ] Database created and migrations run
- [ ] Nginx configured with SSL
- [ ] Redis installed and configured
- [ ] Queue workers running with Supervisor
- [ ] Cron jobs configured
- [ ] Firewall enabled
- [ ] Performance optimized
- [ ] Monitoring in place
- [ ] Backup strategy configured
- [ ] DNS records pointing to server
- [ ] Application tested and working

## Useful Commands

```bash
# Restart services
systemctl restart nginx
systemctl restart php8.3-fpm
systemctl restart mysql
systemctl restart redis-server

# View logs
journalctl -u nginx
journalctl -u php8.3-fpm

# Laravel commands
cd /var/www/venturex
php artisan down              # Enable maintenance mode
php artisan up                # Disable maintenance mode
php artisan cache:clear       # Clear cache
php artisan config:cache      # Cache configuration
php artisan route:cache       # Cache routes
php artisan view:cache        # Cache views

# Supervisor
supervisorctl status
supervisorctl restart all
```

## Troubleshooting

### 502 Bad Gateway

- Check PHP-FPM is running: `systemctl status php8.3-fpm`
- Check socket exists: `ls -la /var/run/php/php8.3-fpm.sock`
- Check Nginx config: `nginx -t`

### Permission Issues

```bash
chown -R www-data:www-data /var/www/venturex
chmod -R 755 /var/www/venturex/storage
chmod -R 755 /var/www/venturex/bootstrap/cache
```

### Database Connection Issues

- Verify credentials in `.env`
- Check MySQL is running: `systemctl status mysql`
- Test connection: `mysql -u venturex_user -p venturex_db`

### Redis Connection Issues

- Check Redis is running: `systemctl status redis-server`
- Test connection: `redis-cli ping`
