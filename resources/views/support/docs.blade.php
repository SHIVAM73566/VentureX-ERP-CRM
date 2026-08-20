<x-layouts.app
    :title="'Documentation'"
    :breadcrumbs="[['label' => 'Support', 'url' => route('support.index')], ['label' => 'Documentation']]">

    <div class="mx-auto max-w-6xl" x-data="{ activeSection: 'getting-started' }">

        <div class="grid gap-6 lg:grid-cols-[240px_1fr]">

            {{-- Sidebar Navigation --}}
            <aside class="hidden lg:block">
                <nav class="sticky top-20 space-y-1">
                    <button @click="activeSection = 'getting-started'" :class="activeSection === 'getting-started' ? 'bg-navy-50 font-semibold text-navy-700' : 'text-ink-600 hover:bg-ink-50'" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm transition">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Getting Started
                    </button>
                    <button @click="activeSection = 'installation'" :class="activeSection === 'installation' ? 'bg-navy-50 font-semibold text-navy-700' : 'text-ink-600 hover:bg-ink-50'" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm transition">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Installation
                    </button>
                    <button @click="activeSection = 'configuration'" :class="activeSection === 'configuration' ? 'bg-navy-50 font-semibold text-navy-700' : 'text-ink-600 hover:bg-ink-50'" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm transition">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                        Configuration
                    </button>
                    <button @click="activeSection = 'modules'" :class="activeSection === 'modules' ? 'bg-navy-50 font-semibold text-navy-700' : 'text-ink-600 hover:bg-ink-50'" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm transition">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Modules
                    </button>
                    <button @click="activeSection = 'troubleshooting'" :class="activeSection === 'troubleshooting' ? 'bg-navy-50 font-semibold text-navy-700' : 'text-ink-600 hover:bg-ink-50'" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm transition">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        Troubleshooting
                    </button>
                    <button @click="activeSection = 'api'" :class="activeSection === 'api' ? 'bg-navy-50 font-semibold text-navy-700' : 'text-ink-600 hover:bg-ink-50'" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm transition">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        API Reference
                    </button>
                </nav>
            </aside>

            {{-- Mobile Nav --}}
            <div class="lg:hidden">
                <select x-model="activeSection" class="input">
                    <option value="getting-started">Getting Started</option>
                    <option value="installation">Installation</option>
                    <option value="configuration">Configuration</option>
                    <option value="modules">Modules</option>
                    <option value="troubleshooting">Troubleshooting</option>
                    <option value="api">API Reference</option>
                </select>
            </div>

            {{-- Main Content --}}
            <div class="space-y-6">

                {{-- Getting Started --}}
                <div x-show="activeSection === 'getting-started'" x-transition class="card space-y-4">
                    <h2 class="text-xl font-bold text-ink-900">Getting Started with VentureX ERP & CRM</h2>
                    <p class="text-sm text-ink-600">Welcome to VentureX ERP & CRM â€” a comprehensive business operating system that combines CRM, ERP, and AI capabilities into a single platform.</p>

                    <h3 class="text-base font-bold text-ink-800">What is VentureX ERP & CRM?</h3>
                    <p class="text-sm text-ink-600">VentureX ERP & CRM is a unified business management platform designed for small to enterprise businesses. It integrates customer relationship management, sales, procurement, inventory, logistics, finance, and artificial intelligence into one cohesive system.</p>

                    <h3 class="text-base font-bold text-ink-800">Core Modules</h3>
                    <ul class="list-disc space-y-1 pl-5 text-sm text-ink-600">
                        <li><strong>CRM</strong> â€” Manage customers, contacts, leads, opportunities, and activities</li>
                        <li><strong>Sales</strong> â€” Handle quotations, orders, invoices, and payments</li>
                        <li><strong>Procurement</strong> â€” Manage suppliers, purchase requisitions, orders, and RFQs</li>
                        <li><strong>Inventory</strong> â€” Track products, warehouses, and stock levels</li>
                        <li><strong>Logistics</strong> â€” Coordinate shipments, containers, and landed costs</li>
                        <li><strong>Finance</strong> â€” Monitor chart of accounts, journals, receivables, and payables</li>
                        <li><strong>AI Center</strong> â€” Leverage AI assistants, copilots, and document readers</li>
                    </ul>

                    <h3 class="text-base font-bold text-ink-800">Quick Links</h3>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('support.installation') }}" class="rounded-lg bg-ink-100 px-3 py-1.5 text-xs font-medium text-ink-700 hover:bg-ink-200">Installation Guide</a>
                        <a href="{{ route('support.faq') }}" class="rounded-lg bg-ink-100 px-3 py-1.5 text-xs font-medium text-ink-700 hover:bg-ink-200">FAQ</a>
                        <a href="{{ route('support.contact') }}" class="rounded-lg bg-ink-100 px-3 py-1.5 text-xs font-medium text-ink-700 hover:bg-ink-200">Contact Support</a>
                    </div>
                </div>

                {{-- Installation --}}
                <div x-show="activeSection === 'installation'" x-transition class="card space-y-4">
                    <h2 class="text-xl font-bold text-ink-900">Installation</h2>
                    <p class="text-sm text-ink-600">Follow these steps to install VentureX ERP & CRM on your server.</p>

                    <h3 class="text-base font-bold text-ink-800">Server Requirements</h3>
                    <ul class="list-disc space-y-1 pl-5 text-sm text-ink-600">
                        <li>PHP 8.2 or higher</li>
                        <li>MySQL 8.0+ or MariaDB 10.6+</li>
                        <li>Composer 2.x</li>
                        <li>Node.js 18+ and npm</li>
                        <li>Laravel 13.x</li>
                    </ul>

                    <h3 class="text-base font-bold text-ink-800">Installation Steps</h3>
                    <div class="space-y-3">
                        <div class="flex gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-navy-100 text-xs font-bold text-navy-700">1</span>
                            <div>
                                <p class="text-sm font-semibold text-ink-800">Clone the repository</p>
                                <code class="mt-1 block rounded-lg bg-ink-900 px-4 py-2 text-xs text-emerald-400">git clone https://github.com/VentureX-ERP/VentureX-ERP.git</code>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-navy-100 text-xs font-bold text-navy-700">2</span>
                            <div>
                                <p class="text-sm font-semibold text-ink-800">Install PHP dependencies</p>
                                <code class="mt-1 block rounded-lg bg-ink-900 px-4 py-2 text-xs text-emerald-400">composer install</code>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-navy-100 text-xs font-bold text-navy-700">3</span>
                            <div>
                                <p class="text-sm font-semibold text-ink-800">Install front-end dependencies</p>
                                <code class="mt-1 block rounded-lg bg-ink-900 px-4 py-2 text-xs text-emerald-400">npm install && npm run build</code>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-navy-100 text-xs font-bold text-navy-700">4</span>
                            <div>
                                <p class="text-sm font-semibold text-ink-800">Configure environment</p>
                                <code class="mt-1 block rounded-lg bg-ink-900 px-4 py-2 text-xs text-emerald-400">cp .env.example .env && php artisan key:generate</code>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-navy-100 text-xs font-bold text-navy-700">5</span>
                            <div>
                                <p class="text-sm font-semibold text-ink-800">Run database migrations</p>
                                <code class="mt-1 block rounded-lg bg-ink-900 px-4 py-2 text-xs text-emerald-400">php artisan migrate --seed</code>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-navy-100 text-xs font-bold text-navy-700">6</span>
                            <div>
                                <p class="text-sm font-semibold text-ink-800">Start the development server</p>
                                <code class="mt-1 block rounded-lg bg-ink-900 px-4 py-2 text-xs text-emerald-400">php artisan serve</code>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Configuration --}}
                <div x-show="activeSection === 'configuration'" x-transition class="card space-y-4">
                    <h2 class="text-xl font-bold text-ink-900">Configuration</h2>
                    <p class="text-sm text-ink-600">After installation, configure VentureX ERP & CRM to match your business needs.</p>

                    <h3 class="text-base font-bold text-ink-800">Environment Variables</h3>
                    <p class="text-sm text-ink-600">Key settings in your <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">.env</code> file:</p>
                    <ul class="list-disc space-y-1 pl-5 text-sm text-ink-600">
                        <li><code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">APP_NAME</code> â€” Your company name (shown in the UI)</li>
                        <li><code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">APP_URL</code> â€” Your application URL</li>
                        <li><code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">DB_*</code> â€” Database connection settings</li>
                        <li><code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">MAIL_*</code> â€” Email configuration for notifications</li>
                        <li><code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">AI_* </code> â€” AI provider API keys (OpenAI, Anthropic, etc.)</li>
                    </ul>

                    <h3 class="text-base font-bold text-ink-800">Company Setup</h3>
                    <p class="text-sm text-ink-600">Navigate to <strong>Administration â†’ Companies</strong> to configure your company profile, tax settings, currency, and branding.</p>

                    <h3 class="text-base font-bold text-ink-800">User Roles & Permissions</h3>
                    <p class="text-sm text-ink-600">Go to <strong>Administration â†’ Roles & Permissions</strong> to define roles and assign granular permissions to each module.</p>

                    <h3 class="text-base font-bold text-ink-800">Notification Settings</h3>
                    <p class="text-sm text-ink-600">Configure email and in-app notifications under <strong>Administration â†’ Settings</strong> to control what alerts are sent and to whom.</p>
                </div>

                {{-- Modules --}}
                <div x-show="activeSection === 'modules'" x-transition class="card space-y-4">
                    <h2 class="text-xl font-bold text-ink-900">Modules</h2>
                    <p class="text-sm text-ink-600">Each module can be enabled or disabled based on your business needs.</p>

                    <div class="divide-y divide-ink-100">
                        <div class="py-3">
                            <h3 class="text-sm font-bold text-ink-800">CRM Module</h3>
                            <p class="mt-1 text-sm text-ink-600">Manage your entire customer lifecycle â€” from initial lead capture through opportunity tracking to closed deals. Includes contact management, activity logging, and a visual sales pipeline.</p>
                        </div>
                        <div class="py-3">
                            <h3 class="text-sm font-bold text-ink-800">Sales Module</h3>
                            <p class="mt-1 text-sm text-ink-600">Create professional quotations, convert them to sales orders, generate invoices, and track payments. Supports multi-currency and tax configurations.</p>
                        </div>
                        <div class="py-3">
                            <h3 class="text-sm font-bold text-ink-800">Procurement Module</h3>
                            <p class="mt-1 text-sm text-ink-600">Streamline your purchasing workflow â€” from supplier management and RFQs to purchase requisitions and orders. Includes AI-powered procurement recommendations.</p>
                        </div>
                        <div class="py-3">
                            <h3 class="text-sm font-bold text-ink-800">Inventory Module</h3>
                            <p class="mt-1 text-sm text-ink-600">Track products, manage multiple warehouses, monitor stock levels, and set reorder points. Real-time inventory visibility across all locations.</p>
                        </div>
                        <div class="py-3">
                            <h3 class="text-sm font-bold text-ink-800">Logistics Module</h3>
                            <p class="mt-1 text-sm text-ink-600">Manage shipments, track containers, and calculate landed costs. Integrates with inventory for end-to-end supply chain visibility.</p>
                        </div>
                        <div class="py-3">
                            <h3 class="text-sm font-bold text-ink-800">Finance Module</h3>
                            <p class="mt-1 text-sm text-ink-600">Full double-entry bookkeeping with chart of accounts, journal entries, accounts receivable/payable, and financial dashboards.</p>
                        </div>
                        <div class="py-3">
                            <h3 class="text-sm font-bold text-ink-800">AI Center</h3>
                            <p class="mt-1 text-sm text-ink-600">Leverage artificial intelligence for document reading, email drafting, business copilot assistance, and procurement optimization.</p>
                        </div>
                    </div>
                </div>

                {{-- Troubleshooting --}}
                <div x-show="activeSection === 'troubleshooting'" x-transition class="card space-y-4">
                    <h2 class="text-xl font-bold text-ink-900">Troubleshooting</h2>
                    <p class="text-sm text-ink-600">Common issues and their solutions.</p>

                    <div class="space-y-4">
                        <div class="rounded-lg border border-ink-200 p-4">
                            <h3 class="text-sm font-bold text-ink-800">Application won't start after installation</h3>
                            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-ink-600">
                                <li>Verify PHP version: <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">php -v</code> (must be 8.2+)</li>
                                <li>Run <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">php artisan key:generate</code> if APP_KEY is missing</li>
                                <li>Check database connection in <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">.env</code></li>
                                <li>Ensure storage directories are writable: <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">chmod -R 775 storage bootstrap/cache</code></li>
                            </ul>
                        </div>
                        <div class="rounded-lg border border-ink-200 p-4">
                            <h3 class="text-sm font-bold text-ink-800">CSS/JS assets not loading</h3>
                            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-ink-600">
                                <li>Run <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">npm install && npm run build</code></li>
                                <li>Clear view cache: <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">php artisan view:clear</code></li>
                            </ul>
                        </div>
                        <div class="rounded-lg border border-ink-200 p-4">
                            <h3 class="text-sm font-bold text-ink-800">Email notifications not sending</h3>
                            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-ink-600">
                                <li>Verify MAIL_* settings in <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">.env</code></li>
                                <li>Test with <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">php artisan tinker</code> and send a test mail</li>
                                <li>Check queue worker is running if using queued mail</li>
                            </ul>
                        </div>
                        <div class="rounded-lg border border-ink-200 p-4">
                            <h3 class="text-sm font-bold text-ink-800">AI features not working</h3>
                            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-ink-600">
                                <li>Ensure AI API keys are configured in <code class="rounded bg-ink-100 px-1.5 py-0.5 text-xs">.env</code></li>
                                <li>Check API quotas with your AI provider</li>
                                <li>Review AI usage logs in <strong>AI Center â†’ AI Usage</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- API Reference --}}
                <div x-show="activeSection === 'api'" x-transition class="card space-y-4">
                    <h2 class="text-xl font-bold text-ink-900">API Reference</h2>
                    <p class="text-sm text-ink-600">VentureX ERP & CRM exposes a RESTful API for integration with external systems.</p>

                    <h3 class="text-base font-bold text-ink-800">Authentication</h3>
                    <p class="text-sm text-ink-600">All API requests require a Bearer token. Generate tokens from <strong>Administration â†’ Settings â†’ API Tokens</strong>.</p>
                    <code class="block rounded-lg bg-ink-900 px-4 py-2 text-xs text-emerald-400">Authorization: Bearer your-api-token-here</code>

                    <h3 class="text-base font-bold text-ink-800">Base URL</h3>
                    <code class="block rounded-lg bg-ink-900 px-4 py-2 text-xs text-emerald-400">{{ url('/api/v1') }}</code>

                    <h3 class="text-base font-bold text-ink-800">Available Endpoints</h3>
                    <div class="space-y-1 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-emerald-100 px-1.5 py-0.5 text-xs font-bold text-emerald-700">GET</span>
                            <code class="text-xs text-ink-600">/api/v1/customers</code>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-navy-100 px-1.5 py-0.5 text-xs font-bold text-navy-700">POST</span>
                            <code class="text-xs text-ink-600">/api/v1/customers</code>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-amber-100 px-1.5 py-0.5 text-xs font-bold text-amber-700">PUT</span>
                            <code class="text-xs text-ink-600">/api/v1/customers/{id}</code>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-red-100 px-1.5 py-0.5 text-xs font-bold text-red-700">DELETE</span>
                            <code class="text-xs text-ink-600">/api/v1/customers/{id}</code>
                        </div>
                    </div>
                    <p class="text-xs text-ink-400">Full API documentation available at <code class="rounded bg-ink-100 px-1.5 py-0.5">/api/docs</code> (Swagger/OpenAPI).</p>
                </div>

            </div>
        </div>
    </div>
</x-layouts.app>
