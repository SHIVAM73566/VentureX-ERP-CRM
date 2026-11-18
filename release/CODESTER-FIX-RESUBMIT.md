CODESTER LOCAL DEMO & RESUBMISSION GUIDE
==================================================

Use this guide to give reviewers a reproducible **local** preview and to resubmit the v1.3.1 listing. No public demo server, private LAN address or shared credential is required.

=====================================================
STEP 1 — PREPARE A LOCAL PREVIEW
=====================================================

Requirements: Ubuntu 22.04/24.04, PHP 8.4, MySQL 8.0+, Composer 2 and Node.js 20+.

```bash
sudo apt update
sudo apt install -y curl git unzip composer mysql-server nginx
sudo apt install -y php8.4 php8.4-mbstring php8.4-xml php8.4-bcmath php8.4-curl php8.4-gd php8.4-mysql
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

git clone https://github.com/SHIVAM73566/VentureX-ERP-CRM.git venturex
cd venturex
composer install --no-dev --optimize-autoloader
npm install && npm run build
cp .env.example .env
php artisan key:generate

sudo mysql -e "CREATE DATABASE venturex_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER 'vx'@'localhost' IDENTIFIED BY '<your-strong-unique-password>'; GRANT ALL ON venturex_local.* TO 'vx'@'localhost'; FLUSH PRIVILEGES;"
# Edit .env: DB_DATABASE=venturex_local, DB_USERNAME=vx,
# DB_PASSWORD=<your-strong-unique-password>, APP_URL=http://localhost:8000

php artisan migrate --force
php artisan storage:link
sudo chown -R www-data:www-data storage bootstrap/cache
php artisan serve --host=127.0.0.1 --port=8000
```

Open <http://127.0.0.1:8000/install>. For a reviewer preview, choose **Demo Preview Mode**. For a clean install or production evaluation, choose **Clean Production Mode** and create your own administrator.

> Never run `migrate --seed` in production. The one-time `/install` wizard is the supported setup path.

=====================================================
STEP 2 — LOCAL DEMO ONLY CREDENTIALS
=====================================================

These accounts exist only when **Demo Preview Mode** or the demo credential seeder is deliberately enabled on localhost/127.0.0.1. They are **LOCAL DEMO ONLY** and must never be used on a public, shared or production server.

| Role | Email | Password |
|---|---|---|
| Super Admin | `demo_admin@example.com` | `Demo_Admin_2026!` |
| CEO | `demo_manager@example.com` | `Demo_Manager_2026!` |
| Sales Manager | `demo_sales@example.com` | `Demo_Sales_2026!` |

Remove them or reinstall with Clean Production Mode before exposing the application.

=====================================================
STEP 3 — PASTE-READY LISTING BLOCK
=====================================================

Add this block at the top of the Codester description:

--------------------------------------------------
LOCAL DEMO PREVIEW
Run locally at <http://127.0.0.1:8000> using Demo Preview Mode. No public demo URL or shared credential is provided.

LOCAL DEMO ONLY — do not use in production:
`demo_admin@example.com` / `Demo_Admin_2026!`

VIDEO WALKTHROUGH
[upload the install/demo video to YouTube and paste link here]
--------------------------------------------------

And in the "What's included" section add this line first:

$89.99 includes the COMPLETE FULL SOURCE CODE — no encryption, no obfuscation. The web installer creates your own administrator. Demo Preview Mode is optional and is for LOCAL DEMO ONLY. Source code is provided under the MIT License.

=====================================================
STEP 4 — RESUBMIT MESSAGE TO Codester REVIEWER
=====================================================

Hi,

Thank you for reviewing. The item now includes everything requested:

1. A reproducible local demo preview with LOCAL DEMO ONLY credentials
2. Video walkthrough embedded
3. Clarified that full unencrypted source code is included under the MIT License
4. Installation instructions and requirements clearly stated

The application installs via the one-time web installer in under five minutes. Automated tests are passing; final v1.3.1 counts will be published in `SECURITY-AUDIT-REPORT.md` after central integration. Marketplace approval remains pending.

Please re-review the item. Happy to adjust anything else needed.

Best regards,
Shivam Chaturvedi
chaturvedishivam179@gmail.com

=====================================================
STEP 5 — RESUBMIT CHECKLIST
=====================================================

[ ] Local preview loads at `http://127.0.0.1:8000`
[ ] Demo Preview Mode creates only LOCAL DEMO ONLY sign-ins
[ ] Clean Production Mode creates no demo users or sample business records
[ ] No `migrate --seed` command appears in production instructions
[ ] No private LAN IP, public demo URL or shared password is published
[ ] YouTube video uploaded & linked
[ ] Screenshots still attached on the item page
[ ] Price $89.99 unchanged
[ ] MIT License wording matches the root `LICENSE`
[ ] Resubmit message sent with the item update
