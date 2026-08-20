# Video 6: First Login & Dashboard Tour
**Duration:** 6 minutes
**Difficulty:** Beginner

## Pre-Recording Checklist
- [ ] Screen recording ready at 1920x1080
- [ ] VentureX dev server running at http://localhost:8000
- [ ] Browser at full width for desktop view initially
- [ ] Demo credentials ready: demo_admin@example.com / Demo_Admin_2026!
- [ ] Dark mode toggle accessible
- [ ] Voiceover mic tested

---

## Script

### [0:00 - 0:15] INTRO
**On Screen:** Title card — "Video 6: First Login & Dashboard Tour"
**Voiceover:** "Welcome to video six. VentureX is installed and running. Today, we log in for the first time and take a complete tour of the dashboard — stat cards, charts, sidebar navigation, dark mode, and every major section. Let's dive in."
**Action:** Fade in title card, hold 3 seconds, fade to browser.

---

### [0:15 - 0:45] NAVIGATE AND LOG IN
**On Screen:** Browser at `http://localhost:8000/login`
**Voiceover:** "Navigate to `http://localhost:8000`. If you're not already on the login page, you'll be redirected here automatically — VentureX protects every route behind authentication. Enter the demo admin email: `demo_admin@example.com`. Now enter the password: `Demo_Admin_2026!`. Click the Login button. You'll notice the button shows a loading spinner while the server authenticates your credentials and creates a session."
**Action:** Type email into the email field. Type password into the password field. Click Login. Wait for dashboard to load.

---

### [0:45 - 1:15] DASHBOARD OVERVIEW — STAT CARDS
**On Screen:** Dashboard loaded, top section with stat cards visible
**Voiceover:** "Here's the VentureX dashboard. At the top, you see four stat cards giving you an at-a-glance summary of your business. The first card shows Total Contacts — the number of people in your CRM. The second shows Active Deals — deals currently in your pipeline. The third displays Monthly Revenue — your current month's closed revenue. The fourth shows Pending Tasks — items requiring attention. Each card includes a percentage change indicator comparing this period to the last, color-coded green for growth and red for decline. These cards update in real-time as data changes."
**Action:** Hover over each stat card. Point to the percentage change indicators. Pause briefly on each card.

---

### [1:15 - 1:45] DASHBOARD — CHARTS
**On Screen:** Dashboard, charts section below stat cards
**Voiceover:** "Scroll down to the charts section. On the left, the Revenue Chart shows your monthly revenue trend as an area chart. The blue fill represents revenue, and you can hover any point to see the exact amount and month. On the right, the Deals Pipeline Chart displays your deals broken down by stage — qualification, proposal, negotiation, and closed-won — as a horizontal bar chart. Below these, the Activity Timeline shows recent actions across the system — new contacts added, deals updated, tasks completed, and emails sent. Each entry includes a timestamp and the user who performed the action."
**Action:** Scroll to charts. Hover over data points on the revenue chart. Hover over pipeline bars. Scroll to activity timeline and hover over entries.

---

### [1:45 - 2:15] SIDEBAR NAVIGATION — CORE MODULES
**On Screen:** Sidebar navigation visible on the left
**Voiceover:** "Let's walk through the sidebar. At the top is the VentureX logo and your workspace name. Below that, the navigation is organized into logical groups. **Dashboard** — this home view we're looking at now. **Contacts** — your CRM contact database with search, filters, and bulk actions. **Companies** — organization profiles linked to contacts and deals. **Deals** — your sales pipeline with drag-and-drop Kanban boards and list views. **Tasks** — task management with assignments, due dates, priorities, and project grouping."
**Action:** Hover over each menu item as mentioned. Click briefly into Contacts to show the list, then back to Dashboard. Do the same for Companies, Deals, and Tasks.

---

### [2:15 - 2:45] SIDEBAR — BUSINESS MODULES
**On Screen:** Sidebar navigation, scrolling down
**Voiceover:** "Continuing down the sidebar. **Invoices** — create, send, and track invoices with line items, tax calculations, and payment status. **Products** — your product and service catalog with pricing, categories, and inventory tracking. **Reports** — analytics dashboards with exportable charts and data tables for sales, revenue, and team performance. **Calendar** — integrated event scheduling synced with tasks and deal milestones. **Email** — email integration for sending and tracking communications directly from contacts and deals."
**Action:** Hover over each menu item. Click into Invoices briefly. Click into Products. Click into Reports to show chart previews. Return to Dashboard.

---

### [2:45 - 3:10] SIDEBAR — AI AND SYSTEM
**On Screen:** Sidebar navigation, bottom section
**Voiceover:** "The bottom section of the sidebar. **AI Insights** — VentureX's AI-powered analytics. This module uses your configured AI provider to generate deal predictions, contact scoring, revenue forecasting, and smart recommendations. We'll dedicate an entire video to this later. **Settings** — system configuration including your profile, company settings, team management, notification preferences, and integrations. **Error Center** — centralized error logging and monitoring, tracking PHP exceptions, API failures, and system health in real-time. At the very bottom, your user avatar with a dropdown for profile, settings, and logout."
**Action:** Hover over AI Insights, Settings, Error Center. Click into AI Insights briefly to show the interface. Click Settings to show the settings page. Return to Dashboard.

---

### [3:10 - 3:35] DARK MODE TOGGLE
**On Screen:** Dashboard, header area with dark mode toggle
**Voiceover:** "VentureX includes a built-in dark mode. Find the toggle in the top-right header — it's the sun and moon icon. Click it to switch themes. The entire interface transitions smoothly — charts, cards, tables, sidebars, everything. Your preference is saved to your profile, so it persists across sessions. Dark mode reduces eye strain during long work sessions and looks great on OLED displays. Toggle it back to light mode for the rest of this tour."
**Action:** Click dark mode toggle. Show dashboard in dark mode. Scroll down to show charts and cards in dark theme. Toggle back to light mode.

---

### [3:35 - 4:00] HEADER — NOTIFICATIONS AND PROFILE
**On Screen:** Header area
**Voiceover:** "The header has a few more features. The bell icon shows your notification count — click it to see unread notifications for task assignments, deal updates, invoice payments, and system alerts. Each notification is actionable — click to navigate directly to the relevant item. Next to notifications, the profile dropdown shows your name and avatar. Click it for quick access to your profile page, settings, and the logout option. The header also displays your current workspace name on the left, which is useful if VentureX manages multiple organizations."
**Action:** Click notification bell, show notification dropdown. Click profile dropdown, show menu items. Close dropdowns.

---

### [4:00 - 4:30] RESPONSIVE DESIGN
**On Screen:** Browser window being resized
**Voiceover:** "VentureX is fully responsive. Watch as I resize the browser window. At tablet width — around 768 pixels — the sidebar collapses into a hamburger menu. Tap it to slide the sidebar out as an overlay. The stat cards rearrange into a two-column grid. Charts stack vertically. Tables switch to card-based layouts. At mobile width — under 640 pixels — everything stacks into a single column. The header compresses, navigation becomes a bottom tab bar or slide-out menu, and all content remains fully functional. Every module in VentureX — contacts, deals, invoices, tasks — is optimized for touch interaction on mobile devices."
**Action:** Resize browser from full desktop to tablet width. Show sidebar collapse. Resize to mobile width. Show stacked layout. Navigate to Deals page at mobile width to show Kanban on small screen. Return to full width.

---

### [4:30 - 5:00] ERROR CENTER INTRODUCTION
**On Screen:** Error Center page
**Voiceover:** "Let's take a quick look at the Error Center. Navigate to Error Center from the sidebar. This module logs all system errors in one place — PHP exceptions, failed API calls, database query errors, and application-level failures. Each entry shows the error type, message, file and line number, stack trace, frequency count, and first and last occurrence timestamps. You can filter by severity level — critical, warning, or info — and search by error message. This is invaluable during development and production monitoring. Errors are automatically logged by VentureX's exception handler, so you don't need to add manual logging."
**Action:** Navigate to Error Center. Show the error log table. Click on an error entry to show the detail view with stack trace. Use the filter dropdown to show different severity levels.

---

### [5:00 - 5:30] AI INSIGHTS SECTION
**On Screen:** AI Insights page
**Voiceover:** "Finally, let's preview the AI Insights module. Navigate to AI Insights from the sidebar. This is where VentureX's AI provider powers real business intelligence. The dashboard shows a deal win probability score for each active deal, contact engagement predictions, revenue forecasts for the next quarter, and recommended next actions. The AI analyzes your historical data — past deals, communication patterns, and activity logs — to generate these insights. Each insight includes a confidence score and the reasoning behind the recommendation. We'll configure the AI provider in detail in a dedicated video, but this gives you a preview of what's possible."
**Action:** Navigate to AI Insights. Show the overview cards. Scroll to deal predictions table. Hover over confidence scores. Show the recommended actions section.

---

### [5:30 - 5:50] QUICK NAVIGATION SUMMARY
**On Screen:** Dashboard
**Voiceover:** "That's the complete dashboard tour. To recap: four stat cards for at-a-glance metrics, charts for trend analysis, a sidebar with twelve modules covering CRM, ERP, AI, and system management, dark mode for comfortable viewing, full responsive design for mobile and tablet, an error center for monitoring, and AI insights for intelligent business decisions. Every module follows the same design language — consistent table layouts, form patterns, and interaction models."
**Action:** Return to dashboard. Quickly navigate through two or three modules to show design consistency.

---

### [5:50 - 6:00] OUTRO
**On Screen:** Title card — "Next: Video 7 — Contacts & CRM Deep Dive"
**Voiceover:** "You've seen the full dashboard. In the next video, we'll do a deep dive into the Contacts module — creating, importing, filtering, and managing your CRM contacts. See you there."
**Action:** Fade to title card.
