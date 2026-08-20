<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Frequently Asked Questions','breadcrumbs' => [['label' => 'Support', 'url' => route('support.index')], ['label' => 'FAQ']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Frequently Asked Questions'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Support', 'url' => route('support.index')], ['label' => 'FAQ']])]); ?>

    <div class="mx-auto max-w-3xl space-y-6">

        <div x-data="{ query: '' }" class="card">
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="query" placeholder="Search frequently asked questions..." class="input pl-10">
            </div>
        </div>

        
        <div x-data="{ open: null }" class="card space-y-1">
            <h2 class="mb-3 text-lg font-bold text-ink-900">General</h2>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 1 ? open = null : open = 1" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    What is VentureX ERP & CRM?
                    <svg :class="open === 1 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 1" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    VentureX ERP & CRM is a comprehensive business operating system that combines CRM, sales, procurement, inventory, logistics, finance, and AI capabilities into a single unified platform.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 2 ? open = null : open = 2" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    Who is VentureX ERP & CRM designed for?
                    <svg :class="open === 2 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 2" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    VentureX ERP & CRM is built for businesses of all sizes - from startups to enterprise organizations looking to consolidate multiple business tools into a single platform with integrated AI capabilities.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 3 ? open = null : open = 3" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    Is there a free trial available?
                    <svg :class="open === 3 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 3" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Yes, we offer a 14-day free trial with full access to all modules. No credit card required. You can also self-host the Community Edition which is free and open source.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 4 ? open = null : open = 4" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    Can I migrate data from another ERP system?
                    <svg :class="open === 4 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 4" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Yes! We support data import from CSV and XLSX files. You can import customers, products, suppliers, journal entries, and more. Contact support for migration assistance.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 5 ? open = null : open = 5" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    What kind of support do you offer?
                    <svg :class="open === 5 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 5" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    We offer email support, a comprehensive knowledge base, FAQ section, and in-app AI assistant. Priority support is available for Enterprise plan customers with guaranteed response times.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 6 ? open = null : open = 6" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    Is my data secure?
                    <svg :class="open === 6 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 6" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Absolutely. We use industry-standard encryption, regular backups, role-based access control, and audit logging. Our Security Center provides real-time monitoring and alerts.
                </div>
            </div>
        </div>

        
        <div x-data="{ open: null }" class="card space-y-1">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Installation</h2>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 1 ? open = null : open = 1" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    What are the minimum server requirements?
                    <svg :class="open === 1 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 1" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    You need PHP 8.2+, MySQL 8.0+, Composer 2.x, Node.js 18+, and at least 512 MB of memory. See our Installation Guide for full details.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 2 ? open = null : open = 2" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    Can I install VentureX ERP & CRM on shared hosting?
                    <svg :class="open === 2 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 2" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Shared hosting is not recommended due to SSH access requirements. We recommend a VPS or dedicated server (DigitalOcean, AWS, Linode, etc.) for the best experience.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 3 ? open = null : open = 3" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    How do I update to the latest version?
                    <svg :class="open === 3 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 3" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Pull the latest code, run composer install, npm install, npm run build, then php artisan migrate. Always back up your database before updating.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 4 ? open = null : open = 4" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    Do I need SSH access to install?
                    <svg :class="open === 4 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 4" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Yes, SSH access is required for installation, migrations, queue management, and cron jobs. Most VPS providers include SSH by default.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 5 ? open = null : open = 5" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    Can I run VentureX ERP & CRM with Docker?
                    <svg :class="open === 5 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 5" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Yes, Docker and Docker Compose configurations are included in the repository. Run docker-compose up to get started with all services configured automatically.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 6 ? open = null : open = 6" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    What is the default login after installation?
                    <svg :class="open === 6 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 6" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    After running <code>php artisan migrate --seed</code>, a demo admin account is created. Check the database seeder for the current demo credentials. Always change passwords before production use.
                </div>
            </div>
        </div>

        
        <div x-data="{ open: null }" class="card space-y-1">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Configuration</h2>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 1 ? open = null : open = 1" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    How do I configure email notifications?
                    <svg :class="open === 1 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 1" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Configure MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, and MAIL_PASSWORD in your .env file. We support SMTP, Mailgun, Postmark, and SES out of the box.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 2 ? open = null : open = 2" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    How do I set up multi-company support?
                    <svg :class="open === 2 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 2" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Navigate to Administration > Companies and create each company with its own settings, currency, and tax rules. Users can be assigned to one or more companies.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 3 ? open = null : open = 3" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    How do I configure AI features?
                    <svg :class="open === 3 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 3" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Add your AI provider API keys (OpenAI, Anthropic, etc.) in the .env file under AI_* settings. Then configure AI quotas in Administration > AI Quotas.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 4 ? open = null : open = 4" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    Can I customize the sidebar navigation?
                    <svg :class="open === 4 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 4" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    The sidebar is role-based and automatically shows/hides modules based on user permissions. Control access through Administration > Roles &amp; Permissions.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 5 ? open = null : open = 5" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    How do I set up two-factor authentication?
                    <svg :class="open === 5 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 5" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Users can enable 2FA from their profile settings. We support TOTP authenticator apps (Google Authenticator, Authy). Admins can enforce 2FA via Administration Settings.
                </div>
            </div>
        </div>

        
        <div x-data="{ open: null }" class="card space-y-1">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Billing</h2>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 1 ? open = null : open = 1" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    What pricing plans are available?
                    <svg :class="open === 1 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 1" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    We offer three plans: Starter (up to 5 users), Professional (up to 25 users), and Enterprise (unlimited users with priority support). Self-hosted Community Edition is free.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 2 ? open = null : open = 2" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    Can I change my plan at any time?
                    <svg :class="open === 2 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 2" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Yes, you can upgrade or downgrade your plan at any time. Upgrades take effect immediately with prorated billing. Downgrades take effect at the start of your next billing cycle.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 3 ? open = null : open = 3" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    What payment methods do you accept?
                    <svg :class="open === 3 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 3" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    We accept all major credit cards, wire transfers, and PayPal. Enterprise customers can also pay via invoice with NET-30 terms.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 4 ? open = null : open = 4" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    Is there a refund policy?
                    <svg :class="open === 4 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 4" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Yes, we offer a 30-day money-back guarantee on all plans. If you are not satisfied, contact our billing team for a full refund.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 5 ? open = null : open = 5" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    Are there discounts for annual billing?
                    <svg :class="open === 5 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 5" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Yes, annual billing saves you 20% compared to monthly billing. Contact our sales team for custom pricing on Enterprise plans with 50+ users.
                </div>
            </div>
        </div>

        
        <div x-data="{ open: null }" class="card space-y-1">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Troubleshooting</h2>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 1 ? open = null : open = 1" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    The application is showing a blank white screen
                    <svg :class="open === 1 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 1" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Enable debug mode by setting APP_DEBUG=true in your .env file. This will show the actual error. Common causes include missing PHP extensions or incorrect file permissions.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 2 ? open = null : open = 2" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    I cannot log in after installation
                    <svg :class="open === 2 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 2" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                     Ensure you ran <code>php artisan migrate --seed</code> to create the demo admin user. Check the database seeder for current credentials. Never use default passwords in production.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 3 ? open = null : open = 3" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    AI features are not responding
                    <svg :class="open === 3 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 3" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Verify your AI provider API keys in the .env file. Check AI Usage in the AI Center to see if your quota has been exceeded.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 4 ? open = null : open = 4" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    Emails are not being sent
                    <svg :class="open === 4 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 4" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Check your MAIL_* configuration in .env. If using queued mail, ensure the queue worker is running with php artisan queue:work.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 5 ? open = null : open = 5" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    I forgot my password
                    <svg :class="open === 5 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 5" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Click "Forgot Password?" on the login page to receive a reset link via email. If email is not configured, a super admin can reset your password from Administration > Users.
                </div>
            </div>

            <div class="rounded-lg border border-ink-200">
                <button @click="open === 6 ? open = null : open = 6" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-medium text-ink-800 hover:bg-ink-50">
                    How do I clear the cache?
                    <svg :class="open === 6 ? 'rotate-90' : ''" class="h-4 w-4 shrink-0 text-ink-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open === 6" x-collapse class="px-4 pb-3 text-sm text-ink-600">
                    Run: php artisan cache:clear &amp;&amp; php artisan config:clear &amp;&amp; php artisan route:clear &amp;&amp; php artisan view:clear. This resolves most issues after configuration changes.
                </div>
            </div>
        </div>

        
        <div class="card text-center">
            <h2 class="text-lg font-bold text-ink-900">Still have questions?</h2>
            <p class="mt-1 text-sm text-ink-500">Our support team is here to help.</p>
            <div class="mt-4 flex items-center justify-center gap-3">
                <a href="<?php echo e(route('support.contact')); ?>" class="btn-accent">Contact Support</a>
                <a href="<?php echo e(route('support.tickets.create')); ?>" class="btn-secondary">Submit a Ticket</a>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH C:\MY_ERP\resources\views\support\faq.blade.php ENDPATH**/ ?>