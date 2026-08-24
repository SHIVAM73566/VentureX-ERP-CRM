CODESTER FIX & RESUBMISSION GUIDE
==================================================

REJECTION REASON: "Not ready for sale. Please include a demo."

Codester REQUIRES a working demo before approving software items.
Here is exactly how to fix it and get approved.

=====================================================
STEP 1 — PUT A LIVE DEMO ONLINE (pick ONE option)
=====================================================

OPTION A — ORACLE CLOUD FREE FOREVER VPS (recommended, $0)
1. Sign up: https://www.oracle.com/cloud/free/ (needs card for verification only, never charged)
2. Create instance: Ubuntu 22.04, Always Free eligible shape (A1 Flex, 2 CPU / 4GB RAM is free forever)
3. Open ports 80/443 in the security list + instance firewall
4. SSH in and run these commands:
     sudo apt update && sudo apt install -y curl git unzip
     curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
     sudo apt install -y php8.3 php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-curl php8.3-gd php8.3-mysql mysql-server composer nginx nodejs
     cd /var/www && git clone https://github.com/SHIVAM73566/VentureX-ERP-CRM.git venturex
     cd venturex && composer install --no-dev --optimize-autoloader
     npm install && npm run build
     cp .env.example .env && php artisan key:generate
     sudo mysql -e "CREATE DATABASE venturex_demo; CREATE USER 'vx'@'localhost' IDENTIFIED BY 'DemoPass2026!'; GRANT ALL ON venturex_demo.* TO 'vx'@'localhost'; FLUSH PRIVILEGES;"
     # edit .env DB_DATABASE=venturex_demo DB_USERNAME=vx DB_PASSWORD=DemoPass2026!
     php artisan migrate --seed
     php artisan storage:link
     sudo chown -R www-data:www-data storage bootstrap/cache
5. Point Nginx root to /var/www/venturex/public -> restart nginx
6. Your live demo is now at your server IP

OPTION B — CHEAP FAST ($4-6/mo)
Hostinger/DigitalOcean/Hetzner VPS -> same commands as Option A step 4.

OPTION C — SHARED HOSTING (cPanel)
Upload ZIP to public_html -> extract -> visit /install wizard -> done.
(Works if hosting has PHP 8.3; set document root to /public)

=====================================================
STEP 2 — PROTECT THE DEMO (important!)
=====================================================
After seeding, change demo passwords so buyers see but cannot break:
   Login as demo_admin -> Users -> reset all three demo user passwords
   to the same values below, then use THESE in the listing:

   Demo page URL:      http://YOUR-SERVER-IP  (or domain)
   Super Admin:        demo_admin@example.com  / VentureX_Demo_2026!
   CEO:                demo_manager@example.com / VentureX_Demo_2026!
   Sales Manager:      demo_sales@example.com  / VentureX_Demo_2026!

Also add a banner note in listing: "Demo resets nightly. Data you add may be cleared."

=====================================================
STEP 3 — UPDATE THE LISTING (paste-ready additions)
=====================================================

Add this block at the VERY TOP of the Codester description:

--------------------------------------------------
LIVE DEMO
Frontend/Admin Panel: http://YOUR-DEMO-URL
Login:    demo_admin@example.com
Password: VentureX_Demo_2026!

VIDEO WALKTHROUGH
[upload the install/demo video to YouTube and paste link here]
--------------------------------------------------

And in the "What's included" section add this line first:

$89.99 includes the COMPLETE FULL SOURCE CODE - no encryption,
no obfuscation. Web installer creates your own admin account;
demo credentials above are seeded sample accounts.

=====================================================
STEP 4 — RESUBMIT MESSAGE TO CODester REVIEWER
=====================================================

Hi,

Thank you for reviewing. The item now includes everything requested:

1. LIVE DEMO added at top of description with login credentials
2. Video walkthrough embedded
3. Clarified that full unencrypted source code is included
4. Installation instructions + requirements clearly stated

The application installs via web installer wizard in under 5 minutes
and ships with 339 passing automated tests.

Please re-review the item. Happy to adjust anything else needed.

Best regards,
Shivam
support@venturexerp.com

=====================================================
STEP 5 — RESUBMIT CHECKLIST (tick before submitting)
=====================================================
[ ] Demo URL loads in browser (test from phone too)
[ ] Demo login works with listed credentials
[ ] Demo shows dashboard with data (seeders ran - not empty!)
[ ] YouTube video uploaded & linked
[ ] "LIVE DEMO" block is FIRST thing in description
[ ] Screenshots still attached on item page
[ ] Price $89.99 unchanged
[ ] Resubmit message sent with the item update
