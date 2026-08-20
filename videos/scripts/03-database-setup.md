# Video 3: Database Setup
**Duration:** 4 minutes 45 seconds
**Difficulty:** Beginner to Intermediate

## Pre-Recording Checklist
- [ ] Screen recording software ready
- [ ] Resolution: 1920x1080
- [ ] Browser zoom: 100%
- [ ] Terminal font size: 14pt+
- [ ] Clean desktop, no personal files visible
- [ ] MySQL server running
- [ ] MySQL root credentials available
- [ ] phpMyAdmin accessible (optional, for alternative method)
- [ ] MySQL Workbench installed (optional, for alternative method)

## Script

### [0:00 - 0:15] INTRO
**On Screen:** Title card "Database Setup" with VentureX branding, then cut to terminal window
**Voiceover:** "Welcome back! Now that we've got all our requirements in place, it's time to set up the database. This is where VentureX will store all its data — customer records, transactions, inventory, everything. Let's get into it."
**Action:** Fade from title card to terminal window with MySQL prompt

### [0:15 - 1:00] MYSQL COMMAND LINE - LOGIN
**On Screen:** Terminal window
**Voiceover:** "I'm going to show you three ways to set this up. First, the command line — this is the most direct method and my personal favorite. Open your terminal and log into MySQL as the root user."
**Action:** Type `mysql -u root -p` and press Enter, then type password when prompted
**Voiceover:** "You'll be prompted for your root password. Type it in — you won't see the characters as you type, that's normal for security. Hit Enter, and if your password is correct, you'll see the MySQL monitor prompt."
**Action:** Show successful login with MySQL monitor prompt visible

### [1:00 - 1:40] CREATE DATABASE
**On Screen:** MySQL monitor prompt
**Voiceover:** "Alright, we're in. Now let's create the database. I'm going to type this command exactly."
**Action:** Type `CREATE DATABASE VENTUREX_ERP;` and press Enter
**Voiceover:** "VENTUREX_ERP — that's the name of our database. The semicolon at the end is important, don't forget it. You should see 'Query OK' which means the database was created successfully. Let's verify it exists."
**Action:** Show 'Query OK' message, then type `SHOW DATABASES;` and press Enter
**Voiceover:** "There it is — VENTUREX_ERP shows up in the list. Perfect."

### [1:40 - 2:20] CREATE USER
**On Screen:** MySQL monitor prompt
**Voiceover:** "Next, we need to create a dedicated user for this database. You never want to use root for your application — that's a security best practice. Let's create a user with limited privileges."
**Action:** Type `CREATE USER 'VENTUREX_ERP_user'@'127.0.0.1' IDENTIFIED BY 'YourSecurePassword123!';` and press Enter
**Voiceover:** "I'm creating a user called VENTUREX_ERP_user that can only connect from localhost — that's the 127.0.0.1 part. And I'm setting a strong password. Now, replace 'YourSecurePassword123!' with something unique and strong. Don't use the same password I'm using here."
**Action:** Show 'Query OK' message

### [2:20 - 3:00] GRANT PRIVILEGES
**On Screen:** MySQL monitor prompt
**Voiceover:** "Now we need to give this user permission to actually work with our database. We'll grant all privileges on the VENTUREX_ERP database to our new user."
**Action:** Type `GRANT ALL PRIVILEGES ON VENTUREX_ERP.* TO 'VENTUREX_ERP_user'@'127.0.0.1';` and press Enter
**Voiceover:** "This gives our user full access to everything inside the VENTUREX_ERP database, but nothing outside of it. That's exactly what we want. Let's flush the privileges to make sure MySQL picks up the changes."
**Action:** Type `FLUSH PRIVILEGES;` and press Enter, then type `EXIT;` to leave MySQL monitor

### [3:00 - 3:30] PHPMYADMIN ALTERNATIVE
**On Screen:** Browser navigating to phpMyAdmin
**Voiceover:** "Now, if the command line feels a bit intimidating, here's the GUI approach using phpMyAdmin. This comes bundled with tools like XAMPP and WAMP, so you might already have it."
**Action:** Open browser, navigate to localhost/phpmyadmin
**Voiceover:** "Log in with your MySQL credentials. Once you're in, click on the 'Databases' tab at the top."
**Action:** Click Databases tab
**Voiceover:** "Type your database name — VENTUREX_ERP — and hit Create. One click, done. For creating a user, go to the 'User accounts' tab, click 'Add user account', fill in the username, host, and password, then scroll down and check 'Grant all privileges' for the VENTUREX_ERP database."
**Action:** Demonstrate creating database and adding user in phpMyAdmin interface

### [3:30 - 4:00] MYSQL WORKBENCH ALTERNATIVE
**On Screen:** MySQL Workbench application
**Voiceover:** "And for those of you who prefer a desktop application, MySQL Workbench is another great option. It's free and available for Windows, Mac, and Linux."
**Action:** Open MySQL Workbench, connect to local MySQL instance
**Voiceover:** "Connect to your MySQL server, then open a new query tab. You can paste the same SQL commands we ran in the terminal — the CREATE DATABASE, CREATE USER, and GRANT PRIVILEGES statements. Execute them one by one, and you're set."
**Action:** Show query editor with SQL commands, execute them

### [4:00 - 4:30] VERIFY SETUP
**On Screen:** Back to terminal or MySQL Workbench
**Voiceover:** "Let's verify everything is working. I'll log in with the new user credentials to make sure the user was created correctly."
**Action:** Type `mysql -u VENTUREX_ERP_user -p -h 127.0.0.1` and press Enter, enter password
**Voiceover:** "Now let's check that we can see our database."
**Action:** Type `SHOW DATABASES;` and press Enter
**Voiceover:** "And there it is — VENTUREX_ERP is visible to our new user. That confirms the privileges were granted correctly. Our database is ready to go."

### [4:30 - 4:45] WRAP UP
**On Screen:** Terminal with successful verification, then fade to end card
**Voiceover:** "That's it! Your database is set up and ready. In the next video, we'll configure the VentureX environment file and connect it to this database. Almost there — see you in the next one!"
**Action:** Fade to end card with series navigation
