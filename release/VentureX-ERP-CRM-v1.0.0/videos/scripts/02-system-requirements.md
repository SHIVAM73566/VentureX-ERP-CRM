# Video 2: System Requirements & Download
**Duration:** 4 minutes 30 seconds
**Difficulty:** Beginner

## Pre-Recording Checklist
- [ ] Screen recording software ready
- [ ] Resolution: 1920x1080
- [ ] Browser zoom: 100%
- [ ] Terminal font size: 14pt+
- [ ] Clean desktop, no personal files visible
- [ ] Browser with Codester marketplace page loaded
- [ ] Terminal/PowerShell window ready
- [ ] PHP, MySQL, Composer, Node.js installed and verified

## Script

### [0:00 - 0:15] INTRO
**On Screen:** Title card "System Requirements & Download" with VentureX branding, then cut to terminal window
**Voiceover:** "Alright, before we install VentureX, let's make sure your system has everything it needs. Don't worry — I'll walk you through checking each requirement, and I'll show you exactly what to do if something's missing."
**Action:** Fade from title card to terminal window

### [0:15 - 0:55] PHP REQUIREMENT
**On Screen:** Terminal window with command prompt
**Voiceover:** "First up — PHP. VentureX needs PHP 8.3 or higher. Let's check what you've got. Open your terminal and type this command."
**Action:** Type `php -v` and press Enter. Show output with PHP version highlighted.
**Voiceover:** "See that version number? You need 8.3 or above. If you see something like 8.2 or lower, you'll need to upgrade. On Windows, I'd recommend downloading from the official PHP website. On Mac, use Homebrew. On Linux, your package manager makes this pretty straightforward."
**Action:** Highlight the version number in the output with mouse cursor

### [0:55 - 1:35] MYSQL REQUIREMENT
**On Screen:** Terminal window, previous command still visible
**Voiceover:** "Next — MySQL. You need version 8.0 or higher. MariaDB 10.6 or above works too, if that's what you prefer. Let's check."
**Action:** Type `mysql --version` and press Enter. Show output.
**Voiceover:** "There's my MySQL version. If you don't have MySQL installed yet, don't panic — we'll cover the database setup in the next video. Just make a note that you'll need it."
**Action:** Highlight MySQL version in output

### [1:35 - 2:10] COMPOSER REQUIREMENT
**On Screen:** Terminal window
**Voiceover:** "Now, Composer — this is PHP's package manager. Think of it like npm, but for PHP. You need version 2.x."
**Action:** Type `composer --version` and press Enter. Show output.
**Voiceover:** "If you see version 2.something, you're good. If Composer isn't installed, head to getcomposer.org and follow their installation guide. It's a one-time setup."
**Action:** Highlight Composer version

### [2:10 - 2:45] NODE.JS REQUIREMENT
**On Screen:** Terminal window
**Voiceover:** "We also need Node.js and npm. VentureX uses Tailwind CSS for the frontend, so we need Node to compile those assets. Version 20 or higher is what we're after."
**Action:** Type `node --version` and press Enter, then type `npm --version` and press Enter.
**Voiceover:** "There's my Node version and npm version. Both look good. If you need to install or update Node, I recommend using nvm — the Node Version Manager — it makes switching between versions really easy."
**Action:** Highlight both version outputs

### [2:45 - 3:15] WEB SERVER
**On Screen:** Terminal window, then brief cut to Apache/Nginx config
**Voiceover:** "Finally, you'll need a web server. Apache with mod_rewrite enabled works great. Nginx is also fully supported. But here's a nice shortcut for development — PHP has a built-in server that works perfectly for getting started. We'll use that in this series to keep things simple."
**Action:** Brief flash of Apache config, then back to terminal

### [3:15 - 3:55] DOWNLOAD FROM CODESTER
**On Screen:** Browser navigating to Codester marketplace
**Voiceover:** "Okay, requirements check — done! Now let's grab the files. Head over to the Codester marketplace and search for VentureX ERP. The item number is VentureX-ERP-2026-01483863236. You can also find the direct link in the video description below."
**Action:** Navigate to Codester, search for VentureX, click on the listing, show the product page
**Voiceover:** "Once you're on the page, hit that download button. You'll get a ZIP file containing everything you need."

### [3:55 - 4:30] EXTRACT & FILE STRUCTURE
**On Screen:** File explorer showing downloaded ZIP, then extraction, then folder structure
**Voiceover:** "Let's extract this and take a look at what's inside. I'll right-click the ZIP file and extract it to my project folder."
**Action:** Right-click ZIP file, extract to destination folder, open the extracted folder
**Voiceover:** "Here's the file structure. You can see the standard Laravel directories — app, config, database, resources, routes — plus some custom modules for the ERP functionality. Don't worry about memorizing all this now. We'll explore each part as we go through the installation. In the next video, we'll set up our database so VentureX has somewhere to store all its data. See you there!"
**Action:** Scroll through folder structure, highlight key directories, fade to end card
