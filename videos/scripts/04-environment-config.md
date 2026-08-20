# Video 4: Environment Configuration & .env
**Duration:** 4 minutes
**Difficulty:** Beginner

## Pre-Recording Checklist
- [ ] Screen recording ready at 1920x1080
- [ ] Terminal open in project root directory
- [ ] Text editor with .env.example visible
- [ ] Browser tab open to build.nvidia.com (for API key demo)
- [ ] Voiceover mic tested

---

## Script

### [0:00 - 0:15] INTRO
**On Screen:** Title card — "Video 4: Environment Configuration & .env"
**Voiceover:** "Welcome back. In this video, we'll configure VentureX's environment file. This controls your database connections, AI provider, mail settings, and third-party integrations. Let's get started."
**Action:** Fade in title card, hold 3 seconds, fade to terminal.

---

### [0:15 - 0:45] COPY .ENV.EXAMPLE
**On Screen:** Terminal at project root `C:\MY_ERP\venturex-erp`
**Voiceover:** "First, copy the example environment file. VentureX ships with `.env.example` containing every supported variable with sensible defaults. Run `copy .env.example .env` on Windows, or `cp .env.example .env` on macOS and Linux."
**Action:** Type and execute: `copy .env.example .env`
**On Screen:** File explorer confirms `.env` file now exists alongside `.env.example`.

---

### [0:45 - 1:15] GENERATE APP_KEY
**On Screen:** Terminal
**Voiceover:** "Every Laravel application requires an encrypted application key. This key is used for hashing, encryption, and secure cookie signing. Generate it now with `php artisan key:generate`. You should see a success message confirming the key was set."
**Action:** Type and execute: `php artisan key:generate`
**On Screen:** Output shows: `Application key set successfully.`

---

### [1:15 - 1:50] WALK THROUGH APP SECTION
**On Screen:** Text editor showing `.env` file, cursor highlighting the APP section
**Voiceover:** "Let's walk through each section. Under APP, `APP_NAME` is your display name — set it to your company name. `APP_ENV` should stay `local` during development and switch to `production` on your live server. `APP_URL` is your application base URL. For local development, keep it at `http://localhost:8000`. `APP_DEBUG` should be `true` locally for detailed error pages, and `false` in production to hide stack traces from users."
**Action:** Highlight each variable as mentioned, pause briefly on each line.

---

### [1:50 - 2:25] DATABASE CONFIGURATION
**On Screen:** Text editor, cursor on Database section
**Voiceover:** "Next, the database section. VentureX defaults to MySQL 8. Set your host to `127.0.0.1`, port to `3306`, and the database name to `VENTUREX_ERP` — that's the default. Enter your MySQL username and password. If you're using the bundled XAMPP or Laragon setup, your credentials are likely `root` with an empty password. For production, use a dedicated database user with restricted permissions."
**Action:** Highlight `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` in sequence.

---

### [2:25 - 2:50] SESSION AND MAIL
**On Screen:** Text editor, Session and Mail sections visible
**Voiceover:** "Session settings control how user login state is managed. `SESSION_DRIVER=database` stores sessions in your database for reliability. You can switch to `redis` for higher performance. `SESSION_LIFETIME` is in minutes — 120 means users stay logged in for two hours of inactivity. For mail, VentureX uses `smtp` by default. Configure your SMTP host, port, username, and password for your email provider. Set `MAIL_ENCRYPTION` to `tls` for most providers like Gmail or SendGrid."
**Action:** Highlight `SESSION_DRIVER`, `SESSION_LIFETIME`, then move to `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`.

---

### [2:50 - 3:20] AI GATEWAY PROVIDER
**On Screen:** Text editor, AI Gateway section
**Voiceover:** "Now the AI Gateway — one of VentureX's standout features. The `AI_PROVIDER` variable determines which AI backend powers your insights, predictions, and chatbot. You have four options: `swift` for SwiftChat AI, `gemini` for Google Gemini, `deepseek` for DeepSeek, and `nvidia` for NVIDIA's NIM platform. NVIDIA offers a free tier at build.nvidia.com — sign up, grab your API key, and paste it into `NVIDIA_API_KEY`. For this tutorial, we'll use the NVIDIA provider since the free tier is generous enough for development and testing."
**Action:** Highlight `AI_PROVIDER`, then each option. Open browser tab to build.nvidia.com briefly, show the API key page, return to editor.

---

### [3:20 - 3:40] OPTIONAL INTEGRATIONS
**On Screen:** Text editor, bottom of `.env` file
**Voiceover:** "Finally, the optional integrations. VentureX supports PayPal for payment processing — just add your `PAYPAL_CLIENT_ID` and `PAYPAL_CLIENT_SECRET`. Firebase handles push notifications and real-time features with your `FIREBASE_CREDENTIALS` JSON. Google OAuth and Microsoft OAuth enable social login — register your app in each provider's console and paste your client IDs and secrets here. All of these are optional. You can run VentureX fully without any of them configured."
**Action:** Highlight each integration block as mentioned.

---

### [3:40 - 3:55] DATABASE-SETUP.MD REFERENCE
**On Screen:** Terminal or editor showing `DATABASE-SETUP.md`
**Voiceover:** "If you need a deeper walkthrough on database creation and user privileges, check the `DATABASE-SETUP.md` file included in your project root. It covers MySQL setup for both Windows and macOS with step-by-step commands."
**Action:** Open and briefly scroll through `DATABASE-SETUP.md`.

---

### [3:55 - 4:00] OUTRO
**On Screen:** Title card — "Next: Video 5 — Installation & Dependencies"
**Voiceover:** "Your environment is configured. In the next video, we'll install dependencies and build the project. See you there."
**Action:** Fade to title card.
