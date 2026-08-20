<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(config('app.name')); ?> â€” CRM + ERP + AI Business Operating System</title>
    <meta name="description" content="VentureX ERP & CRM is a universal CRM + ERP platform for sales, procurement, inventory, finance, logistics and AI business intelligence. One system, full audit trail, role-based security, AI insights on demand.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo e(url('/')); ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo e(config('app.name')); ?> â€” CRM + ERP + AI Business Operating System">
    <meta property="og:description" content="One platform for sales, procurement, inventory, finance, logistics and AI business intelligence. Role-based security with a full audit trail.">
    <meta property="og:url" content="<?php echo e(url('/')); ?>">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo e(config('app.name')); ?> â€” CRM + ERP + AI">
    <meta name="twitter:description" content="Universal CRM + ERP + AI Business Operating System.">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "SoftwareApplication",
                "name": "<?php echo e(config('app.name')); ?>",
                "operatingSystem": "Web",
                "applicationCategory": "BusinessApplication",
                "description": "Universal CRM + ERP + AI Business Operating System covering sales, procurement, inventory, finance, logistics and AI business intelligence.",
                "offers": { "@type": "Offer", "price": "0", "priceCurrency": "INR" }
            },
            {
                "@type": "Organization",
                "name": "<?php echo e(config('app.name')); ?>",
                "url": "<?php echo e(url('/')); ?>"
            },
            {
                "@type": "WebSite",
                "name": "<?php echo e(config('app.name')); ?>",
                "url": "<?php echo e(url('/')); ?>"
            },
            {
                "@type": "FAQPage",
                "mainEntity": [
                    {
                        "@type": "Question",
                        "name": "What is VentureX ERP & CRM?",
                        "acceptedAnswer": { "@type": "Answer", "text": "VentureX ERP & CRM is a web-based business operating system that combines CRM, ERP and AI. It manages customers, leads, sales, procurement, inventory, finance and logistics in one secure platform." }
                    },
                    {
                        "@type": "Question",
                        "name": "How does AI work inside VentureX ERP & CRM?",
                        "acceptedAnswer": { "@type": "Answer", "text": "AI runs securely on the backend. You can ask the Business Copilot about your data, generate AI insights, and use one-click AI analysis on customers, suppliers, invoices and inventory. AI only explains and recommends â€” it never changes your ERP data without human approval." }
                    },
                    {
                        "@type": "Question",
                        "name": "Is my business data secure?",
                        "acceptedAnswer": { "@type": "Answer", "text": "Yes. VentureX ERP & CRM uses role-based access control, multi-factor authentication, a full audit trail and server-side AI that never sends API keys or passwords to the browser." }
                    },
                    {
                        "@type": "Question",
                        "name": "Which modules are included?",
                        "acceptedAnswer": { "@type": "Answer", "text": "CRM, sales quotations, sales orders, invoices, payments, suppliers, supplier offers, purchase requisitions, purchase orders, RFQs, inventory, warehouses, stock, logistics, landed costs, finance, documents and the AI Center." }
                    }
                ]
            }
        ]
    }
    </script>
</head>
<body class="h-full bg-ink-100 text-ink-800 antialiased">
    <header class="border-b border-ink-200 bg-white">
        <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6" aria-label="Main">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-accent-600 text-sm font-bold text-white">T</span>
                <span class="text-sm font-bold text-ink-900"><?php echo e(config('app.name')); ?></span>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('login')); ?>" class="btn-secondary">Sign in</a>
            </div>
        </nav>
    </header>

    <main>
        <section class="bg-navy-900 text-white">
            <div class="mx-auto max-w-6xl px-4 py-20 sm:px-6 sm:py-28">
                <p class="text-sm font-semibold uppercase tracking-widest text-accent-400">CRM Â· ERP Â· AI</p>
                <h1 class="mt-4 max-w-3xl text-3xl font-bold leading-tight sm:text-5xl">One business operating system for sales, procurement, inventory and finance.</h1>
                <p class="mt-5 max-w-2xl text-base text-slate-300 sm:text-lg">VentureX ERP & CRM brings customers, suppliers, stock, money and AI intelligence into a single secure platform â€” with role-based access and a full audit trail.</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="<?php echo e(route('login')); ?>" class="btn-accent">Sign in to your workspace</a>
                    <span class="inline-flex items-center gap-2 rounded-lg border border-white/20 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white/80">
                        Demo available upon request
                    </span>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6" aria-label="Capabilities">
            <h2 class="text-center text-2xl font-bold text-ink-900">Everything your business runs on</h2>
            <p class="mx-auto mt-3 max-w-2xl text-center text-sm text-ink-500">Fully functional modules, built on a single platform with one source of truth.</p>
            <div class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <article class="card"><h3 class="text-sm font-bold text-ink-900">CRM</h3><p class="mt-1 text-sm text-ink-500">Customers, contacts, leads, opportunities and an active sales pipeline.</p></article>
                <article class="card"><h3 class="text-sm font-bold text-ink-900">Sales</h3><p class="mt-1 text-sm text-ink-500">Quotations, sales orders, invoices and payments with follow-up tracking.</p></article>
                <article class="card"><h3 class="text-sm font-bold text-ink-900">Procurement</h3><p class="mt-1 text-sm text-ink-500">Suppliers, supplier offers, requisitions, purchase orders and RFQs.</p></article>
                <article class="card"><h3 class="text-sm font-bold text-ink-900">Inventory</h3><p class="mt-1 text-sm text-ink-500">Products, warehouses, stock movements and reorder-level alerts.</p></article>
                <article class="card"><h3 class="text-sm font-bold text-ink-900">Finance</h3><p class="mt-1 text-sm text-ink-500">Chart of accounts, journal entries, receivables and payables.</p></article>
                <article class="card"><h3 class="text-sm font-bold text-ink-900">Logistics</h3><p class="mt-1 text-sm text-ink-500">Shipments, containers and landed-cost tracking.</p></article>
                <article class="card"><h3 class="text-sm font-bold text-ink-900">AI Center</h3><p class="mt-1 text-sm text-ink-500">Business Copilot, AI insights and one-click analysis â€” secure, backend-only.</p></article>
                <article class="card"><h3 class="text-sm font-bold text-ink-900">Security</h3><p class="mt-1 text-sm text-ink-500">Role-based permissions, multi-factor authentication and a full audit trail.</p></article>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-4 pb-16 sm:px-6" aria-label="AI overview">
            <div class="card p-8">
                <h2 class="text-2xl font-bold text-ink-900">AI that explains â€” it never changes your data</h2>
                <p class="mt-3 max-w-3xl text-sm text-ink-600">Ask the Business Copilot about receivables, overdue invoices, stock levels or suppliers. Generate AI business insights, or run one-click AI analysis on any customer, supplier, invoice or product. Every answer is generated on the secure backend, labelled with its facts, and always subject to human review before anything is acted on.</p>
                <div class="mt-6 grid gap-3 text-sm sm:grid-cols-3">
                    <p class="rounded-lg bg-ink-50 p-3 text-ink-700"><strong class="text-ink-900">[FACT]</strong> â€” statements grounded in your data</p>
                    <p class="rounded-lg bg-ink-50 p-3 text-ink-700"><strong class="text-ink-900">[RECOMMENDATION]</strong> â€” suggested next actions</p>
                    <p class="rounded-lg bg-ink-50 p-3 text-ink-700"><strong class="text-ink-900">[ASSUMPTION]</strong> â€” clearly marked estimates</p>
                </div>
            </div>
        </section>

        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-ink-900 text-center mb-12">See It In Action</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="rounded-xl overflow-hidden shadow-lg border border-ink-200">
                        <img src="<?php echo e(asset('screenshots/Product demo/02-dashboard-desktop.png')); ?>" alt="Dashboard" class="w-full h-auto">
                    </div>
                    <div class="rounded-xl overflow-hidden shadow-lg border border-ink-200">
                        <img src="<?php echo e(asset('screenshots/Product demo/03-customers.png')); ?>" alt="CRM Customers" class="w-full h-auto">
                    </div>
                    <div class="rounded-xl overflow-hidden shadow-lg border border-ink-200">
                        <img src="<?php echo e(asset('screenshots/Product demo/05-opportunities.png')); ?>" alt="Sales Pipeline" class="w-full h-auto">
                    </div>
                    <div class="rounded-xl overflow-hidden shadow-lg border border-ink-200">
                        <img src="<?php echo e(asset('screenshots/Product demo/07-purchase-orders.png')); ?>" alt="Purchase Orders" class="w-full h-auto">
                    </div>
                    <div class="rounded-xl overflow-hidden shadow-lg border border-ink-200">
                        <img src="<?php echo e(asset('screenshots/Product demo/13-ai-quotas.png')); ?>" alt="AI Features" class="w-full h-auto">
                    </div>
                    <div class="rounded-xl overflow-hidden shadow-lg border border-ink-200">
                        <img src="<?php echo e(asset('screenshots/Product demo/17-dashboard-mobile.png')); ?>" alt="Mobile View" class="w-full h-auto">
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-4 pb-16 sm:px-6" aria-label="Frequently asked questions">
            <h2 class="text-2xl font-bold text-ink-900">Frequently asked questions</h2>
            <div class="mt-6 space-y-4">
                <details class="card group">
                    <summary class="cursor-pointer text-sm font-semibold text-ink-900">What is VentureX ERP & CRM?</summary>
                    <p class="mt-2 text-sm text-ink-600">VentureX ERP & CRM is a web-based business operating system that combines CRM, ERP and AI. It manages customers, leads, sales, procurement, inventory, finance and logistics in one secure platform.</p>
                </details>
                <details class="card group">
                    <summary class="cursor-pointer text-sm font-semibold text-ink-900">How does AI work inside VentureX ERP & CRM?</summary>
                    <p class="mt-2 text-sm text-ink-600">AI runs securely on the backend. You can ask the Business Copilot about your data, generate AI insights, and use one-click AI analysis on customers, suppliers, invoices and inventory. AI only explains and recommends â€” it never changes your ERP data without human approval.</p>
                </details>
                <details class="card group">
                    <summary class="cursor-pointer text-sm font-semibold text-ink-900">Is my business data secure?</summary>
                    <p class="mt-2 text-sm text-ink-600">Yes. VentureX ERP & CRM uses role-based access control, multi-factor authentication, a full audit trail and server-side AI that never exposes API keys or passwords to the browser.</p>
                </details>
                <details class="card group">
                    <summary class="cursor-pointer text-sm font-semibold text-ink-900">Which modules are included?</summary>
                    <p class="mt-2 text-sm text-ink-600">CRM, sales quotations, sales orders, invoices, payments, suppliers, supplier offers, purchase requisitions, purchase orders, RFQs, inventory, warehouses, stock, logistics, landed costs, finance, documents and the AI Center.</p>
                </details>
            </div>
        </section>
    </main>

    <footer class="border-t border-ink-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-6 text-sm text-ink-400 sm:flex-row sm:px-6">
            <p>Â© <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?> â€” Universal CRM + ERP + AI Business Operating System</p>
            <a href="<?php echo e(route('login')); ?>" class="text-navy-600 hover:text-navy-500">Sign in</a>
        </div>
    </footer>
</body>
</html>
<?php /**PATH C:\MY_ERP\resources\views\landing.blade.php ENDPATH**/ ?>