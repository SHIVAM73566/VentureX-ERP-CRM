<x-layouts.app
    :title="'Customer Support Center'"
    :breadcrumbs="[['label' => 'Support Center']]">

    <div class="mx-auto max-w-5xl space-y-6">

        {{-- Hero Section --}}
        <div class="rounded-2xl bg-gradient-to-br from-navy-600 to-navy-800 px-8 py-10 text-center text-white">
            <h2 class="text-3xl font-bold">How can we help you?</h2>
            <p class="mt-2 text-navy-200">Search our knowledge base or reach out to our support team.</p>

            {{-- Search Bar --}}
            <div x-data="{ query: '' }" class="mx-auto mt-6 max-w-xl">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="query" placeholder="Search for articles, guides, FAQs..." class="w-full rounded-xl border-0 bg-white/95 py-3.5 pl-12 pr-4 text-sm text-ink-700 placeholder-ink-400 shadow-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-white/50">
                </div>
            </div>

            <div class="mt-4 flex items-center justify-center gap-4">
                <a href="{{ route('support.tickets.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-navy-700 shadow transition hover:bg-navy-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Submit a Ticket
                </a>
                <a href="{{ route('support.report-error') }}" class="inline-flex items-center gap-2 rounded-lg border border-white/30 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    Report a Bug
                </a>
            </div>
        </div>

        {{-- Quick Links Grid --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('support.index') }}" class="card group flex flex-col items-center gap-3 py-6 text-center transition hover:shadow-md">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-navy-50 text-navy-600 group-hover:bg-navy-100">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-ink-800">Help Center</p>
                    <p class="mt-0.5 text-xs text-ink-400">Browse guides & articles</p>
                </div>
            </a>

            <a href="{{ route('support.docs') }}" class="card group flex flex-col items-center gap-3 py-6 text-center transition hover:shadow-md">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-ink-800">Documentation</p>
                    <p class="mt-0.5 text-xs text-ink-400">Technical docs & API</p>
                </div>
            </a>

            <a href="{{ route('support.faq') }}" class="card group flex flex-col items-center gap-3 py-6 text-center transition hover:shadow-md">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600 group-hover:bg-amber-100">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-ink-800">FAQ</p>
                    <p class="mt-0.5 text-xs text-ink-400">Frequently asked questions</p>
                </div>
            </a>

            <a href="{{ route('support.contact') }}" class="card group flex flex-col items-center gap-3 py-6 text-center transition hover:shadow-md">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-50 text-violet-600 group-hover:bg-violet-100">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-ink-800">Contact Support</p>
                    <p class="mt-0.5 text-xs text-ink-400">Talk to our team</p>
                </div>
            </a>
        </div>

        {{-- Recent Announcements --}}
        <div class="card">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-ink-900">Recent Announcements</h2>
                <span class="badge badge-blue">New</span>
            </div>
            <div class="divide-y divide-ink-100">
                <div class="py-3">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-2 w-2 shrink-0 rounded-full bg-navy-500"></span>
                        <div>
                            <p class="text-sm font-semibold text-ink-800">VentureX ERP & CRM v3.2 Released</p>
                            <p class="mt-0.5 text-xs text-ink-500">August 15, 2026</p>
                            <p class="mt-1 text-sm text-ink-600">Major update with improved AI capabilities, new procurement module, and performance enhancements across all modules.</p>
                        </div>
                    </div>
                </div>
                <div class="py-3">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <div>
                            <p class="text-sm font-semibold text-ink-800">Scheduled Maintenance — August 20</p>
                            <p class="mt-0.5 text-xs text-ink-500">August 12, 2026</p>
                            <p class="mt-1 text-sm text-ink-600">We will perform infrastructure upgrades from 02:00 to 06:00 UTC. The system may be briefly unavailable.</p>
                        </div>
                    </div>
                </div>
                <div class="py-3">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-2 w-2 shrink-0 rounded-full bg-amber-500"></span>
                        <div>
                            <p class="text-sm font-semibold text-ink-800">New Help Center Articles Published</p>
                            <p class="mt-0.5 text-xs text-ink-500">August 10, 2026</p>
                            <p class="mt-1 text-sm text-ink-600">We've added 15 new articles covering advanced CRM workflows, inventory management, and financial reporting.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Ticket Status --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <a href="{{ route('support.tickets.index') }}" class="card flex items-center gap-4 transition hover:shadow-md">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-navy-50 text-navy-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink-900">{{ $openTickets ?? 0 }}</p>
                    <p class="text-xs text-ink-500">Open tickets</p>
                </div>
            </a>

            <a href="{{ route('support.tickets.index') }}" class="card flex items-center gap-4 transition hover:shadow-md">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink-900">{{ $pendingReplies ?? 0 }}</p>
                    <p class="text-xs text-ink-500">Awaiting your reply</p>
                </div>
            </a>

            <a href="{{ route('support.notifications') }}" class="card flex items-center gap-4 transition hover:shadow-md">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-ink-900">{{ $unreadNotifications ?? 0 }}</p>
                    <p class="text-xs text-ink-500">Unread notifications</p>
                </div>
            </a>
        </div>
    </div>
</x-layouts.app>
