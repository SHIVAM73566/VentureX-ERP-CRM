# Video 7: CRM & Sales Pipeline
**Duration:** 5-6 minutes

## Pre-Recording Checklist

- [ ] VentureX running at http://localhost:8000
- [ ] Logged in as demo_admin@example.com
- [ ] Clean CRM data (no existing companies/contacts)
- [ ] Sample logo/image files ready for company records
- [ ] Browser window at 1920×1080, zoom at 100%
- [ ] Notifications dismissed, calendar cleared
- [ ] Screen recording software active at 1080p 60fps
- [ ] Sales Pipeline stages visible: Prospecting, Qualification, Proposal, Negotiation, Closed Won, Closed Lost

---

## Script

### [0:00 - 0:15] INTRO
**On Screen:** Title card "Video 7: CRM & Sales Pipeline" over blurred CRM dashboard
**Voiceover:** "Welcome back to the VentureX tutorial series. In this video, we're diving into the CRM and Sales Pipeline modules — the heart of your customer relationship management. We'll cover companies, contacts, activities, and how to manage your entire sales funnel from lead to close."
**Action:** Fade in title card, hold 3 seconds, fade to CRM dashboard

---

### [0:15 - 0:35] CRM OVERVIEW
**On Screen:** VentureX sidebar, CRM module expanded showing Companies, Contacts, Activities, Sales Pipeline
**Voiceover:** "Let's start with the CRM module. VentureX organizes your customer data into three core components: Companies — the organizations you do business with, Contacts — the people within those companies, and Activities — every interaction you've logged. Everything connects, giving you a 360-degree view of every relationship."
**Action:** Click CRM in sidebar, hover over each submenu item as mentioned

---

### [0:35 - 1:20] ADDING A NEW COMPANY
**On Screen:** CRM → Companies → Company list (empty state)
**Voiceover:** "Let's add our first company. I'll click on Companies in the sidebar, and since this is a fresh install, we see the empty state. Let's click the Add Company button."
**Action:** Click "Companies" in sidebar, click "Add Company" button

**On Screen:** Company creation form
**Voiceover:** "The form asks for the essentials. Company name — I'll enter Acme Corporation. Industry — let's select Technology. Website — acme-corp.com. Phone, email, address — all optional but recommended. Notice the custom fields section at the bottom — you can add your own data points like Company Size or Annual Revenue. Let me fill in a few of these."
**Action:** Type "Acme Corporation" in Company Name field, select "Technology" from Industry dropdown, type "acme-corp.com" in Website field, fill in phone "555-0100", email "info@acme-corp.com", expand Custom Fields, type "50-200" in Company Size, type "$5M" in Annual Revenue

**On Screen:** Company form filled out, Save button highlighted
**Voiceover:** "Before saving, notice the Tags field — these are great for segmenting companies later. I'll add 'Enterprise' and 'Active Client'. Now let's save."
**Action:** Type "Enterprise" in tags, press Enter, type "Active Client", press Enter, click "Save Company"

---

### [1:20 - 1:55] ADDING A CONTACT
**On Screen:** Contacts page, empty state
**Voiceover:** "Now let's add a contact — a person at Acme Corporation. I'll navigate to Contacts and click Add Contact."
**Action:** Click "Contacts" in sidebar, click "Add Contact"

**On Screen:** Contact creation form
**Voiceover:** "First name — Sarah. Last name — Mitchell. The Company field is a searchable dropdown that pulls from our Companies module. Watch this — I'll start typing Acme and it auto-completes. This creates a relational link between the contact and the company. Email, phone, title — I'll set her as VP of Sales. We can also assign an Account Owner — the VentureX user responsible for this relationship."
**Action:** Type "Sarah" in First Name, "Mitchell" in Last Name, type "Acme" in Company field and select "Acme Corporation" from dropdown, type "sarah.mitchell@acme-corp.com" in Email, "555-0101" in Phone, "VP of Sales" in Title, select "Demo Admin" from Account Owner dropdown

**On Screen:** Contact form filled, Save button
**Voiceover:** "Same custom fields and tags system as Companies. Let me add the tag 'Decision Maker' and save this contact."
**Action:** Add "Decision Maker" tag, click "Save Contact"

---

### [1:55 - 2:35] LOGGING ACTIVITIES
**On Screen:** Activities page or Contact detail view for Sarah Mitchell
**Voiceover:** "Activities are how you track every touchpoint. Calls, emails, meetings, notes — everything lives here and can be tied to a specific contact or company. Let's log an activity for Sarah."
**Action:** Click into Sarah Mitchell's contact detail page, click "Log Activity" button

**On Screen:** Activity form
**Voiceover:** "Activity type — I'll choose Call. Subject: Introductory Call. Date and time: let's set it to today. Duration: 30 minutes. I'll add a note: 'Discussed Acme's current ERP pain points. They're using spreadsheets and losing data. Interested in demo of inventory module.' The Activity Status — Completed. We can also link this to a Deal, but we haven't created one yet. We'll come back to that."
**Action:** Select "Call" from Type dropdown, type "Introductory Call" in Subject, leave date as today, set time to 10:00 AM, type "30" in Duration field, type the note text in Description field, select "Completed" from Status

**On Screen:** Save activity, Activity appears in Sarah's timeline
**Voiceover:** "When I save this, notice how it appears in Sarah's activity timeline. Every future activity, email, and deal will stack here chronologically. Let's add one more — a meeting."
**Action:** Click "Save Activity", click "Log Activity" again, select "Meeting" type, type "Product Demo" in Subject, type "Conducted full ERP demo. Focused on inventory and CRM modules. Very positive response." in Description, select "Completed", click "Save"

---

### [2:35 - 2:50] ACTIVITY LIST VIEW
**On Screen:** Activities list view showing both logged activities
**Voiceover:** "From the main Activities page, you can see all activities across all contacts and companies. You can filter by type, date range, status, or assigned user. This is your activity feed — your single source of truth for every interaction."
**Action:** Click "Activities" in sidebar, hover over filter options, click filter dropdown showing Type options

---

### [2:50 - 3:35] SALES PIPELINE — KANBAN VIEW
**On Screen:** Sales Pipeline module, Kanban board view with empty stages
**Voiceover:** "Now let's move to the Sales Pipeline — this is where the magic happens. VentureX uses a Kanban-style board where each column represents a pipeline stage. Out of the box, you get: Prospecting, Qualification, Proposal, Negotiation, Closed Won, and Closed Lost. You can customize these stages in Settings."
**Action:** Click "Sales Pipeline" in sidebar, show the empty Kanban board

**On Screen:** Create Deal form
**Voiceover:** "Let's create a deal for Acme Corporation. I'll click New Deal. Deal name: 'Acme — Enterprise ERP Suite'. Company: Acme Corporation — the auto-complete is here too. Contact: Sarah Mitchell. Stage: Prospecting. Deal value: I'll set this at $45,000. Expected close date: end of next month. Probability — this auto-fills based on the stage. For Prospecting, it defaults to 10%. I can override it manually if I have a gut feeling this is more likely."
**Action:** Click "New Deal" button, fill in Deal Name "Acme — Enterprise ERP Suite", select Acme Corporation for Company, select Sarah Mitchell for Contact, select "Prospecting" stage, type "45000" in Deal Value, set Expected Close Date to one month out, show the Probability field auto-filled at 10%

**On Screen:** Deal form with optional fields
**Voiceover:** "Optional fields include Assigned To, Priority, and a Description. I'll set Priority to High and add a brief description. Let's save this deal."
**Action:** Select "High" for Priority, type "Hot lead from intro call. Ready for full demo." in Description, click "Save Deal"

---

### [3:35 - 4:10] MOVING DEALS THROUGH STAGES
**On Screen:** Kanban board showing "Acme — Enterprise ERP Suite" in Prospecting column
**Voiceover:** "Here's our deal in the Prospecting stage. Now watch this — I'm going to drag it to Qualification. This is the beauty of Kanban: visual, intuitive, and instant."
**Action:** Drag the deal card from Prospecting to Qualification column

**On Screen:** Deal card now in Qualification, Deal detail opens
**Voiceover:** "When I click into the deal, you can see all the details, activity history, and the linked contact and company. The Activity Log shows our introductory call. Let me log a follow-up activity directly from here, then move this deal forward."
**Action:** Click deal to open detail view, click "Log Activity" within deal, select "Email", type "Follow-Up — Demo Scheduled", type "Sent demo calendar invite for Thursday. Sarah confirmed attendance." in Description, save activity

**On Screen:** Back on Kanban, drag deal to Proposal
**Voiceover:** "Great demo happened, so I'm moving this to Proposal stage. Probability jumps to 40%. Let's open it and log the proposal sent."
**Action:** Drag deal from Qualification to Proposal, click deal, log activity type "Email", subject "Proposal Sent — Acme ERP Suite", description "Sent full proposal with pricing breakdown. Includes CRM, Inventory, and Accounting modules. 12-month contract at $45K.", save activity

**On Screen:** Drag deal to Negotiation
**Voiceover:** "They're negotiating on pricing. Moving to Negotiation — probability now at 60%."
**Action:** Drag deal from Proposal to Negotiation

**On Screen:** Drag deal to Closed Won
**Voiceover:** "And... they signed! Moving to Closed Won. The probability is 100%, the deal value is locked in, and this now shows in your revenue reports."
**Action:** Drag deal from Negotiation to Closed Won, celebrate visually by hovering over the deal

---

### [4:10 - 4:45] DEAL VALUES AND PIPELINE METRICS
**On Screen:** Kanban board with deal in Closed Won, pipeline summary bar visible
**Voiceover:** "Let's look at the numbers. At the top of the pipeline, you can see summary metrics — total pipeline value, weighted pipeline value based on probability, number of deals per stage, and your win rate. Let's create a second deal so these numbers are more interesting."
**Action:** Point to pipeline summary metrics at top of page

**On Screen:** Create Deal form
**Voiceover:** "Second deal: 'TechStart — Starter CRM Package', Contact: someone new, Value: $8,000, Stage: Qualification, Probability: 30%."
**Action:** Click "New Deal", fill in "TechStart — Starter CRM Package", create new contact "James Park" at "TechStart Inc.", set Value to $8000, Stage to Qualification, save deal

**On Screen:** Pipeline with two deals, metrics updated
**Voiceover:** "Now the pipeline shows $53,000 in total value, with a weighted forecast of about $44,100. This weighted number is what you'd use for revenue forecasting — it accounts for the probability of each deal closing."
**Action:** Hover over pipeline metrics, point to weighted value

---

### [4:45 - 5:10] SALES REPORTS
**On Screen:** Reports or Analytics section showing Sales Reports
**Voiceover:** "VentureX includes built-in sales reports. You can view deals by stage, by rep, by time period, and by value. The pipeline visualization shows conversion rates between stages — how many prospects become qualified, how many proposals become negotiations, and ultimately, your close rate. These reports update in real-time as you move deals through the pipeline."
**Action:** Navigate to Reports → Sales Reports, hover over different report widgets, show funnel visualization, show deals-by-stage chart

---

### [5:10 - 5:35] CRM-TO-SALES RELATIONSHIP
**On Screen:** Navigate to Sarah Mitchell's contact page showing linked deal
**Voiceover:** "One of the most powerful aspects of VentureX is how CRM and Sales connect. Go to Sarah Mitchell's contact page — you'll see her company, her activities, and the $45,000 Acme deal all in one view. Click on Acme Corporation's company page and you see Sarah, the deal, and every logged activity. This interconnected data means your sales team never loses context. Every touchpoint, every deal, every company — linked and searchable."
**Action:** Click Contacts, open Sarah Mitchell, scroll to show linked deal and activities, click Acme Corporation company link, show company page with all linked records

---

### [5:35 - 5:50] OUTRO
**On Screen:** CRM dashboard with data populated, VentureX logo
**Voiceover:** "That's the CRM and Sales Pipeline in VentureX. You've learned how to manage companies, contacts, activities, and your entire sales funnel. In the next video, we'll cover Inventory and Procurement — how to manage your products, stock levels, and purchase orders. If you found this helpful, like and subscribe for the next installment."
**Action:** Show populated CRM dashboard, fade to end card with "Next: Video 8 — Inventory & Procurement"

---

## Notes for Editor

- Use zoom-in effects when showing form fields
- Highlight mouse cursor with yellow circle for all click actions
- Add subtle transitions between sections (crossfade, 0.5s)
- Ensure all text is readable at 1080p — increase font size in settings if needed
- Background music: low corporate/tech ambient, duck during voiceover
