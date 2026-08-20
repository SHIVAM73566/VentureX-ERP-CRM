# Video 12: VPS & Cloud Deployment — Full Control, Full Power
## Duration: 22 minutes
## Target Audience: IT-savvy business owners, developers, sysadmins
## Difficulty: Intermediate

---

## SCENE-BY-SCENE BREAKDOWN

### Scene 1: Introduction (00:00 - 01:30)
**Screen:** Title card, then desktop with terminal open next to a cloud provider dashboard (DigitalOcean or Linode)
**Voiceover:** "Welcome back to the VentureX series! In the last video, we deployed on shared hosting with cPanel — simple, but sometimes you need more. More control, more performance, more flexibility. That's where a VPS or cloud server comes in. In this video, I'm going to set up a fresh Ubuntu server, install everything VentureX needs, deploy the application, and configure it for production. I'm using DigitalOcean here, but this works on AWS EC2, Linode, Vultr — any Ubuntu VPS. Let's spin up a server."
**Action:** Cursor moves between DigitalOcean dashboard and terminal window
**Caption:** Video 12 — VPS & Cloud Deployment

### Scene 2: Creating a Cloud Server (01:30 - 03:30)
**Screen:** DigitalOcean dashboard — Create Droplet page
**Voiceover:** "In your cloud provider's dashboard, create a new server. For the OS, pick Ubuntu 22.04 LTS or 24.04 LTS — both work great with VentureX. For plan size, the basic $12/month plan with 2 gigs of RAM and one CPU is enough to get started. You can always scale up later. Choose a data center region close to your users. Set your root password — make it strong, write it down. Give your server a hostname like venturex-prod. Click Create Droplet. Wait about 60 seconds for it to spin up. Once it's ready, copy the IP address. We're going to connect to it now."
**Action:** Click through DigitalOcean creation form, copy IP address
**Caption:** Ubuntu 22.04/24.04 LTS, $12/mo plan, copy IP address

### Scene 3: Initial Server Setup (03:30 - 06:00)
**Screen:** Terminal window
**Voiceover:** "Open your terminal — or use PowerShell on Windows, or Terminal on Mac. Type: ssh root@your_server_ip. Accept the fingerprint, enter your password. You're in! First thing — let's update the server. Run: apt update && apt upgrade -y. This pulls in all the latest security patches. Takes a minute. Next, let's create a non-root user for security. Run: adduser venturex. Set a password, fill in the details or just press Enter through them. Now give this user sudo privileges: usermod -aG sudo venturex. Then set up the firewall — we'll use UFW. Run: ufw allow OpenSSH, then ufw allow 'Nginx Full', then ufw enable. Say yes. Now switch to your new user: su - venturex. From here on, we're working as the venturex user, not root. Much safer."
**Action:** SSH in, run update, create user, configure UFW, switch user
**Caption:** Update → Create user → sudo → UFW → Switch user

### Scene 4: Installing PHP 8.3 (06:00 - 08:00)
**Screen:** Terminal
**Voiceover:** "Now let's install PHP. We need PHP 8.3 with a bunch of extensions. First, add the PHP repository: sudo add-apt-repository ppa:ondrej/php -y. Then update again: sudo apt update. Now install PHP and the extensions VentureX needs: sudo apt install php8.3 php8.3-fpm php8.3-mysql php8.3-curl php8.3-gd php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath php8.3-intl php8.3-readline php8.3-opcache -y. That's a lot of packages, but they're all important — curl for API calls, mbstring for text handling, opcache for performance. Verify it worked: php -v. You should see PHP 8.3.something. Nice."
**Action:** Run each command, show output, verify PHP version
**Caption:** PHP 8.3 + all required extensions

### Scene 5: Installing MySQL 8 (08:00 - 09:30)
**Screen:** Terminal
**Voiceover:** "Next up, MySQL. Run: sudo apt install mysql-server -y. Once it's installed, run the security script: sudo mysql_secure_installation. It'll ask you a few questions. Set the VALIDATE PASSWORD component — I choose MEDIUM for a good balance. Set a strong root password. Remove anonymous users? Yes. Disallow root login remotely? Yes. Remove the test database? Yes. Reload privilege tables? Yes. Now let's create the database and user for VentureX. Log into MySQL: sudo mysql. You're at the MySQL prompt now. Run these three commands: CREATE DATABASE venturex_prod; CREATE USER 'vxuser'@'localhost' IDENTIFIED BY 'your_strong_password'; GRANT ALL PRIVILEGES ON venturex_prod.* TO 'vxuser'@'localhost'; FLUSH PRIVILEGES; EXIT; Database is ready."
**Action:** Install MySQL, run secure installation, create database and user
**Caption:** MySQL 8 → Secure install → Create DB + user

### Scene 6: Installing and Configuring Nginx (09:30 - 12:00)
**Screen:** Terminal, then nano editor for Nginx config
**Voiceover:** "Now Nginx — this is our web server. Install it: sudo apt install nginx -y. Start it and enable it on boot: sudo systemctl start nginx && sudo systemctl enable nginx. Test it — open your browser and go to your server's IP. You should see the default Nginx welcome page. If you do, Nginx is working. Now let's configure it for VentureX. Create a new config file: sudo nano /etc/nginx/sites-available/venturex. Paste in this server block — I'll link the config in the description. The key parts are: server_name your_domain.com, root pointing to /home/venturex/public_html/public, and the location block handling PHP-FPM with the try_files directive for Laravel routing. Save the file with Ctrl+O, Ctrl+X. Enable it: sudo ln -s /etc/nginx/sites-available/venturex /etc/nginx/sites-enabled/. Remove the default: sudo rm /etc/nginx/sites-enabled/default. Test the config: sudo nginx -t. If it says syntax is ok and test is successful, reload Nginx: sudo systemctl reload nginx."
**Action:** Install Nginx, show welcome page, edit config, enable site, test and reload
**Caption:** Nginx config → server block → enable → test → reload

### Scene 7: Deploying the Application (12:00 - 14:30)
**Screen:** Terminal
**Voiceover:** "Time to get VentureX on the server. Two options here. Option one — Git. If your code is in a Git repository: cd /home/venturex, then git clone your-repo-url public_html. It clones everything into the public_html folder. Option two — SCP from your local machine. Open a new terminal on your computer: scp -r /path/to/venturex/* venturex@your_server_ip:/home/venturex/public_html. Enter your password, and it copies everything over. Once the files are there, set permissions: sudo chown -R venturex:venturex /home/venturex/public_html. Then: cd public_html. Copy the environment file: cp .env.example .env. Edit it: nano .env. Fill in your database details — DB_HOST is 127.0.0.1, DB_DATABASE is venturex_prod, DB_USERNAME is vxuser, DB_PASSWORD is your password. Set APP_URL to https://yourdomain.com. Save and exit. Generate the key: php artisan key:generate. Install dependencies: composer install --no-dev --optimize-autoloader. Set permissions again: chmod -R 755 storage bootstrap/cache. Create the storage link: php artisan storage:link."
**Action:** Clone repo or SCP files, set permissions, configure .env, install dependencies
**Caption:** Git clone or SCP → .env → composer install → permissions

### Scene 8: SSL with Certbot (14:30 - 16:00)
**Screen:** Terminal
**Voiceover:** "Free SSL with Certbot. First, make sure your domain's DNS is pointing to your server IP — A record pointing to the IP address. Once DNS has propagated, install Certbot: sudo apt install certbot python3-certbot-nginx -y. Run it: sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com. It'll ask for your email, agree to terms, and ask if you want to redirect HTTP to HTTPS — choose yes. Certbot automatically configures Nginx for you. Verify: sudo certbot certificates. You'll see your domain with a valid certificate. Auto-renewal is set up by default. Test it: sudo certbot renew --dry-run. If no errors, you're good. Your site is now live with HTTPS."
**Action:** Install Certbot, run it, show certificate, test renewal
**Caption:** Certbot → Free SSL → Auto-renewal

### Scene 9: Setting Up Queue Workers with Supervisor (16:00 - 18:30)
**Screen:** Terminal
**Voiceover:** "VentureX uses queues for background jobs — things like sending bulk emails, processing AI requests, and generating reports. We need Supervisor to keep those workers running. Install it: sudo apt install supervisor -y. Create a worker config: sudo nano /etc/supervisor/conf.d/venturex-worker.conf. Paste this in: [program:venturex-worker] command=php /home/venturex/public_html/artisan queue:work --sleep=3 --tries=3 --max-time=3600 user=venturex autostart=true autorestart=true stopasgroup=true killasgroup=true numprocs=1 redirect_stderr=true stdout_logfile=/home/venturex/worker.log. Save and exit. Reload Supervisor: sudo supervisorctl reread, then sudo supervisorctl update, then sudo supervisorctl start venturex-worker:*. Check status: sudo supervisorctl status. You should see your worker running. If it says FATAL, check the log file for errors."
**Action:** Install Supervisor, create config, start worker, verify status
**Caption:** Supervisor → Queue worker → Always running

### Scene 10: Performance Tuning (18:30 - 20:30)
**Screen:** Terminal, then phpinfo or config files
**Voiceover:** "Let's squeeze some extra performance out. First, PHP OPcache — check it's enabled: php -m | grep opcache. It should show up. Then edit PHP config: sudo nano /etc/php/8.3/fpm/php.ini. Find these settings and adjust them: opcache.enable=1, opcache.memory_consumption=128, opcache.max_accelerated_files=10000, opcache.revalidate_freq=2. Save. Restart PHP-FPM: sudo systemctl restart php8.3-fpm. Next, Nginx caching. Add these lines to your server block in the Nginx config: location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ { expires 30d; add_header Cache-Control "public, immutable"; }. This tells the browser to cache static files for 30 days. Reload Nginx. Finally, MySQL tuning: sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf. For a 2GB RAM server, set innodb_buffer_pool_size to 256M and query_cache_size to 32M. Restart MySQL: sudo systemctl restart mysql. These changes alone can make your site feel twice as fast."
**Action:** Edit PHP config, Nginx config, MySQL config, restart services
**Caption:** OPcache + Nginx cache + MySQL buffer = faster site

### Scene 11: Wrapping Up (20:30 - 22:00)
**Screen:** Browser showing the live VentureX site on the server, then terminal
**Voiceover:** "And that's it — VentureX is live on your own VPS! You've got full control, full performance, and you're not sharing resources with anyone. Open your browser, go to your domain, and log in. Everything should work exactly like it did locally, but now it's production-ready. A few tips going forward: set up automatic backups with a script and cron job, monitor your server with something like Netdata or UptimeRobot, and keep your system updated regularly with sudo apt update && sudo apt upgrade. In the next video, we'll cover WordPress integration — how to connect your WordPress site directly to VentureX. See you there!"
**Action:** Show live site, navigate around, show monitoring dashboard briefly
**Caption:** Live on VPS! → Backups + Monitoring + Updates

---

## SCREEN RECORDING CHECKLIST
- [ ] Record DigitalOcean droplet creation
- [ ] Record SSH connection and initial setup
- [ ] Record PHP 8.3 installation with all extensions
- [ ] Record MySQL installation and secure setup
- [ ] Record Nginx installation and config editing
- [ ] Record application deployment (Git clone or SCP)
- [ ] Record .env configuration and artisan commands
- [ ] Record Certbot SSL installation
- [ ] Record Supervisor setup and worker status
- [ ] Record performance tuning edits
- [ ] Record final site load and login

## THUMBNAIL DESIGN
- Main text: "VPS Deployment"
- Visual elements: Cloud server icon + terminal window + Ubuntu logo + speedometer icon
- Color scheme: navy (#0f172a) + teal (#14b8a6)
