# Video 5: Installation & Dependencies
**Duration:** 6 minutes
**Difficulty:** Beginner

## Pre-Recording Checklist
- [ ] Screen recording ready at 1920x1080
- [ ] Terminal open in project root directory
- [ ] `.env` file already configured (from Video 4)
- [ ] PHP 8.3, Composer, Node.js 20+, npm all installed
- [ ] MySQL 8 running and `VENTUREX_ERP` database created
- [ ] Voiceover mic tested

---

## Script

### [0:00 - 0:15] INTRO
**On Screen:** Title card — "Video 5: Installation & Dependencies"
**Voiceover:** "Welcome to video five. Now that our environment is configured, it's time to install all dependencies and get VentureX running. We'll walk through Composer, npm, Vite, database migrations, and launching the dev server. Let's go."
**Action:** Fade in title card, hold 3 seconds, fade to terminal.

---

### [0:15 - 0:55] COMPOSER INSTALL
**On Screen:** Terminal at project root
**Voiceover:** "We start with Composer. Run `composer install`. This reads your `composer.lock` file and downloads every PHP package the project depends on — Laravel framework, Livewire components, Tailwind CSS build tools, testing libraries, and more. On a fresh install, this typically takes thirty to ninety seconds depending on your internet connection. You'll see a progress bar as packages download, then an autoload generation step at the end. If you see a memory limit error, increase PHP's `memory_limit` in your `php.ini` file to at least 512 megabytes."
**Action:** Type and execute: `composer install`
**On Screen:** Show the full output — package downloads, progress bar, autoload generation, and success. Scroll up briefly to show the list of installed packages. If memory error occurs, cut to editing `php.ini`, setting `memory_limit = 512M`, then re-running.

---

### [0:55 - 1:30] NPM INSTALL
**On Screen:** Terminal
**Voiceover:** "Next, install frontend dependencies with `npm install`. This downloads Node packages — Tailwind CSS 4, Livewire, Alpine.js, Vite build system, and related plugins — into the `node_modules` folder. First-time installs take a bit longer as npm resolves the dependency tree. You'll see a summary of packages added and any peer dependency warnings. Warnings are typically safe to ignore unless they indicate a breaking conflict."
**Action:** Type and execute: `npm install`
**On Screen:** Show npm install progress, package count summary, and any warnings. Pause on final output.

---

### [1:30 - 2:10] NPM RUN BUILD
**On Screen:** Terminal
**Voiceover:** "Now compile the frontend assets. Run `npm run build`. This triggers Vite — our JavaScript build tool. Vite reads `vite.config.js`, processes your Tailwind CSS files, compiles JavaScript modules, and outputs optimized bundles to the `public/build` directory. For VentureX, expect roughly 95 kilobytes of CSS and 47 kilobytes of JavaScript after minification. The build takes about ten to twenty seconds. Watch for the output summary — it shows each chunk, its size, and any warnings about large bundles."
**Action:** Type and execute: `npm run build`
**On Screen:** Show Vite build output — CSS compilation, JS bundling, chunk sizes, and build time.

---

### [2:10 - 2:25] VERIFY BUILD OUTPUT
**On Screen:** File explorer showing `public/build/manifest.json`
**Voiceover:** "Verify the build by checking `public/build/manifest.json`. This file maps source files to their compiled versions. You should see entries for the main CSS and JS bundles. The `public/build/assets` folder contains the actual compiled files with content-hashed filenames for cache busting."
**Action:** Open `public/build/manifest.json` in editor. Briefly show `public/build/assets` folder contents.

---

### [2:25 - 3:20] DATABASE MIGRATION AND SEEDING
**On Screen:** Terminal
**Voiceover:** "Now create the database tables and populate demo data. Run `php artisan migrate --seed`. This executes every migration file in `database/migrations` in chronological order. You'll see each table being created — `users`, `contacts`, `companies`, `deals`, `tasks`, `invoices`, `products`, `ai_insights`, `error_logs`, and dozens more. VentureX has over fifty migration files covering the full ERP and CRM schema. After all tables are created, the seeder runs. It inserts demo companies, contacts, deals with various stages, sample invoices, product catalogs, AI-generated insights, and two admin user accounts. Watch for the green check marks — each confirms a migration ran successfully. If you see a table already exists error, your database may have old tables. You can run `php artisan migrate:fresh --seed` to drop everything and start over."
**Action:** Type and execute: `php artisan migrate --seed`
**On Screen:** Show full migration output — table names scrolling, seeder output, final summary with counts. If error occurs, demonstrate `migrate:fresh --seed` fix.

---

### [3:20 - 3:40] VERIFY DATABASE
**On Screen:** MySQL client (Workbench, phpMyAdmin, or terminal)
**Voiceover:** "Let's verify the database. Open your MySQL client and connect to the `VENTUREX_ERP` database. Run `SHOW TABLES` and you should see all fifty-plus tables. Check the `users` table — you'll find the demo admin account with email `demo_admin@example.com`. The `deals` table has sample deals in various stages from prospecting to closed-won. This confirms both migrations and seeders ran correctly."
**Action:** Run `SHOW TABLES;` in MySQL client. Query `SELECT id, name, email FROM users;` to show demo accounts.

---

### [3:40 - 4:15] PHP ARTISAN SERVE
**On Screen:** Terminal
**Voiceover:** "Start the development server. Run `php artisan serve`. Laravel's built-in server starts on port 8000 by default. You'll see the message `Server running on http://127.0.0.1:8000`. Keep this terminal window open — the server runs as long as this process is active. To use a different port, add `--port=8080`. To allow access from other devices on your network, add `--host=0.0.0.0`."
**Action:** Type and execute: `php artisan serve`
**On Screen:** Show server startup message. Briefly show `php artisan serve --port=8080` as an alternative.

---

### [4:15 - 4:45] FIRST VISIT TO APPLICATION
**On Screen:** Browser navigating to `http://localhost:8000`
**Voiceover:** "Open your browser and navigate to `http://localhost:8000`. You should see the VentureX login page loading. The initial load takes a moment as Vite development assets compile on first request — this is normal. Once loaded, you'll see the login form with the VentureX branding, email and password fields, and the login button. The interface uses Tailwind CSS 4 with our custom design system. You'll notice the skeleton loading states — those shimmer placeholders that appear while content loads. These provide instant visual feedback and make the app feel faster."
**Action:** Navigate to `http://localhost:8000`. Wait for initial load. Show the login page fully rendered. Briefly reload to show skeleton loading states.

---

### [4:45 - 5:15] COMMON ERRORS AND FIXES
**On Screen:** Terminal showing error messages
**Voiceover:** "Let's cover the most common installation errors. First, `composer install` fails with a memory error — fix this by setting `memory_limit = 512M` in your `php.ini` file. Second, `npm install` fails with a Node version error — VentureX requires Node 20 or later. Check your version with `node -v` and update if needed using nvm. Third, port 8000 is already in use — either stop the other process or use `php artisan serve --port=8080`. Fourth, database connection refused — make sure MySQL is running and your `.env` credentials match your MySQL setup. Fifth, migration fails with a table already exists error — run `php artisan migrate:fresh --seed` to reset."
**Action:** Show each error message briefly and the corresponding fix command.

---

### [5:15 - 5:40] UI COMPONENTS PREVIEW
**On Screen:** Browser with VentureX login page loaded
**Voiceover:** "Before we end, let's appreciate the UI foundation. VentureX uses a component-based interface built with Livewire and Tailwind CSS 4. The login page itself uses our card component, form input components with validation states, and a primary button component with loading animations. Every module in the application follows this same component library — tables, modals, dropdowns, toast notifications, and stat cards. In the next video, we'll log in for the first time and take a full tour of the dashboard."
**Action:** Hover over form fields, show focus states. Point to branding, form layout, button hover effect.

---

### [5:40 - 6:00] OUTRO
**On Screen:** Title card — "Next: Video 6 — First Login & Dashboard Tour"
**Voiceover:** "VentureX is installed and running. In the next video, we'll log in with the demo credentials and explore every corner of the dashboard. I'll see you there."
**Action:** Fade to title card.
