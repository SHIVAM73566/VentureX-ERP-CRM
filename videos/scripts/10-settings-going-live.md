# Video 10: Settings, Users & Going Live
**Duration:** 5–6 minutes

## Pre-Recording Checklist
- [ ] Screen recording ready
- [ ] Resolution: 1920x1080
- [ ] VentureX running at http://localhost:8000
- [ ] Logged in as demo_admin@example.com
- [ ] Clean database with demo data loaded
- [ ] Subtitles file: ../subtitles/10-settings-going-live.srt

---

## Script

### [0:00 – 0:20] INTRO

**On Screen:** Title card — "Video 10: Settings, Users & Going Live"

**Voiceover:** "Welcome to the final video in the VentureX tutorial series. Today we walk through the Settings module, user management, security features, and everything you need to know before deploying to production."

**Action:** Fade to the VentureX dashboard.

---

### [0:20 – 0:55] SETTINGS MODULE WALKTHROUGH

**On Screen:** Navigate to Settings (`/settings`). Show the main settings page with all available sections.

**Voiceover:** "The Settings module is your central configuration hub. On the left sidebar you'll see sections for General, Users, Security, Audit Logs, Support, Error Center, and Control Center. General settings cover your company name, logo, timezone, currency, and default language. Changes here apply globally across the system."

**Action:** Click through each section on the sidebar briefly. In General, update the company name to demonstrate saving a setting. Show the success toast notification.

---

### [0:55 – 1:35] USER MANAGEMENT

**On Screen:** Navigate to Users section (`/settings/users`). Show the user list table and the Add User form.

**Voiceover:** "User management lets you add, edit, and deactivate team members. Click Add User to create a new account — fill in name, email, assign a role, and set an initial password. Each user receives an email notification with their login credentials."

**Action:** Click Add User. Fill in a sample user: "Sarah Chen", sarah.chen@example.com, role: Sales Manager. Save and show the new user appearing in the list. Then click Edit on the new user to show the edit form.

**Voiceover:** "Roles determine what each user can access. Super Admin has unrestricted access to everything. CEO sees all dashboards and reports. Sales Manager can manage deals and contacts but not system settings. Inventory Manager handles stock and purchase orders. Each role is carefully scoped to enforce least-privilege access."

**Action:** Open the role dropdown and hover over each option. Show the permissions summary panel that updates as you change the role.

---

### [1:35 – 2:00] ROLE-BASED ACCESS CONTROL

**On Screen:** Show the roles and permissions matrix in Settings, then switch to a different user's view.

**Voiceover:** "VentureX ships with predefined roles — super_admin, ceo, sales_manager, inventory_manager, hr_manager, accountant, and support_agent. Each role maps to a specific set of permissions. If you need a custom role, you can define one from the Roles panel and assign granular permissions."

**Action:** Show the Roles panel. Click on Sales Manager to expand its permissions list. Then log out, log in as the new Sarah Chen user, and show the restricted navigation menu.

---

### [2:00 – 2:35] SECURITY CENTER

**On Screen:** Navigate to Security section (`/settings/security`).

**Voiceover:** "The Security Center gives you control over password policies, session management, and trusted devices. You can enforce minimum password length, require special characters, and set password expiration periods. Session settings let you define idle timeout and maximum session duration."

**Action:** Show the password policy form. Change minimum length from 8 to 12. Show the session timeout dropdown set to 30 minutes. Scroll down to the Trusted Devices section.

**Voiceover:** "Trusted Devices lists every device that has logged into the system. You can see the device type, browser, last active time, and IP address. If you spot an unfamiliar device, click Revoke to force a re-login on that device."

**Action:** Show the trusted devices table. Hover over a device entry. Click Revoke on one device to demonstrate.

---

### [2:35 – 3:00] AUDIT LOGS

**On Screen:** Navigate to Audit Logs (`/settings/audit-logs`). Show the filterable log table.

**Voiceover:** "Every significant action in VentureX is recorded in the Audit Logs. This includes login attempts, data modifications, setting changes, and AI interactions. Use the filters to search by user, date range, or action type. This is invaluable for compliance and troubleshooting."

**Action:** Show the filter bar. Filter by user "demo_admin" and date range "Last 7 Days". Scroll through the results showing different action types. Click on one entry to expand the detail view showing the before and after values.

---

### [3:00 – 3:20] SUPPORT TICKETS

**On Screen:** Navigate to Support Tickets (`/settings/support`). Show the ticket list and a sample ticket.

**Voiceover:** "The built-in Support Tickets system lets your team report issues without leaving the application. Users can submit tickets with a category, priority, and description. Admins can assign, track, and resolve tickets directly from this panel."

**Action:** Click Create Ticket. Fill in a sample: Category "Bug Report", Priority "High", Subject "Export not working for large datasets". Save and show it in the list.

---

### [3:20 – 3:40] ERROR CENTER

**On Screen:** Navigate to Error Center (`/settings/errors`). Show the error log table.

**Voiceover:** "The Error Center aggregates application errors in real time. Each entry shows the error message, the file and line number, the user who triggered it, and the full stack trace. You can mark errors as resolved, set them as ignored, or export them for external tracking."

**Action:** Show the error list. Click on one error to expand the stack trace. Mark it as resolved. Show the resolution count updating.

---

### [3:40 – 4:05] CONTROL CENTER

**On Screen:** Navigate to Control Center (`/settings/control-center`). Show customer management, announcements, and updates panels.

**Voiceover:** "The Control Center is your administrative command panel. Customer Management lets you view and manage all registered companies and their subscription status. The Announcements panel lets you push system-wide messages to all users — useful for maintenance notices or feature announcements. The Updates panel tracks system version history and pending updates."

**Action:** Show each sub-panel. Create a sample announcement: "System maintenance scheduled for Saturday 2 AM — 4 AM UTC." Save and show it appearing in the announcements list.

---

### [4:05 – 5:05] PRODUCTION DEPLOYMENT CHECKLIST

**On Screen:** Show a terminal or deployment configuration. Present each item as a checklist.

**Voiceover:** "Before going live, run through this deployment checklist. First, set APP_DEBUG to false in your .env file — this is critical for security. Never expose debug information in production."

**Action:** Open `.env`. Change `APP_DEBUG=true` to `APP_DEBUG=false`.

**Voiceover:** "Second, configure your real mail server. Replace the Mailtrap settings with your actual SMTP credentials — host, port, username, password, and encryption."

**Action:** Show the mail configuration section in `.env`.

**Voiceover:** "Third, set up SSL or HTTPS. Use Let's Encrypt for free certificates, or your hosting provider's built-in SSL. VentureX should never run over plain HTTP in production."

**Action:** Show a brief nginx configuration snippet with SSL directives.

**Voiceover:** "Fourth, configure queue workers. VentureX uses Laravel queues for background jobs like email sending and AI processing. Run `php artisan queue:work` as a background service, or use Supervisor to manage the process."

**Action:** Show the queue configuration in `.env` and a sample Supervisor config.

**Voiceover:** "Fifth, set up automated backups. Use mysqldump with a cron job to back up your database daily. Store backups offsite. A simple cron entry looks like this."

**Action:** Show a cron entry: `0 2 * * * /usr/bin/mysqldump -u user -p'password' venturex_db | gzip > /backups/venturex_$(date +\%F).sql.gz`

**Voiceover:** "Sixth, verify file permissions. The storage and bootstrap/cache directories must be writable by the web server. Run `chmod -R 775 storage bootstrap/cache` and `chown -R www-data:www-data storage bootstrap/cache`."

**Action:** Show these commands in the terminal.

**Voiceover:** "Finally, if you need PayPal payments, Firebase push notifications, Google OAuth, or Microsoft OAuth — each has its own configuration section in .env. Fill in the credentials you obtained from each provider's developer console."

**Action:** Show the relevant `.env` sections for PayPal, Firebase, Google, and Microsoft. Briefly highlight each block.

---

### [5:05 – 5:30] EXPORT & IMPORT DATA

**On Screen:** Navigate to Settings → General. Show the Export and Import options.

**Voiceover:** "VentureX supports exporting your data as CSV or JSON for external analysis or migration. Click Export, select the module — customers, deals, products, or the entire database — and download the file. To import data, upload a CSV matching the module's expected format and the system validates and imports it row by row, reporting any errors."

**Action:** Click Export, select "Customers", show the download. Then click Import, upload a sample CSV, and show the import results summary.

---

### [5:30 – 5:50] WHERE TO GET HELP

**On Screen:** Show the documentation links and the Support Tickets panel.

**Voiceover:** "If you need help, start with the built-in documentation — accessible from the Help menu in the top navigation bar. For specific issues, submit a Support Ticket from Settings. For community help, visit the GitHub repository and open an issue. The documentation covers every module in detail with step-by-step guides."

**Action:** Open the Help menu. Show the documentation page. Then navigate to Support Tickets and point to the "Create Ticket" button.

---

### [5:50 – 6:00] OUTRO

**On Screen:** Title card — "VentureX ERP & CRM — Tutorial Series Complete"

**Voiceover:** "That wraps up the VentureX tutorial series. You've seen every major module — from CRM and sales to inventory, HR, accounting, AI features, and now deployment. Thank you for watching, and enjoy building with VentureX."

**Action:** Fade to black with the VentureX logo.
