# Troubleshooting Guide

Common issues and their solutions when running VentureX ERP & CRM.

---

## Installation Issues

### "No application encryption key has been specified"

**Cause:** `.env` file missing APP_KEY or key not generated.

**Solution:**
```bash
cp .env.example .env
php artisan key:generate
```

### Composer Install Fails with Memory Limit

**Cause:** PHP memory limit too low for Composer.

**Solution:**
```bash
php -d memory_limit=512M composer install
```

Or edit `php.ini`:
```ini
memory_limit = 512M
```

### "Class not found" Errors After Install

**Cause:** Autoload not generated.

**Solution:**
```bash
composer dump-autoload
```

### Vite Build Fails

**Cause:** Missing Node.js dependencies or incorrect version.

**Solution:**
```bash
rm -rf node_modules
npm install
npm run build
```

Verify Node.js version:
```bash
node --version  # Should be 20+
```

### Migration Errors

**Cause:** Database not created or MySQL version incompatible.

**Solution:**
```bash
# Verify MySQL version
mysql --version  # Should be 8.0+

# Create database if missing
mysql -u root -p -e "CREATE DATABASE VENTUREX_ERP CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate
```

### Permission Denied on Storage Directory

**Cause:** Web server user lacks write permissions.

**Solution (Linux):**
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

**Solution (Windows):**
Grant write permissions to IUSR and IIS_IUSRS on `storage/` and `bootstrap/cache/`.

---

## Authentication Issues

### Cannot Log In

1. **Verify credentials:** Ensure email and password are correct
2. **Check user status:** User may be deactivated
3. **Check database:** Verify user exists in `users` table
4. **Clear cache:**
   ```bash
   php artisan cache:clear
   ```

### MFA Code Not Working

1. **Check time sync:** Ensure authenticator app time is synced
2. **Verify setup:** Confirm MFA was set up correctly
3. **Admin reset:** Super admin can reset MFA from user profile
4. **Use backup codes:** If available, use one-time backup codes

### Session Expired Immediately

**Cause:** Session misconfiguration or cache driver issues.

**Solution:**
1. Verify session driver in `.env`:
   ```ini
   SESSION_DRIVER=file  # or redis
   ```
2. Check session directory permissions:
   ```bash
   chmod -R 775 storage/framework/sessions
   ```
3. If using Redis, verify Redis is running:
   ```bash
   redis-cli ping  # Should return PONG
   ```

### "403 Forbidden" on All Pages

**Cause:** User lacks necessary permissions.

**Solution:**
1. Check user role assignment in database
2. Verify role has required permissions
3. Clear permission cache:
   ```bash
   php artisan permission:cache-reset
   ```

---

## Database Issues

### "SQLSTATE[HY000] [2002] No such file or directory"

**Cause:** MySQL not running or incorrect socket path.

**Solution:**
1. Start MySQL:
   ```bash
   sudo systemctl start mysql
   ```
2. Check socket path in `.env`:
   ```ini
   DB_HOST=127.0.0.1
   DB_PORT=3306
   ```

### "Access denied for user"

**Cause:** Incorrect database credentials.

**Solution:**
1. Verify credentials in `.env`
2. Check MySQL user permissions:
   ```sql
   SHOW GRANTS FOR 'VENTUREX_ERP'@'localhost';
   ```

### "Table doesn't exist" Errors

**Cause:** Migrations not run.

**Solution:**
```bash
php artisan migrate --force
```

### Slow Query Performance

**Cause:** Missing indexes or large datasets.

**Solution:**
1. Check for missing indexes on frequently queried columns
2. Enable query logging to identify slow queries:
   ```php
   DB::enableQueryLog();
   // ... perform queries ...
   dd(DB::getQueryLog());
   ```
3. Add indexes:
   ```sql
   CREATE INDEX idx_customers_email ON customers(email);
   ```

---

## Frontend Issues

### Styles Not Loading

**Cause:** Vite manifest missing or assets not built.

**Solution:**
```bash
npm install
npm run build
```

Verify `public/build/manifest.json` exists.

### Alpine.js Not Working

**Cause:** JavaScript not loaded or Alpine.js initialization error.

**Solution:**
1. Verify Alpine.js is included in layout
2. Check browser console for JavaScript errors
3. Rebuild assets:
   ```bash
   npm run build
   ```

### Livewire Components Not Responding

**Cause:** Livewire JavaScript not loaded or AJAX errors.

**Solution:**
1. Check browser console for network errors
2. Verify Livewire is installed:
   ```bash
   composer show livewire/livewire
   ```
3. Clear Livewire cache:
   ```bash
   php artisan livewire:clear
   ```

### Hot Reload Not Working (Development)

**Cause:** Vite dev server not running or misconfigured.

**Solution:**
```bash
npm run dev
```

In separate terminal:
```bash
php artisan serve
```

---

## Queue Issues

### Jobs Not Processing

**Cause:** Queue worker not running.

**Solution:**
```bash
php artisan queue:work redis
```

Or with Supervisor (see DEPLOYMENT.md).

### Jobs Failing Repeatedly

**Cause:** Exception in job logic.

**Solution:**
1. Check failed jobs:
   ```bash
   php artisan queue:failed
   ```
2. Retry failed job:
   ```bash
   php artisan queue:retry {job-id}
   ```
3. Check logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Queue Worker Stops

**Cause:** Memory limit exceeded or unhandled exception.

**Solution:**
```bash
# Restart worker with memory limit
php artisan queue:work redis --memory=512

# Or use Supervisor for automatic restart
```

---

## File Upload Issues

### "File exceeds maximum upload size"

**Cause:** PHP or web server upload limit too low.

**Solution:**
1. Edit `php.ini`:
   ```ini
   upload_max_filesize = 64M
   post_max_size = 64M
   ```
2. Restart PHP-FPM:
   ```bash
   sudo systemctl restart php8.2-fpm
   ```
3. Update Nginx config:
   ```nginx
   client_max_body_size 64M;
   ```

### Uploaded Files Not Accessible

**Cause:** Storage symlink missing.

**Solution:**
```bash
php artisan storage:link
```

---

## Email Issues

### Emails Not Sending

**Cause:** Mail configuration incorrect.

**Solution:**
1. Verify mail settings in `.env`
2. Test mail configuration:
   ```bash
   php artisan tinker
   Mail::raw('Test email', function ($message) {
       $message->to('test@example.com')->subject('Test');
   });
   ```
3. Check mail logs

### "Connection could not be established with host"

**Cause:** SMTP server unreachable.

**Solution:**
1. Verify SMTP host and port
2. Check firewall rules
3. Verify SSL/TLS settings
4. Test connectivity:
   ```bash
   telnet smtp.example.com 587
   ```

---

## Performance Issues

### Slow Page Load

**Cause:** Uncached queries, missing indexes, or large datasets.

**Solution:**
1. Enable query logging to identify slow queries
2. Add database indexes
3. Enable caching:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. Use Redis for sessions and cache

### High Memory Usage

**Cause:** Large datasets loaded without pagination.

**Solution:**
1. Use cursor-based pagination
2. Eager load relationships
3. Select only needed columns
4. Use chunking for large operations:
   ```php
   Customer::chunk(100, function ($customers) {
       foreach ($customers as $customer) {
           // Process
       }
   });
   ```

---

## Production Issues

### 500 Internal Server Error

**Cause:** Application error with debug mode off.

**Solution:**
1. Check application logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```
2. Temporarily enable debug mode:
   ```ini
   APP_DEBUG=true
   ```
3. Identify and fix the error
4. Disable debug mode:
   ```ini
   APP_DEBUG=false
   ```

### "Whoops, looks like something went wrong"

**Cause:** Debug mode enabled in production.

**Solution:**
1. Set `APP_DEBUG=false` in `.env`
2. Check error in `storage/logs/laravel.log`

### Application Unresponsive

**Cause:** PHP-FPM or queue workers crashed.

**Solution:**
```bash
# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Restart queue workers
sudo supervisorctl restart VentureX-ERP-worker:*

# Check disk space
df -h

# Check memory
free -m
```

### Maintenance Mode Stuck

**Cause:** `.env` or config cache has maintenance flag.

**Solution:**
```bash
php artisan up
php artisan config:cache
```

---

## Redis Issues

### "Connection refused" to Redis

**Cause:** Redis not running.

**Solution:**
```bash
sudo systemctl start redis
sudo systemctl status redis
```

### Redis Memory Issues

**Cause:** Redis running out of memory.

**Solution:**
1. Check Redis memory:
   ```bash
   redis-cli info memory
   ```
2. Increase memory limit in `redis.conf`:
   ```
   maxmemory 512mb
   ```
3. Clear Redis:
   ```bash
   redis-cli FLUSHALL
   ```

---

## Getting Additional Help

1. Check application logs: `storage/logs/laravel.log`
2. Enable debug mode temporarily for detailed errors
3. Search existing issues on GitHub
4. Contact support: support@venturexerp.com
5. Include error messages, logs, and environment details when reporting issues
