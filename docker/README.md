# Docker Configuration for VentureX ERP & CRM

This directory contains Docker configuration files for deploying VentureX ERP & CRM.

## Quick Start

### Development Environment

```bash
# Build and start containers
docker-compose up -d

# View logs
docker-compose logs -f

# Stop containers
docker-compose down
```

### Production Environment

```bash
# Copy environment file
cp .env.docker .env

# Edit .env with your settings
nano .env

# Start production environment
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d

# View logs
docker-compose logs -f
```

## Services

| Service | Description | Port |
|---------|-------------|------|
| app | PHP-FPM Application | 9000 |
| nginx | Web Server | 80, 443 |
| mysql | Database | 3306 |
| redis | Cache & Queue | 6379 |
| queue-worker | Laravel Queue Worker | - |
| scheduler | Laravel Scheduler | - |

## File Structure

```
docker/
├── Dockerfile              # Multi-stage build for PHP app
├── docker-compose.yml      # Base composition
├── docker-compose.prod.yml # Production overrides
├── .env.docker             # Environment template
├── supervisord.conf        # Process manager config
├── php/
│   └── local.ini          # PHP configuration
├── nginx/
│   ├── default.conf       # Nginx server block
│   ├── ssl/               # SSL certificates
│   └── logs/              # Nginx logs
└── mysql/
    ├── init/              # Database initialization
    └── backup/            # Database backups
```

## Commands

### Container Management

```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# Restart services
docker-compose restart

# View running containers
docker-compose ps

# View logs
docker-compose logs [service_name]

# Execute commands in container
docker-compose exec app php artisan --version
docker-compose exec mysql mysql -u root -p
```

### Laravel Commands

```bash
# Run migrations
docker-compose exec app php artisan migrate

# Seed database
docker-compose exec app php artisan db:seed

# Clear cache
docker-compose exec app php artisan cache:clear

# Generate key
docker-compose exec app php artisan key:generate

# Create admin user
docker-compose exec app php artisan tinker
```

### Queue Management

```bash
# View queue workers
docker-compose exec queue-worker php artisan queue:work --once

# Restart queue workers
docker-compose restart queue-worker

# Clear failed jobs
docker-compose exec app php artisan queue:flush
```

## Environment Variables

### Required Variables

| Variable | Description | Example |
|----------|-------------|---------|
| APP_NAME | Application name | VentureX |
| APP_ENV | Environment | production |
| APP_KEY | Encryption key | base64:... |
| APP_URL | Application URL | https://yourdomain.com |
| DB_DATABASE | Database name | venturex |
| DB_USERNAME | Database user | venturex |
| DB_PASSWORD | Database password | secure_password |

### Optional Variables

| Variable | Description | Default |
|----------|-------------|---------|
| APP_DEBUG | Debug mode | false |
| LOG_CHANNEL | Log channel | stack |
| CACHE_DRIVER | Cache driver | redis |
| SESSION_DRIVER | Session driver | redis |
| QUEUE_CONNECTION | Queue connection | redis |

## SSL Configuration

### Using Let's Encrypt

```bash
# Install certbot
apt install certbot

# Generate certificates
certbot certonly --standalone -d yourdomain.com

# Copy certificates to nginx/ssl/
cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem ./nginx/ssl/
cp /etc/letsencrypt/live/yourdomain.com/privkey.pem ./nginx/ssl/
```

### Self-Signed Certificates (Development)

```bash
# Generate self-signed certificate
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout ./nginx/ssl/privkey.pem \
    -out ./nginx/ssl/fullchain.pem
```

## Backup

### Database Backup

```bash
# Manual backup
docker-compose exec mysql mysqldump -u root -p venturex > backup_$(date +%Y%m%d).sql

# Automated backup (add to crontab)
0 2 * * * docker-compose exec mysql mysqldump -u root -p'password' venturex | gzip > /backups/venturex_$(date +\%Y\%m\%d).sql.gz
```

### Volume Backup

```bash
# Backup MySQL volume
docker run --rm -v venturex_mysql-data:/data -v $(pwd):/backup alpine tar czf /backup/mysql-data-backup.tar.gz /data
```

## Troubleshooting

### Container Won't Start

```bash
# Check logs
docker-compose logs [service_name]

# Check container status
docker-compose ps

# Rebuild containers
docker-compose build --no-cache
```

### Permission Issues

```bash
# Fix storage permissions
docker-compose exec app chmod -R 755 storage
docker-compose exec app chmod -R 755 bootstrap/cache

# Fix ownership
docker-compose exec app chown -R www:www storage bootstrap/cache
```

### Database Connection Issues

```bash
# Check MySQL is running
docker-compose ps mysql

# Test connection
docker-compose exec mysql mysql -u venturex -p

# Check environment variables
docker-compose exec app env | grep DB_
```

### Memory Issues

```bash
# Check container resources
docker stats

# Increase memory limit in docker-compose.prod.yml
deploy:
  resources:
    limits:
      memory: 4G
```

## Development

### Adding New Services

1. Add service to `docker-compose.yml`
2. Create Dockerfile if needed
3. Add to network configuration
4. Update `depends_on` as needed

### Custom PHP Extensions

1. Modify `Dockerfile`
2. Add extension installation commands
3. Rebuild: `docker-compose build --no-cache`

### Local Development

For local development without Docker:

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run development server
php artisan serve
```

## Support

For issues with Docker configuration:
1. Check container logs
2. Verify environment variables
3. Ensure ports are not in use
4. Check Docker version compatibility
