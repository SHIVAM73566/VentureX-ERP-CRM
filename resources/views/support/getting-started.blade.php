<x-layouts.app
    :title="'Getting Started'"
    :breadcrumbs="[['label' => 'Getting Started']]">

    @php
        $canUseAi = auth()->user()?->can('viewAny', App\Models\AiRun::class) ?? false;
        $isOwner = auth()->user()?->hasRole('super_admin') ?? false;
    @endphp

    <div class="mx-auto max-w-6xl space-y-6" x-data="{
        done: JSON.parse(localStorage.getItem('vx-getting-started') || '{}'),
        toggle(key) {
            this.done[key] = ! this.done[key];
            localStorage.setItem('vx-getting-started', JSON.stringify(this.done));
        },
        get progress() {
            const total = 10;
            const count = Object.values(this.done).filter(Boolean).length;
            return Math.min(100, Math.round((count / total) * 100));
        },
        reset() {
            this.done = {};
            localStorage.removeItem('vx-getting-started');
        }
    }">

        {{-- Hero --}}
        <section class="overflow-hidden rounded-2xl bg-gradient-to-br from-navy-700 via-navy-800 to-navy-900 px-6 py-10 text-white sm:px-10">
            <div class="max-w-3xl">
                <span class="badge bg-white/10 text-white">Product tour · 10 minutes</span>
                <h1 class="mt-4 text-3xl font-bold sm:text-4xl">Welcome to VentureX ERP &amp; CRM</h1>
                <p class="mt-3 text-base text-navy-100 sm:text-lg">
                    One workspace for your customers, sales, stock, purchasing, finance, logistics and AI.
                    This page shows you exactly what to click first, with real examples you can copy into your own data.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('dashboard') }}" class="btn bg-white text-navy-800 hover:bg-navy-50">Open Dashboard</a>
                    <a href="{{ route('ai.assistant') }}" class="btn border border-white/30 text-white hover:bg-white/10">Try the AI Assistant</a>
                    <a href="#quick-start" class="btn border border-white/30 text-white hover:bg-white/10">Jump to quick start</a>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-xl bg-white/10 px-4 py-3">
                    <p class="text-2xl font-bold">8</p>
                    <p class="text-xs text-navy-100">Connected modules</p>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-3">
                    <p class="text-2xl font-bold">84</p>
                    <p class="text-xs text-navy-100">AI RAG pipelines</p>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-3">
                    <p class="text-2xl font-bold">1</p>
                    <p class="text-xs text-navy-100">Login for everything</p>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-3">
                    <p class="text-2xl font-bold">0</p>
                    <p class="text-xs text-navy-100">External vector DBs</p>
                </div>
            </div>
        </section>

        {{-- What this software is --}}
        <section class="card p-6">
            <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">What VentureX does for your company</h2>
            <p class="mt-2 text-sm text-ink-600 dark:text-ink-300">
                VentureX connects the departments that usually live in separate tools. A lead becomes a customer,
                a customer becomes a quotation, a quotation becomes an order, an order moves stock, an invoice becomes
                a payment, and every step is visible to finance and management — without re-typing data.
            </p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-ink-200 p-4 dark:border-ink-700">
                    <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">Sell</p>
                    <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">Leads → pipeline → quotation → order → invoice → payment.</p>
                </div>
                <div class="rounded-xl border border-ink-200 p-4 dark:border-ink-700">
                    <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">Buy</p>
                    <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">Supplier offer → requisition → purchase order → goods receipt.</p>
                </div>
                <div class="rounded-xl border border-ink-200 p-4 dark:border-ink-700">
                    <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">Move</p>
                    <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">Warehouses, stock levels, shipments, containers and landed cost.</p>
                </div>
                <div class="rounded-xl border border-ink-200 p-4 dark:border-ink-700">
                    <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">Understand</p>
                    <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">Finance dashboards, audit logs and AI answers with sources.</p>
                </div>
            </div>
        </section>

        {{-- Quick start --}}
        <section id="quick-start" class="space-y-4">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Your first 7 steps</h2>
                    <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Follow these in order. Each step takes 1–3 minutes.</p>
                </div>
                <span class="badge badge-blue">Beginner friendly</span>
            </div>

            @php
                $steps = [
                    [
                        'n' => '1',
                        'title' => 'Set up your company',
                        'what' => 'Add your legal name, currency, timezone and invoice prefix.',
                        'example' => 'Acme Trading LLC · INR · Asia/Kolkata · invoice prefix INV',
                        'route' => $isOwner ? 'admin.settings.index' : null,
                        'link' => 'Open Settings',
                    ],
                    [
                        'n' => '2',
                        'title' => 'Add one customer and one supplier',
                        'what' => 'Use the same records everywhere instead of spreadsheets.',
                        'example' => 'Customer: Bluewave Retail · Supplier: Shenzhen Components Ltd',
                        'route' => 'customers.index',
                        'link' => 'Open Customers',
                    ],
                    [
                        'n' => '3',
                        'title' => 'Add a product and opening stock',
                        'what' => 'Set SKU, selling price, reorder level and warehouse quantity.',
                        'example' => 'VC-1001 Bluetooth Headset · ₹2,499 · reorder at 20 · 120 units',
                        'route' => 'inventory.products.index',
                        'link' => 'Open Products',
                    ],
                    [
                        'n' => '4',
                        'title' => 'Create a quotation and convert it',
                        'what' => 'Send a quotation, then convert the won deal into a sales order.',
                        'example' => '10 × VC-1001 @ ₹2,499 = ₹24,990 · converted to SO-2026-0087',
                        'route' => 'sales.quotations.index',
                        'link' => 'Open Quotations',
                    ],
                    [
                        'n' => '5',
                        'title' => 'Raise an invoice and record payment',
                        'what' => 'Invoice the order, then record full or partial payment.',
                        'example' => 'INV-2026-0042 · ₹24,990 · paid by UPI · balance ₹0',
                        'route' => 'sales.invoices.index',
                        'link' => 'Open Invoices',
                    ],
                    [
                        'n' => '6',
                        'title' => 'Buy stock end-to-end',
                        'what' => 'Raise a requisition, approve a purchase order, receive goods.',
                        'example' => 'PR-2026-0031 → PO-2026-0114 · 200 units · landed cost ₹1,890/unit',
                        'route' => 'procurement.requisitions.index',
                        'link' => 'Open Requisitions',
                    ],
                    [
                        'n' => '7',
                        'title' => 'Ask AI about your own data',
                        'what' => 'Use the assistant or Enterprise RAG to query live records.',
                        'example' => '“Which customers have overdue invoices this month?”',
                        'route' => 'ai.assistant',
                        'link' => 'Open AI Assistant',
                    ],
                ];
            @endphp

            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ($steps as $step)
                    <div class="card p-5">
                        <div class="flex items-start gap-4">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-navy-800 text-sm font-bold text-white">{{ $step['n'] }}</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <h3 class="text-sm font-bold text-ink-900 dark:text-ink-50">{{ $step['title'] }}</h3>
                                    @if ($step['route'])
                                        <a href="{{ route($step['route']) }}" class="text-xs font-semibold text-navy-600 hover:underline dark:text-navy-300">{{ $step['link'] }} →</a>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-ink-600 dark:text-ink-300">{{ $step['what'] }}</p>
                                <p class="mt-2 rounded-lg bg-ink-50 px-3 py-2 text-xs text-ink-600 dark:bg-ink-800 dark:text-ink-300">
                                    <span class="font-semibold text-ink-700 dark:text-ink-200">Example:</span> {{ $step['example'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- AI prompts --}}
        <section class="card p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Try these AI questions</h2>
                    <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Copy a question, paste it into the assistant, and get an answer with sources.</p>
                </div>
                @if ($canUseAi)
                    <a href="{{ route('ai.rag') }}" class="btn-secondary">Open Enterprise RAG</a>
                    <a href="{{ route('ai.rag.tour') }}" class="btn-secondary">Explore all 84 pipelines</a>
                @endif
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @php
                    $prompts = [
                        'Which customers have overdue invoices this month?',
                        'Show products below reorder level in each warehouse.',
                        'Summarise procurement spend by supplier for this quarter.',
                        'Which leads have had no activity in the last 14 days?',
                        'What is the landed cost and margin of container CONT-2026-014?',
                        'Compare quotation Q-1042 with this customer\'s last order.',
                        'Which sales orders are pending shipment for more than 5 days?',
                        'Explain the gap between finance revenue and invoiced sales.',
                    ];
                @endphp
                @foreach ($prompts as $prompt)
                    <div class="rounded-xl border border-ink-200 p-3 dark:border-ink-700" x-data="{ copied: false }">
                        <div class="flex items-start justify-between gap-3">
                            <code class="text-xs leading-relaxed text-ink-700 dark:text-ink-200">{{ $prompt }}</code>
                            <button type="button"
                                class="shrink-0 rounded-md border border-ink-200 px-2 py-1 text-[11px] font-semibold text-ink-600 hover:bg-ink-50 dark:border-ink-700 dark:text-ink-300 dark:hover:bg-ink-800"
                                @click="navigator.clipboard.writeText(@js($prompt)); copied = true; setTimeout(() => copied = false, 1500)">
                                <span x-show="!copied">Copy</span>
                                <span x-show="copied" x-cloak>Copied</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Module map --}}
        <section class="card">
            <div class="card-header">
                <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">What each module replaces</h2>
                <span class="badge badge-gray">Module map</span>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>What you do there</th>
                            <th>Usually replaces</th>
                            <th class="text-right">Open</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @php
                            $modules = [
                                ['CRM', 'Track customers, contacts, leads, activities and the sales pipeline.', 'Spreadsheets + WhatsApp follow-ups', 'pipeline.index', 'Pipeline'],
                                ['Sales', 'Quotations, sales orders, invoices and payments.', 'Word quotes + invoice templates', 'sales.orders.index', 'Sales Orders'],
                                ['Procurement', 'Supplier offers, requisitions, RFQs and purchase orders.', 'Email threads + purchase registers', 'procurement.orders.index', 'Purchase Orders'],
                                ['Inventory', 'Products, warehouses, stock levels and reorder alerts.', 'Stock registers + physical counts', 'inventory.stock.index', 'Stock'],
                                ['Logistics', 'Shipments, containers and landed cost allocation.', 'Freight forwarder spreadsheets', 'logistics.landed-costs.index', 'Landed Costs'],
                                ['Finance', 'Chart of accounts, journals, receivables and payables.', 'Standalone accounting tools', 'finance.dashboard', 'Finance'],
                                ['AI Center', 'Assistant, copilot, document reader and 84+ RAG pipelines.', 'Manual reporting and searching', 'ai.assistant', 'AI Assistant'],
                                ['Support & Admin', 'Help center, tickets, users, roles, audit logs and exports.', 'Shared inboxes + ad-hoc admin', 'support.index', 'Help Center'],
                            ];
                        @endphp
                        @foreach ($modules as [$name, $what, $replaces, $route, $label])
                            <tr>
                                <td class="font-semibold text-ink-800 dark:text-ink-100">{{ $name }}</td>
                                <td class="text-ink-600 dark:text-ink-300">{{ $what }}</td>
                                <td class="text-ink-500 dark:text-ink-400">{{ $replaces }}</td>
                                <td class="text-right">
                                    <a href="{{ route($route) }}" class="text-xs font-semibold text-navy-600 hover:underline dark:text-navy-300">{{ $label }} →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Company scenarios --}}
        <section class="space-y-4">
            <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">How companies actually use it</h2>
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="card p-5">
                    <span class="badge badge-blue">Wholesale distributor</span>
                    <h3 class="mt-3 text-sm font-bold text-ink-900 dark:text-ink-50">Quote → order → stock → invoice</h3>
                    <ol class="mt-2 list-decimal space-y-1 pl-4 text-xs text-ink-600 dark:text-ink-300">
                        <li>Sales creates a quotation for 10 units.</li>
                        <li>Deal is won and converted to a sales order.</li>
                        <li>Stock is reserved and picked from the warehouse.</li>
                        <li>Invoice is raised and payment is recorded.</li>
                        <li>Finance sees the receivable clear automatically.</li>
                    </ol>
                </div>
                <div class="card p-5">
                    <span class="badge badge-green">Services agency</span>
                    <h3 class="mt-3 text-sm font-bold text-ink-900 dark:text-ink-50">Lead → pipeline → invoice</h3>
                    <ol class="mt-2 list-decimal space-y-1 pl-4 text-xs text-ink-600 dark:text-ink-300">
                        <li>Lead arrives from the website or a referral.</li>
                        <li>Owner moves it through the pipeline stages.</li>
                        <li>Quotation is sent with a clear scope.</li>
                        <li>Project is invoiced in milestones.</li>
                        <li>Receivables dashboard shows what is unpaid.</li>
                    </ol>
                </div>
                <div class="card p-5">
                    <span class="badge badge-amber">Import / export trader</span>
                    <h3 class="mt-3 text-sm font-bold text-ink-900 dark:text-ink-50">Supplier → container → margin</h3>
                    <ol class="mt-2 list-decimal space-y-1 pl-4 text-xs text-ink-600 dark:text-ink-300">
                        <li>Compare supplier offers side by side.</li>
                        <li>Raise a purchase order and track the shipment.</li>
                        <li>Allocate freight and duty as landed cost.</li>
                        <li>See true cost per unit before selling.</li>
                        <li>Ask AI which SKUs have the thinnest margin.</li>
                    </ol>
                </div>
            </div>
        </section>

        {{-- Roles --}}
        <section class="card p-6">
            <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">What to do first, by role</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @php
                    $roles = [
                        ['Owner / Admin', 'Set company details, invite users, assign roles, review audit logs.', 'admin.users.index', 'Users'],
                        ['Sales', 'Add leads, work the pipeline, send quotations and follow up.', 'leads.index', 'Leads'],
                        ['Procurement', 'Compare supplier offers, raise requisitions and purchase orders.', 'supplier-offers.index', 'Supplier Offers'],
                        ['Warehouse', 'Maintain products, warehouses, stock and reorder levels.', 'inventory.stock.index', 'Stock'],
                        ['Finance', 'Check journals, receivables and payables every morning.', 'finance.receivables', 'Receivables'],
                        ['Support', 'Answer tickets and keep the help center current.', 'support.tickets.index', 'Tickets'],
                    ];
                @endphp
                @foreach ($roles as [$role, $task, $route, $label])
                    <div class="rounded-xl border border-ink-200 p-4 dark:border-ink-700">
                        <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">{{ $role }}</p>
                        <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">{{ $task }}</p>
                        <a href="{{ route($route) }}" class="mt-2 inline-block text-xs font-semibold text-navy-600 hover:underline dark:text-navy-300">{{ $label }} →</a>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Checklist --}}
        <section class="card p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Your onboarding checklist</h2>
                    <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Saved in this browser. Tick items as your team completes them.</p>
                </div>
                <button type="button" class="btn-secondary" @click="reset()">Reset</button>
            </div>

            <div class="mt-4">
                <div class="flex items-center justify-between text-xs font-semibold text-ink-600 dark:text-ink-300">
                    <span>Progress</span>
                    <span x-text="progress + '%'"></span>
                </div>
                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-ink-100 dark:bg-ink-800">
                    <div class="h-full rounded-full bg-accent-500 transition-all" :style="'width: ' + progress + '%'"></div>
                </div>
            </div>

            <div class="mt-5 grid gap-2 sm:grid-cols-2">
                @php
                    $checks = [
                        'company' => 'Company profile, currency and invoice prefix configured',
                        'users' => 'Team invited and roles assigned',
                        'customers' => 'First 10 customers imported or added',
                        'suppliers' => 'Key suppliers added',
                        'products' => 'Products and opening stock entered',
                        'quotation' => 'First quotation sent',
                        'order' => 'First sales order created',
                        'invoice' => 'First invoice raised and paid',
                        'purchase' => 'First purchase order approved',
                        'ai' => 'First AI question answered from live data',
                    ];
                @endphp
                @foreach ($checks as $key => $label)
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-ink-200 px-3 py-2.5 text-sm text-ink-700 hover:bg-ink-50 dark:border-ink-700 dark:text-ink-200 dark:hover:bg-ink-800">
                        <input type="checkbox" class="mt-0.5 h-4 w-4 rounded border-ink-300 text-accent-600 focus:ring-accent-500"
                            x-model="done.{{ $key }}" @change="toggle('{{ $key }}')">
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </section>

        {{-- Help --}}
        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('support.docs') }}" class="card flex flex-col items-center gap-2 p-5 text-center transition hover:shadow-md">
                <p class="font-semibold text-ink-800 dark:text-ink-100">Documentation</p>
                <p class="text-xs text-ink-500 dark:text-ink-400">Technical guides and API</p>
            </a>
            <a href="{{ route('support.faq') }}" class="card flex flex-col items-center gap-2 p-5 text-center transition hover:shadow-md">
                <p class="font-semibold text-ink-800 dark:text-ink-100">FAQ</p>
                <p class="text-xs text-ink-500 dark:text-ink-400">Common questions answered</p>
            </a>
            <a href="{{ route('support.tickets.create') }}" class="card flex flex-col items-center gap-2 p-5 text-center transition hover:shadow-md">
                <p class="font-semibold text-ink-800 dark:text-ink-100">Submit a ticket</p>
                <p class="text-xs text-ink-500 dark:text-ink-400">Get help from the team</p>
            </a>
            <a href="{{ route('support.installation') }}" class="card flex flex-col items-center gap-2 p-5 text-center transition hover:shadow-md">
                <p class="font-semibold text-ink-800 dark:text-ink-100">Installation</p>
                <p class="text-xs text-ink-500 dark:text-ink-400">Setup and deployment steps</p>
            </a>
        </section>

        <p class="pb-2 text-center text-xs text-ink-400 dark:text-ink-500">
            Tip: press <kbd class="rounded border border-ink-300 px-1.5 py-0.5 text-[10px] dark:border-ink-600">/</kbd> in the top search bar to jump to any customer, supplier, lead or offer.
        </p>
    </div>
</x-layouts.app>
