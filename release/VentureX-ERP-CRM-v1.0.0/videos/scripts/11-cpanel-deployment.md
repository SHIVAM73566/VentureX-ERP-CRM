# Video 11: cPanel Deployment — Getting VentureX Live on Shared Hosting
## Duration: 18 minutes
## Target Audience: Small business owners, freelancers, non-technical users
## Difficulty: Beginner

---

## SCENE-BY-SCENE BREAKDOWN

### Scene 1: Introduction (00:00 - 01:00)
**Screen:** Title card with VentureX logo, then transition to desktop with cPanel login page open
**Voiceover:** "Hey everyone! Welcome back to the VentureX series. So you've got VentureX installed locally, you've explored the CRM, inventory, even the AI features — and now you're ready to take it live. In this video, I'm going to walk you through deploying VentureX on a shared hosting account using cPanel. No terminal commands, no server admin degree required. If you can upload a file, you can do this. Let's get started."
**Action:** Cursor moves to cPanel login, types credentials
**Caption:** Video 11 — cPanel Deployment

### Scene 2: What You Need Before We Start (01:00 - 02:30)
**Screen:** A simple slide or Notepad document listing prerequisites
**Voiceover:** "Before we jump in, here's what you'll need. First, a shared hosting account — any provider that gives you cPanel works. I'm using Hostinger here, but GoDaddy, Bluehost, SiteGround, they all look basically the same. Second, you need PHP 8.3 or higher enabled on your hosting. Most hosts have this now, but if yours is stuck on PHP 7, you'll need to upgrade in your hosting dashboard. Third, MySQL 5.7 or higher — again, most hosts include this by default. And finally, your VentureX files ready to download. You can grab the latest release from the GitHub repository or the official download link. Got all four? Great. Let's open cPanel."
**Action:** Cursor highlights each item in the list as it's mentioned
**Caption:** Prerequisites: cPanel hosting, PHP 8.3+, MySQL 5.7+, VentureX files

### Scene 3: Uploading Files via File Manager (02:30 - 05:30)
**Screen:** cPanel dashboard, then File Manager
**Voiceover:** "Alright, here's your cPanel dashboard. Look for the File Manager icon — it's usually under the Files section. Click that. Now you'll see your hosting file structure. Navigate to public_html — that's your website's root folder. If there's an index.php or default.html in there from your host, you can delete it. Now click Upload at the top. You'll see a file upload page. Drag your VentureX zip file right into the upload area, or click Select File. Wait for it to finish — the bar should turn green at 100%. Head back to File Manager, find the zip file, right-click it, and choose Extract. Extract it right into public_html. If your zip has a folder inside like 'venturex-main', you'll want to move everything from that folder up to public_html directly. I'll show you — click into the folder, Select All, then Move, and type public_html as the destination. Now your files are in the right place."
**Action:** Step-by-step navigation through cPanel File Manager, uploading zip, extracting, moving files
**Caption:** Upload → Extract → Move to public_html

### Scene 4: Creating the MySQL Database (05:30 - 08:00)
**Screen:** cPanel dashboard → MySQL Database Wizard
**Voiceover:** "File uploaded. Next, we need a database. Back in cPanel, find MySQL Database Wizard — it walks you through the whole process. Step one: name your database. I'll call mine venturex_prod. Click Next Step. Step two: create a database user. I'll use vxuser. Generate a strong password — click the Password Generator button, copy that password somewhere safe, you'll need it. Check the box to confirm you saved it, then Create User. Step three: add the user to the database. Check ALL PRIVILEGES — that gives your app full access to its own database. Click Next Step. Done! Write down your database name, username, and password. You'll need all three in a minute."
**Action:** Click through MySQL Database Wizard steps, copy generated password
**Caption:** Database Name, Username, Password — save all three!

### Scene 5: Running Composer Install (08:00 - 10:00)
**Screen:** cPanel terminal or SSH terminal
**Voiceover:** "Now here's where some people get stuck. VentureX uses Composer to install its PHP dependencies. If your host has cPanel Terminal — that's the black icon that looks like a command line — you can use that. Open it. You'll need to be in your project directory, so type: cd public_html. Then run: composer install. If your host doesn't have cPanel Terminal, or if Composer isn't available, here's the workaround. Some hosts let you run Composer from the PHP version selector area, or you can check if there's a 'Composer' app in cPanel under Software. If none of that works, don't panic. You can run composer install on your local machine, upload the entire vendor folder via File Manager, and it works exactly the same way. The vendor folder contains all the pre-installed dependencies — just zip it up locally after running composer install, upload the zip, extract it in public_html, and you're good."
**Action:** Type commands in cPanel Terminal, show error-free output
**Caption:** composer install — or upload vendor folder manually

### Scene 6: Configuring the .env File (10:00 - 12:30)
**Screen:** File Manager → .env file opened in code editor
**Voiceover:** "Time to configure your environment file. In File Manager, find the .env file in your project root. Right-click, Edit. If you don't see a .env file, look for .env.example — rename it to .env. Now let's fill in the values. First, APP_URL — set this to your domain, like https://yourdomain.com. APP_ENV should be 'production'. APP_KEY — you can leave this blank for now, we'll generate it. DB_CONNECTION is mysql. DB_HOST is usually localhost on shared hosting. DB_PORT is 3306. DB_DATABASE — that's the database name you just created. DB_USERNAME — that's the user you created. DB_PASSWORD — paste in that strong password you saved. Scroll down to MAIL settings if you want email notifications to work — set MAIL_HOST, MAIL_PORT, MAIL_USERNAME, and MAIL_PASSWORD with your email provider's SMTP details. Save the file."
**Action:** Edit each field in .env with sample values, highlight important lines
**Caption:** .env — database credentials + app URL + mail settings

### Scene 7: Generating the APP_KEY (12:30 - 13:30)
**Screen:** cPanel Terminal
**Voiceover:** "Almost there. We need to generate the application encryption key. Back in cPanel Terminal, in your public_html directory, run: php artisan key:generate. You should see a green success message. If you can't access Terminal, here's the trick — you can generate a key locally using the same command, then copy the 32-character string it gives you and paste it into the APP_KEY field in your .env file. Just make sure to add base64: before the key in the .env file. Save and close."
**Action:** Run key:generate command, show success message
**Caption:** php artisan key:generate

### Scene 8: Setting Up Cron Jobs (13:30 - 15:00)
**Screen:** cPanel → Cron Jobs
**Voiceover:** "VentureX needs a couple of scheduled tasks to work properly — things like sending email reminders, cleaning up old data, and running AI queue jobs. In cPanel, find Cron Jobs. You'll see a section called Add New Cron Job. Set the timing to Every Minute — that's the asterisk notation: * * * * *. Then in the command field, type: cd /home/yourusername/public_html && php artisan schedule:run >> /dev/null 2>&1. Replace yourusername with your actual cPanel username. Click Add New Cron Job. That's it — your scheduler is running. Some hosts have a minimum of 1 minute, some only allow 5 minutes. If 1 minute isn't available, 5 minutes works fine for most use cases."
**Action:** Fill in cron job form, show the command, click Add
**Caption:** Cron: * * * * * php artisan schedule:run

### Scene 9: SSL Certificate Setup (15:00 - 16:30)
**Screen:** cPanel → SSL/TLS or Let's Encrypt
**Voiceover:** "Let's secure your site with HTTPS. Most cPanel hosts include free SSL through Let's Encrypt. Find the SSL/TLS section, or look for 'Let's Encrypt' or 'SSL Certificates' in your dashboard. Select your domain, choose the Let's Encrypt option, and click Issue or Install. It takes about 30 seconds. Once it's installed, go back to your .env file and make sure APP_URL starts with https://, not http://. If you don't see the SSL option in cPanel, contact your host — some require you to enable it from the hosting dashboard first. After SSL is active, try visiting your site. You should see the padlock icon in the browser."
**Action:** Navigate to SSL section, install certificate, update .env
**Caption:** Install SSL → Update APP_URL to https://

### Scene 10: First Visit and Common Issues (16:30 - 18:00)
**Screen:** Browser navigating to the live VentureX URL
**Voiceover:** "Moment of truth — open your browser and go to your domain. You should see the VentureX login page! Log in with the default credentials or the ones you set during local installation. If you see a blank page, check that all files uploaded correctly and that your .env database details are right. If you get a 500 error, it's usually a permissions issue — in File Manager, set storage and bootstrap/cache folders to 755 permissions. If you see 'Whoops, something went wrong,' double-check your APP_KEY — it might be missing the base64: prefix. And that's it! You've just deployed VentureX on shared hosting. In the next video, we'll cover VPS and cloud deployment for those who need more power. See you there!"
**Action:** Load the site, log in, navigate around briefly
**Caption:** Live! Common fixes: permissions (755), APP_KEY prefix, .env details

---

## SCREEN RECORDING CHECKLIST
- [ ] Record cPanel login and dashboard overview
- [ ] Record File Manager upload, extract, and move process
- [ ] Record MySQL Database Wizard all 4 steps
- [ ] Record cPanel Terminal composer install
- [ ] Record .env file editing in File Manager code editor
- [ ] Record php artisan key:generate command
- [ ] Record cron job setup in cPanel
- [ ] Record SSL certificate installation
- [ ] Record first browser visit and login
- [ ] Record error scenarios and fixes (blank page, 500 error)

## THUMBNAIL DESIGN
- Main text: "cPanel Deployment"
- Visual elements: cPanel logo + VentureX logo + green checkmark + hosting server icon
- Color scheme: navy (#0f172a) + teal (#14b8a6)
