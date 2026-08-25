# Video 13: WordPress Integration — Connect Your Website to VentureX
## Duration: 15 minutes
## Target Audience: WordPress site owners, marketers, small business owners
## Difficulty: Beginner

---

## SCENE-BY-SCENE BREAKDOWN

### Scene 1: Introduction (00:00 - 01:15)
**Screen:** Title card, then split screen — WordPress dashboard on left, VentureX dashboard on right
**Voiceover:** "Hey everyone! Welcome back. So you've got VentureX running and you've got a WordPress website. Wouldn't it be great if they talked to each other? Like, someone fills out a contact form on your WordPress site and it automatically shows up as a lead in VentureX CRM? That's exactly what we're building today. I'm going to show you how to install the WordPress connector plugin, connect it to VentureX, and set up automatic lead capture. This is a game-changer for sales teams. Let's jump in."
**Action:** Cursor moves between WordPress and VentureX dashboards
**Caption:** Video 13 — WordPress Integration

### Scene 2: Getting Your VentureX API Token (01:15 - 03:00)
**Screen:** VentureX dashboard
**Voiceover:** "First, we need an API token from VentureX. This is like a password that lets WordPress securely talk to your VentureX system. Open VentureX and go to Settings. Click on API Tokens or Integrations — depending on your version, it might be in either spot. Click Create New Token. Give it a name like 'WordPress Connector' so you know what it's for. Set the permissions — for WordPress, you need Read and Write access to Contacts and Leads. If you want to sync products too, add Products read access. Click Generate. Copy the token immediately — it'll only show once. Save it somewhere safe, like a password manager. We'll need it in a minute."
**Action:** Navigate to Settings, create token, select permissions, copy token
**Caption:** Settings → API Tokens → Create → Copy & save securely

### Scene 3: Installing the WordPress Plugin (03:00 - 05:00)
**Screen:** WordPress dashboard → Plugins → Add New
**Voiceover:** "Now let's install the connector on WordPress. Log into your WordPress admin dashboard. Go to Plugins, Add New. Search for 'VentureX Connector' or if your version uses a custom plugin, click Upload Plugin and upload the zip file. I've got the zip ready — click Choose File, select it, Install Now. Once installed, click Activate. You'll see a new menu item in your sidebar called 'VentureX Settings' or it might appear under Settings. Click into it. You'll see a simple form with two fields — the API URL and the API Token. Let's fill those in."
**Action:** Navigate WordPress plugins, upload and activate, find settings page
**Caption:** Plugins → Upload → Activate → VentureX Settings

### Scene 4: Configuring the Connection (05:00 - 07:00)
**Screen:** WordPress → VentureX Settings page
**Voiceover:** "In the API URL field, type the full URL of your VentureX installation — for example, https://yourdomain.com or http://localhost/venturex if you're testing locally. Make sure there's no trailing slash. In the API Token field, paste the token you copied from VentureX. Now click the Test Connection button. You should see a green success message that says 'Connected to VentureX.' If you see a red error, double-check the URL and token — usually it's a typo or the token doesn't have the right permissions. Once connected, save the settings. The plugin is now linked to your CRM."
**Action:** Enter URL and token, click Test Connection, show success message
**Caption:** API URL + Token → Test Connection → Green success

### Scene 5: Contact Form Integration (07:00 - 09:30)
**Screen:** WordPress → Contact Form settings (WPForms, Contact Form 7, or Elementor)
**Voiceover:** "Now the fun part — connecting your contact forms. This works with most popular form plugins. Let me show you a few. If you're using WPForms, open your form in the editor. Click on Settings, then look for 'VentureX' or 'CRM Integration' in the tabs. Toggle it on. Map your form fields — Name maps to Contact Name, Email maps to Email, Phone maps to Phone, and the Message field maps to Notes. Save. If you're using Contact Form 7, install the VentureX CF7 addon or use the plugin's built-in integration tab. Same idea — enable it, select which form to connect, map the fields. If you're using Elementor Forms, open the form widget, go to Actions After Submit, add 'VentureX' as an action, and configure the field mapping. The process is basically the same across all form plugins — enable the integration, map your fields, save. Now when someone fills out any of these forms, the contact automatically appears in your VentureX CRM with all their details."
**Action:** Show integration in 2-3 different form plugins, map fields
**Caption:** Form → Settings → VentureX → Map fields → Save

### Scene 6: Lead Capture Shortcode (09:30 - 11:00)
**Screen:** WordPress post editor or page editor
**Voiceover:** "Want to add a VentureX lead capture form directly into any WordPress post or page? The plugin gives you a shortcode. Go back to VentureX Settings in WordPress, and you'll see a tab called 'Lead Capture.' Choose the fields you want — name, email, phone, company, message. Customize the button text — I'll set it to 'Get a Free Quote.' Copy the shortcode it generates. Now open any WordPress post or page, add a Shortcode block, and paste it in. Preview the page — you'll see a clean, styled form right there. When someone submits it, they're automatically created as a lead in VentureX with the 'Website Lead' source tag. You can customize the colors in the shortcode settings to match your brand."
**Action:** Generate shortcode, paste into page, preview form, submit test
**Caption:** Shortcode → Paste in page → Auto-creates leads in VentureX

### Scene 7: WooCommerce Integration Overview (11:00 - 13:00)
**Screen:** WordPress → WooCommerce → VentureX settings
**Voiceover:** "If you're running WooCommerce — that's WordPress's e-commerce plugin — you can sync your customers and orders to VentureX too. In the VentureX WordPress plugin settings, find the WooCommerce tab. Toggle it on. You can choose to sync new orders as deals in VentureX, sync customer data as contacts, and even push product catalog data. Set the sync direction — I recommend 'WordPress to VentureX' so your CRM stays up to date with your store activity. Map your WooCommerce order statuses to VentureX deal stages — like 'Processing' maps to 'In Progress,' 'Completed' maps to 'Won.' Save. Now every new WooCommerce order creates a deal in VentureX with the customer's info, order total, and products purchased. Your sales team can see exactly what customers are buying without logging into WooCommerce."
**Action:** Enable WooCommerce sync, configure mapping, show example deal
**Caption:** WooCommerce → Sync orders as deals, customers as contacts

### Scene 8: Troubleshooting Connection Issues (13:00 - 14:30)
**Screen:** Browser console, WordPress debug log, VentureX error log
**Voiceover:** "Running into issues? Here are the most common ones. Problem: 'Connection failed' when testing. Fix: Check that your VentureX URL is correct and accessible from your WordPress server. Some hosts block outgoing HTTP requests — you may need to whitelist VentureX's IP. Problem: Forms submit but nothing shows in VentureX. Fix: Open your browser's developer tools — press F12, go to the Network tab, submit the form, and look at the response. If you see a 401 error, your API token is invalid or expired. If you see a 403, the token doesn't have the right permissions. Problem: WooCommerce orders aren't syncing. Fix: Check that the WooCommerce REST API is enabled in WooCommerce settings, and make sure your VentureX token has Products and Deals write access. Still stuck? Enable WordPress debug mode by editing wp-config.php — set WP_DEBUG to true — then check wp-content/debug.log for detailed error messages."
**Action:** Show browser console errors, fix each issue
**Caption:** F12 → Network tab → Check response → Fix token/permissions

### Scene 9: Wrap-Up (14:30 - 15:00)
**Screen:** Both dashboards side by side, showing a lead that came in from WordPress
**Voiceover:** "And that's WordPress and VentureX working together! Every form submission, every purchase, every lead — flowing straight into your CRM without any manual entry. In the next video, we'll cover integrating with Wix, Blogger, and other platforms using the REST API. See you there!"
**Action:** Show a synced lead with all details populated
**Caption:** WordPress + VentureX = Automated lead capture

---

## SCREEN RECORDING CHECKLIST
- [ ] Record API token creation in VentureX
- [ ] Record WordPress plugin upload and activation
- [ ] Record API URL and token configuration
- [ ] Record Test Connection success
- [ ] Record contact form integration with WPForms or CF7
- [ ] Record shortcode generation and embedding
- [ ] Record WooCommerce integration setup
- [ ] Record troubleshooting scenarios (F12, debug log)

## THUMBNAIL DESIGN
- Main text: "WordPress + VentureX"
- Visual elements: WordPress logo + VentureX logo + chain link icon + form fields
- Color scheme: navy (#0f172a) + teal (#14b8a6)
