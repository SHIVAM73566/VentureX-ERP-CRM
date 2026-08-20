# Video 14: Wix, Blogger & External Integrations — Connect Anything to VentureX
## Duration: 17 minutes
## Target Audience: Website owners on various platforms, developers, technically inclined users
## Difficulty: Intermediate

---

## SCENE-BY-SCENE BREAKDOWN

### Scene 1: Introduction (00:00 - 01:15)
**Screen:** Title card, then a montage of different website platforms — Wix, Blogger, custom sites
**Voiceover:** "Hey everyone! Welcome back. So maybe you're not on WordPress. Maybe your website is on Wix, or Blogger, or a custom-built site that uses React or plain HTML. Good news — VentureX has a REST API that lets you connect to literally anything. In this video, I'm going to show you how the API works, how to create an API token, and then walk through real integration examples for Wix, Blogger, and custom websites. Let's open up the API docs."
**Action:** Cursor shows different platform logos, transitions to VentureX dashboard
**Caption:** Video 14 — Wix, Blogger & External Integrations

### Scene 2: REST API Overview (01:15 - 03:30)
**Screen:** VentureX dashboard → API Documentation page
**Voiceover:** "Let's start with how the API works. VentureX uses a standard REST API — that means you send HTTP requests to specific URLs and get data back. Every request needs two things: the API URL and an authentication token. The main endpoints you'll use are: POST /api/contacts — this creates a new contact or lead. GET /api/contacts — retrieves your contacts. POST /api/deals — creates a deal or opportunity. GET /api/deals — retrieves your deals. POST /api/webhooks — sets up webhooks for real-time notifications. Each request needs an Authorization header with your token. The data you send goes in the body as JSON. Let me show you exactly how this works in practice."
**Action:** Scroll through API documentation, highlight key endpoints
**Caption:** REST API — POST/GET contacts, deals, webhooks + JSON + Auth token

### Scene 3: Creating an API Token (03:30 - 05:00)
**Screen:** VentureX dashboard → Settings → API Tokens
**Voiceover:** "Let's create your API token. Go to Settings in VentureX, then API Tokens. Click Create New Token. Give it a descriptive name — I'll use 'External Integration' since this will be used across multiple platforms. Now the important part — permissions. For a basic contact capture setup, you need: Contacts — Read and Write. That's the minimum. If you want to create deals too, add Deals — Write. If you want to read product data, add Products — Read. I'll also add Leads — Write, since contacts from external forms should be tagged as leads. Click Generate. Copy the token right away — you won't see it again. Now let's use it."
**Action:** Create token with appropriate permissions, copy it
**Caption:** Settings → API Tokens → Create → Set permissions → Copy

### Scene 4: Wix Velo Integration (05:00 - 08:30)
**Screen:** Wix Velo editor (code view)
**Voiceover:** "Let's start with Wix. Wix has a development environment called Velo that lets you write custom JavaScript. Open your Wix site in the editor, click Dev Mode at the top, and turn it on. Now you can add custom code. Create a new backend file — click the plus icon in the code sidebar, select Backend, name it venturex.jsw. This is where your API calls live. Now I'll paste in a function to send contacts to VentureX. Here's the code: export async function sendToVenturex(contactData) { const response = await fetch('https://yourdomain.com/api/contacts', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer YOUR_API_TOKEN' }, body: JSON.stringify(contactData) }); return response.json(); } Now in your frontend page code, call this function when a form is submitted. Let me show you — find your form's onClick or onSubmit handler, and add: sendToVenturex({ name: $w('#nameInput').value, email: $w('#emailInput').value, phone: $w('#phoneInput').value, source: 'Wix Website' }); That's it. When someone fills out your Wix form, it sends the data straight to VentureX. Test it by submitting the form and checking your VentureX contacts."
**Action:** Enable Velo, create backend file, paste API function, connect to form
**Caption:** Velo backend → fetch() to API → Form onSubmit → Contact in VentureX

### Scene 5: Blogger JavaScript Gadget (08:30 - 10:30)
**Screen:** Blogger dashboard → Layout → Add Gadget
**Voiceover:** "Now Blogger. Blogger is simpler — we'll use a JavaScript gadget. Log into Blogger, go to Layout. Click Add a Gadget and choose HTML/JavaScript. Title it 'Contact Us.' Now paste in this code — it's a simple form with JavaScript that sends data to VentureX when submitted. The form has name, email, and message fields. The script captures the form data on submit, packages it as JSON, and sends a POST request to your VentureX API endpoint using the fetch API. Make sure to replace the API URL and token with your own. Click Save, then View Blog. You'll see your form on the page. Fill it in and submit — check VentureX and the contact appears. One thing to note — Blogger's Content Security Policy might block external requests. If that happens, you'll need to use a proxy service or host a small Cloudflare Worker that forwards the request. I'll link a tutorial for that in the description."
**Action:** Add HTML/JavaScript gadget, paste form code, save, test
**Caption:** Blogger → HTML/JavaScript gadget → fetch() → VentureX API

### Scene 6: Custom Website Integration with cURL/fetch (10:30 - 13:00)
**Screen:** Code editor showing HTML page, then terminal with curl command
**Voiceover:** "Got a custom website? Here's how to integrate with any tech stack. Let me show you three methods. Method one — JavaScript fetch, for any modern website. Add this to your contact form's submit handler: fetch('https://yourdomain.com/api/contacts', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer YOUR_TOKEN' }, body: JSON.stringify({ name: form.name.value, email: form.email.value, phone: form.phone.value, source: 'Custom Website' }) }).then(res => res.json()).then(data => alert('Thank you! We\'ll be in touch.')); Method two — PHP cURL, for server-side integration. This is more secure because your API token stays on the server. $ch = curl_init('https://yourdomain.com/api/contacts'); curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer YOUR_TOKEN']); curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($_POST)); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); $response = curl_exec($ch); Method three — Python requests, for data pipelines or scripts. import requests; requests.post('https://yourdomain.com/api/contacts', json={'name': 'John', 'email': 'john@example.com', 'source': 'Python Script'}, headers={'Authorization': 'Bearer YOUR_TOKEN'}). Any of these approaches work. Pick the one that matches your stack."
**Action:** Show JavaScript, PHP, and Python examples side by side
**Caption:** fetch() / cURL / requests.post() — pick your language

### Scene 7: Webhook Configuration (13:00 - 15:00)
**Screen:** VentureX dashboard → Webhooks settings
**Voiceover:** "Webhooks let VentureX push data to YOUR systems in real-time. Say you want to get a Slack notification every time a new deal is won, or push contacts to a Google Sheet. In VentureX, go to Settings, then Webhooks. Click Create Webhook. Give it a name — 'Slack Notification.' Set the URL — that's where VentureX will send the data. For Slack, you'd use an incoming webhook URL from your Slack workspace. Choose the events that trigger it — I'll select 'Deal Won' and 'New Contact.' You can also set a secret key for verifying the webhook payload. Save it. Now test — create a deal and mark it as won. Check Slack — you should see the notification arrive instantly. You can create multiple webhooks for different events and different destinations. This is how you build real-time workflows without any polling or scheduled tasks."
**Action:** Create webhook, configure URL and events, test with a deal
**Caption:** Settings → Webhooks → URL + Events → Real-time data push

### Scene 8: CORS and Security (15:00 - 16:30)
**Screen:** Code editor showing CORS headers, browser console showing CORS error
**Voiceover:** "Quick but important section on security. If you're calling the API from a browser-based app — like Wix, Blogger, or a custom React site — you might run into CORS errors. That's the browser blocking cross-origin requests for security. To fix this, VentureX needs to include the right CORS headers. In your Laravel config, open config/cors.php. Set allowed_origins to include your website's domain — for example, ['https://yourwixsite.com']. If you're developing locally, add 'http://localhost:3000'. Set supports_credentials to true if you're using cookies. A few other security tips: never put your API token in client-side JavaScript that's visible to everyone. For public-facing forms, use a backend proxy or serverless function. Always use HTTPS — never send API tokens over plain HTTP. And rotate your tokens periodically — if a token is compromised, regenerate it in VentureX settings. These aren't just best practices — they protect your customers' data."
**Action:** Show CORS config, demonstrate CORS error, show fix
**Caption:** CORS config + HTTPS + Backend proxy + Token rotation = Security

### Scene 9: Wrap-Up (16:30 - 17:00)
**Screen:** Montage of different platforms all sending data to VentureX
**Voiceover:** "And there you have it — Wix, Blogger, custom sites, all connected to VentureX. The REST API is your Swiss Army knife. Any platform that can make an HTTP request can send data to your CRM. In the next video, we'll cover backup, restore, and troubleshooting — keeping your VentureX installation safe and running smooth. See you there!"
**Action:** Show multiple platforms flowing data into VentureX dashboard
**Caption:** Any platform → REST API → VentureX CRM

---

## SCREEN RECORDING CHECKLIST
- [ ] Record API documentation walkthrough
- [ ] Record API token creation with permissions
- [ ] Record Wix Velo editor setup and code
- [ ] Record Blogger gadget creation and code
- [ ] Record JavaScript fetch, PHP cURL, and Python examples
- [ ] Record webhook creation and test notification
- [ ] Record CORS error and fix
- [ ] Record browser console during testing

## THUMBNAIL DESIGN
- Main text: "Connect Anything"
- Visual elements: Multiple platform logos (Wix, Blogger, custom) + API icon + arrows pointing to VentureX
- Color scheme: navy (#0f172a) + teal (#14b8a6)
