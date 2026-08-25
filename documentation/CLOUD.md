# Cloud Deployment Guide for VentureX ERP & CRM

This guide provides a generic approach to deploying VentureX ERP & CRM on cloud infrastructure.

## Requirements

### Minimum Server Specifications

| Resource | Minimum | Recommended |
|----------|---------|-------------|
| CPU | 2 vCPU | 4 vCPU |
| RAM | 4GB | 8GB |
| Storage | 40GB SSD | 80GB SSD |
| Bandwidth | 1TB | 2TB |

### Software Requirements

- **Operating System**: Ubuntu 22.04 LTS or 24.04 LTS
- **Web Server**: Nginx or Apache 2.4
- **PHP**: 8.1 or higher (8.4 recommended)
- **Database**: MySQL 8.0+ or MariaDB 10.6+
- **Cache**: Redis 7.0+ (recommended) or Memcached
- **Node.js**: 20.x (for building frontend assets)

### Required PHP Extensions

- pdo_mysql
- mbstring
- openssl
- curl
- gd
- xml
- zip
- bcmath
- fileinfo
- intl
- opcache
- redis (optional, for Redis cache)

## Environment Variables Configuration

### Essential Variables

```env
# Application
APP_NAME=VentureX
APP_ENV=production
APP_KEY=base64:GENERATE_KEY
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=venturex
DB_USERNAME=venturex_user
DB_PASSWORD=secure_password

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=your-redis-host
REDIS_PASSWORD=redis_password
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=email-password
MAIL_ENCRYPTION=tls
```

### Generating Application Key

```bash
php artisan key:generate --show
```

Copy the output and set it as `APP_KEY`.

## Domain & SSL Setup

### DNS Configuration

Point your domain to your cloud server:

| Type | Name | Value | TTL |
|------|------|-------|-----|
| A | @ | your.server.ip | 300 |
| A | www | your.server.ip | 300 |
| CNAME | api | yourdomain.com | 300 |

### SSL Certificate

#### Option 1: Let's Encrypt (Free)

```bash
apt install certbot python3-certbot-nginx -y
certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

#### Option 2: Cloud Provider SSL

Most cloud providers offer free or paid SSL certificates:
- AWS Certificate Manager
- DigitalOcean Load Balancer SSL
- Cloudflare SSL (free tier available)
- Linode SSL

#### Option 3: Cloudflare (Recommended)

1. Sign up for Cloudflare (free tier)
2. Add your domain
3. Update nameservers at your registrar
4. Enable SSL (Full or Full Strict)
5. Enable HTTPS rewrite rules

## Deployment Methods

### Method 1: Git Deployment (Recommended)

#### Setup Git Repository

```bash
# On server
cd /var/www
git clone https://github.com/your-repo/venturex-erp.git venturex
cd venturex
```

#### Deploy Script

Create `/var/www/deploy.sh`:

```bash
#!/bin/bash
set -e

cd /var/www/venturex

echo "Pulling latest changes..."
git pull origin main

echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader

echo "Building frontend assets..."
npm ci
npm run build

echo "Running migrations..."
php artisan migrate --force

echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache

echo "Deployment complete!"
```

Make executable:
```bash
chmod +x /var/www/deploy.sh
```

#### Automated Deployment with Git Hooks

Create `/var/www/venturex/.git/hooks/post-receive`:
```bash
#!/bin/bash
DEPLOY_PATH="/var/www/venturex"
GIT_WORK_TREE=$DEPLOY_PATH git checkout -f
cd $DEPLOY_PATH
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
chown -R www-data:www-data storage bootstrap/cache
```

### Method 2: SCP/rsync Deployment

#### Local Build and Upload

```bash
# Local machine
composer install --no-dev --optimize-autoloader
npm ci && npm run build
tar -czf venturex-$(date +%Y%m%d).tar.gz venturex/

# Upload to server
scp venturex-*.tar.gz user@server:/tmp/

# On server
cd /var/www
tar -xzf /tmp/venturex-*.tar.gz
cd venturex
cp .env.example .env
# Configure .env...
php artisan key:generate
php artisan migrate --force
chown -R www-data:www-data .
```

### Method 3: Docker Deployment

```bash
# Clone repository
git clone https://github.com/your-repo/venturex-erp.git
cd venturex-erp

# Copy Docker environment
cp docker/.env.docker .env

# Configure .env
nano .env

# Build and start
docker-compose -f docker/docker-compose.yml -f docker/docker-compose.prod.yml up -d

# Run migrations
docker exec venturex-app php artisan migrate --force

# Seed database (optional)
docker exec venturex-app php artisan db:seed
```

## Backup Strategy

### Database Backups

#### Automated Daily Backups

```bash
# Create backup script
nano /usr/local/bin/backup-db.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/backups/mysql"
DATE=$(date +%Y-%m-%d_%H-%M-%S)
DB_NAME="venturex"
DB_USER="venturex_user"
DB_PASS="password"

mkdir -p $BACKUP_DIR

mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/venturex_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete
```

#### Setup Cron for Daily Backups

```bash
chmod +x /usr/local/bin/backup-db.sh
crontab -e
```

Add:
```
0 2 * * * /usr/local/bin/backup-db.sh
```

### File Backups

#### Using rsync

```bash
#!/bin/bash
SOURCE="/var/www/venturex"
DEST="/backups/files"
DATE=$(date +%Y-%m-%d)

rsync -avz --exclude='node_modules' --exclude='.git' $SOURCE $DEST/venturex_$DATE/
```

### Cloud Storage Backup

#### AWS S3

```bash
apt install awscli -y
aws configure

# Backup script
#!/bin/bash
DATE=$(date +%Y-%m-%d)
mysqldump venturex | gzip | aws s3 cp - s3://your-bucket/backups/venturex_$DATE.sql.gz
```

#### DigitalOcean Spaces

```bash
# Using s3cmd
s3cmd put /backups/venturex_*.sql.gz s3://your-space/backups/
```

### Backup Schedule

| Item | Frequency | Retention |
|------|-----------|-----------|
| Database | Daily | 30 days |
| Files | Weekly | 4 weeks |
| Full System | Monthly | 12 months |

## Scaling Considerations

### Horizontal Scaling

#### Load Balancer Setup

1. Deploy multiple application servers
2. Configure load balancer (round-robin or least connections)
3. Use shared storage (NFS, S3, or object storage)
4. Use external database (managed MySQL)

#### Session Handling

Use Redis or database for sessions (already configured):
```env
SESSION_DRIVER=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

### Vertical Scaling

#### Resource Upgrade Path

| Stage | CPU | RAM | Storage |
|-------|-----|-----|---------|
| Development | 2 | 4GB | 40GB |
| Small Production | 4 | 8GB | 80GB |
| Medium Production | 8 | 16GB | 160GB |
| Large Production | 16+ | 32GB+ | 320GB+ |

### Database Scaling

#### Read Replicas

For high-traffic applications:
1. Set up MySQL replication
2. Configure read/write splitting in Laravel
3. Use `DB_READ_HOST` for read operations

#### Managed Database Services

Consider managed database services:
- AWS RDS
- DigitalOcean Managed Databases
- Linode Managed Databases
- Google Cloud SQL

### Caching Strategy

#### Redis Cluster

For high availability:
1. Set up Redis Sentinel or Cluster
2. Configure Laravel to use multiple Redis servers
3. Implement cache tags for efficient invalidation

#### CDN Integration

Use CDN for static assets:
1. Cloudflare (free tier)
2. AWS CloudFront
3. DigitalOcean CDN
4. Fastly

## Popular Cloud Providers

### AWS (Amazon Web Services)

**Best for**: Enterprise, scalability, global reach

**Services to use**:
- EC2 (compute)
- RDS (managed database)
- ElastiCache (Redis)
- S3 (file storage)
- CloudFront (CDN)
- Route 53 (DNS)
- Certificate Manager (SSL)

**Estimated cost**: $50-200/month for small production

### DigitalOcean

**Best for**: Simplicity, developer-friendly, cost-effective

**Services to use**:
- Droplets (compute)
- Managed Databases
- Managed Redis
- Spaces (object storage)
- Load Balancers
- App Platform (PaaS)

**Estimated cost**: $40-150/month for small production

### Linode (Akamai)

**Best for**: Performance, simplicity, good support

**Services to use**:
- Linodes (compute)
- Managed Databases
- NodeBalancers (load balancer)
- Object Storage
- Block Storage

**Estimated cost**: $40-120/month for small production

### Vultr

**Best for**: Performance, global locations, hourly billing

**Services to use**:
- Cloud Compute
- Managed Database
- Load Balancers
- Object Storage
- DNS

**Estimated cost**: $35-100/month for small production

### Google Cloud Platform

**Best for**: Data analytics, machine learning, global network

**Services to use**:
- Compute Engine
- Cloud SQL
- Memorystore (Redis)
- Cloud Storage
- Cloud CDN
- Cloud DNS

**Estimated cost**: $50-180/month for small production

### Azure

**Best for**: Enterprise integration, Microsoft ecosystem

**Services to use**:
- Virtual Machines
- Azure Database for MySQL
- Azure Cache for Redis
- Blob Storage
- Azure CDN
- Azure DNS

**Estimated cost**: $60-200/month for small production

### Hetzner

**Best for**: Cost-effective, European data centers

**Services to use**:
- Cloud Servers
- Managed Databases
- Load Balancers
- Block Storage
- Object Storage

**Estimated cost**: $20-80/month for small production

### Cloudflare (Pages/Workers)

**Best for**: Edge computing, free tier, security

**Services to use**:
- Cloudflare Pages (static)
- Workers (serverless)
- R2 (object storage)
- D1 (database)
- CDN and security

**Estimated cost**: Free tier available, $5-20/month for basic

## Post-Deployment Checklist

- [ ] Server provisioned and secured
- [ ] LAMP/LEMP stack installed
- [ ] Application deployed
- [ ] Environment variables configured
- [ ] Database created and migrated
- [ ] SSL certificate installed
- [ ] DNS configured
- [ ] Backup strategy implemented
- [ ] Monitoring enabled
- [ ] Firewall configured
- [ ] Performance optimized
- [ ] Load testing completed (if applicable)
- [ ] Documentation updated
- [ ] Team access configured
- [ ] Emergency contacts established

## Cost Optimization Tips

1. **Start small**: Begin with minimum resources, scale as needed
2. **Use reserved instances**: For predictable workloads (AWS, DigitalOcean)
3. **Enable auto-scaling**: Handle traffic spikes efficiently
4. **Use spot instances**: For non-critical workloads (AWS, GCP)
5. **Monitor resource usage**: Right-size based on actual usage
6. **Use CDN**: Reduce bandwidth costs
7. **Implement caching**: Reduce database load
8. **Schedule non-critical tasks**: Run during off-peak hours
