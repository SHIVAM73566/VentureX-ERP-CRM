@auth
    @php
        $guideDefinitions = \App\Services\Rag\RagPipelineRegistry::definitions();
        $guideDomainLabels = \App\Services\Rag\RagPipelineRegistry::domainLabels();
        $guideContext = \App\Services\Rag\RagPageGuide::context(
            request()->route()?->getName(),
            request()->path()
        );
        $guideCounts = collect($guideDefinitions)->countBy('domain');
    @endphp

    {{--
        Global product guide.

        Rendered by the authenticated layout, so the introduction, the full
        80-pipeline catalogue and page-aware suggestions are available on every
        page of VentureX ERP & CRM without a round trip.
    --}}
    <div x-data="{ open: false, tab: 'intro', query: '', domain: 'all', copied: null }"
         @keydown.escape.window="open = false"
         class="print:hidden">

        {{-- Floating launcher --}}
        <button type="button"
                @click="open = true"
                class="fixed bottom-5 right-5 z-40 inline-flex items-center gap-2 rounded-full bg-navy-900 px-4 py-3 text-sm font-semibold text-white shadow-xl ring-1 ring-white/20 transition hover:bg-navy-800 focus:outline-none focus:ring-2 focus:ring-accent-400 dark:bg-accent-600 dark:hover:bg-accent-500"
                aria-haspopup="dialog"
                aria-controls="product-guide-panel"
                aria-label="Open the VentureX product guide">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span>Product guide</span>
            <span class="rounded-full bg-white/15 px-2 py-0.5 text-[11px] font-bold tracking-wide">80+ RAG</span>
        </button>

        {{-- Slide-over --}}
        <div x-show="open" x-cloak class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-labelledby="product-guide-title">
            <div x-show="open" x-transition.opacity @click="open = false" class="absolute inset-0 bg-ink-950/50" aria-hidden="true"></div>

            <aside id="product-guide-panel"
                   x-show="open"
                   x-transition:enter="transition ease-out duration-200"
                   x-transition:enter-start="translate-x-full"
                   x-transition:enter-end="translate-x-0"
                   x-transition:leave="transition ease-in duration-150"
                   x-transition:leave-start="translate-x-0"
                   x-transition:leave-end="translate-x-full"
                   class="absolute inset-y-0 right-0 flex w-full max-w-2xl flex-col bg-white shadow-2xl dark:bg-ink-900">

                {{-- Header --}}
                <div class="flex items-start justify-between gap-4 border-b border-ink-200 px-5 py-4 dark:border-ink-800">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-accent-600 dark:text-accent-400">VentureX ERP &amp; CRM</p>
                        <h2 id="product-guide-title" class="mt-0.5 text-lg font-bold text-ink-900 dark:text-ink-50">Your product guide</h2>
                        <p class="mt-0.5 text-xs text-ink-500 dark:text-ink-400">Introduction, all {{ count($guideDefinitions) }} RAG pipelines, and what this page can answer.</p>
                    </div>
                    <button type="button" @click="open = false"
                            class="rounded-lg p-2 text-ink-400 transition hover:bg-ink-100 hover:text-ink-700 focus:outline-none focus:ring-2 focus:ring-navy-500 dark:hover:bg-ink-800 dark:hover:text-ink-200"
                            aria-label="Close the product guide">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Tabs --}}
                <div class="flex shrink-0 gap-1 overflow-x-auto border-b border-ink-200 px-3 dark:border-ink-800" role="tablist" aria-label="Product guide sections">
                    <button type="button" role="tab" @click="tab = 'intro'" :aria-selected="tab === 'intro'"
                            :class="tab === 'intro' ? 'border-accent-600 text-accent-700 dark:text-accent-400' : 'border-transparent text-ink-500 hover:text-ink-700 dark:text-ink-400 dark:hover:text-ink-200'"
                            class="shrink-0 border-b-2 px-3 py-2.5 text-sm font-semibold transition">Introduction</button>
                    <button type="button" role="tab" @click="tab = 'pipelines'" :aria-selected="tab === 'pipelines'"
                            :class="tab === 'pipelines' ? 'border-accent-600 text-accent-700 dark:text-accent-400' : 'border-transparent text-ink-500 hover:text-ink-700 dark:text-ink-400 dark:hover:text-ink-200'"
                            class="shrink-0 border-b-2 px-3 py-2.5 text-sm font-semibold transition">All {{ count($guideDefinitions) }} RAG pipelines</button>
                    <button type="button" role="tab" @click="tab = 'page'" :aria-selected="tab === 'page'"
                            :class="tab === 'page' ? 'border-accent-600 text-accent-700 dark:text-accent-400' : 'border-transparent text-ink-500 hover:text-ink-700 dark:text-ink-400 dark:hover:text-ink-200'"
                            class="shrink-0 border-b-2 px-3 py-2.5 text-sm font-semibold transition">This page</button>
                </div>

                <div class="flex-1 overflow-y-auto overscroll-contain px-5 py-5">

                    {{-- ============================ INTRODUCTION ============================ --}}
                    <section x-show="tab === 'intro'" class="space-y-5">
                        <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-navy-900 via-navy-800 to-ink-900 px-5 py-6 text-white">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide">Universal business operating system</span>
                            <h3 class="mt-3 text-2xl font-bold leading-tight">One login. Every department. 80 AI pipelines.</h3>
                            <p class="mt-2 text-sm leading-relaxed text-navy-100">
                                VentureX ERP &amp; CRM connects customers, sales, procurement, inventory, finance,
                                logistics, support and administration in one system. Its {{ count($guideDefinitions) }}
                                Retrieval-Augmented Generation (RAG) pipelines answer questions from
                                <strong class="text-white">your own records</strong> — not from generic internet knowledge.
                            </p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <a href="{{ route('getting-started') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-xs font-semibold text-navy-900 transition hover:bg-navy-50">Start the quick tour</a>
                                <a href="{{ route('ai.rag') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-white/30 px-3 py-2 text-xs font-semibold text-white transition hover:bg-white/10">Open RAG workspace</a>
                                <a href="{{ route('ai.rag.tour') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-white/30 px-3 py-2 text-xs font-semibold text-white transition hover:bg-white/10">See all 80 pipelines</a>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wide text-ink-500 dark:text-ink-400">Start in 60 seconds</h3>
                            <ol class="mt-3 space-y-3">
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-accent-600 text-xs font-bold text-white">1</span>
                                    <div>
                                        <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">Create your first customer or supplier</p>
                                        <p class="text-xs text-ink-500 dark:text-ink-400">Every downstream document — quote, order, invoice, shipment — starts from a party. <a href="{{ route('customers.create') }}" class="font-medium text-accent-700 hover:underline dark:text-accent-400">Add a customer</a>.</p>
                                    </div>
                                </li>
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-accent-600 text-xs font-bold text-white">2</span>
                                    <div>
                                        <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">Add products and opening stock</p>
                                        <p class="text-xs text-ink-500 dark:text-ink-400">Products drive quotations, orders, purchase lines and stock. <a href="{{ route('inventory.products.create') }}" class="font-medium text-accent-700 hover:underline dark:text-accent-400">Add a product</a>.</p>
                                    </div>
                                </li>
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-accent-600 text-xs font-bold text-white">3</span>
                                    <div>
                                        <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">Run one sales cycle end to end</p>
                                        <p class="text-xs text-ink-500 dark:text-ink-400">Quotation → sales order → invoice → payment. Each step posts the right stock and ledger entries automatically.</p>
                                    </div>
                                </li>
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-accent-600 text-xs font-bold text-white">4</span>
                                    <div>
                                        <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">Ask a question in plain English</p>
                                        <p class="text-xs text-ink-500 dark:text-ink-400">Open the RAG workspace, pick a pipeline and ask “Which SKUs will stock out in 14 days?” Answers include sources and confidence.</p>
                                    </div>
                                </li>
                            </ol>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wide text-ink-500 dark:text-ink-400">How companies use VentureX</h3>
                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-ink-200 p-3 dark:border-ink-800">
                                    <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">Distributors &amp; wholesalers</p>
                                    <p class="mt-1 text-xs leading-relaxed text-ink-500 dark:text-ink-400">Quote from live stock, reorder before stockouts, and track supplier lead times and landed cost per container.</p>
                                </div>
                                <div class="rounded-xl border border-ink-200 p-3 dark:border-ink-800">
                                    <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">Manufacturers</p>
                                    <p class="mt-1 text-xs leading-relaxed text-ink-500 dark:text-ink-400">Forecast material requirements, compare RFQ responses and monitor on-time, in-full supplier delivery.</p>
                                </div>
                                <div class="rounded-xl border border-ink-200 p-3 dark:border-ink-800">
                                    <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">Professional services</p>
                                    <p class="mt-1 text-xs leading-relaxed text-ink-500 dark:text-ink-400">Manage leads, opportunities and pipelines, then connect delivery to invoices, receivables and account health.</p>
                                </div>
                                <div class="rounded-xl border border-ink-200 p-3 dark:border-ink-800">
                                    <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">Multi-company groups</p>
                                    <p class="mt-1 text-xs leading-relaxed text-ink-500 dark:text-ink-400">Keep each company's data scoped and auditable, while executives compare consolidated KPIs and risk.</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wide text-ink-500 dark:text-ink-400">The easy process</h3>
                            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs font-semibold">
                                <span class="rounded-full bg-navy-100 px-3 py-1.5 text-navy-800 dark:bg-navy-900 dark:text-navy-200">Capture</span>
                                <span class="text-ink-400">→</span>
                                <span class="rounded-full bg-navy-100 px-3 py-1.5 text-navy-800 dark:bg-navy-900 dark:text-navy-200">Qualify</span>
                                <span class="text-ink-400">→</span>
                                <span class="rounded-full bg-navy-100 px-3 py-1.5 text-navy-800 dark:bg-navy-900 dark:text-navy-200">Fulfil</span>
                                <span class="text-ink-400">→</span>
                                <span class="rounded-full bg-navy-100 px-3 py-1.5 text-navy-800 dark:bg-navy-900 dark:text-navy-200">Invoice</span>
                                <span class="text-ink-400">→</span>
                                <span class="rounded-full bg-navy-100 px-3 py-1.5 text-navy-800 dark:bg-navy-900 dark:text-navy-200">Analyse</span>
                            </div>
                            <p class="mt-2 text-xs leading-relaxed text-ink-500 dark:text-ink-400">
                                Every stage writes to the same dataset, so the AI, dashboards and audit trail always agree.
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wide text-ink-500 dark:text-ink-400">Features that work together</h3>
                            <ul class="mt-3 space-y-2 text-xs leading-relaxed text-ink-600 dark:text-ink-300">
                                <li class="flex gap-2"><span class="text-accent-600">•</span><span><strong>Quote-to-cash:</strong> quotation → sales order → invoice → payment, with stock and journals posted automatically.</span></li>
                                <li class="flex gap-2"><span class="text-accent-600">•</span><span><strong>Source-to-pay:</strong> requisition → RFQ → supplier offer → purchase order → shipment → landed cost.</span></li>
                                <li class="flex gap-2"><span class="text-accent-600">•</span><span><strong>Inventory intelligence:</strong> stock movements, warehouse capacity, stockout risk and replenishment planning.</span></li>
                                <li class="flex gap-2"><span class="text-accent-600">•</span><span><strong>Finance controls:</strong> chart of accounts, journals, payables, receivables and duplicate-payment detection.</span></li>
                                <li class="flex gap-2"><span class="text-accent-600">•</span><span><strong>Logistics &amp; customs:</strong> shipments, containers, demurrage risk and document completeness.</span></li>
                                <li class="flex gap-2"><span class="text-accent-600">•</span><span><strong>AI everywhere:</strong> assistant, copilot, document reader, procurement AI, support AI and the 80 RAG pipelines.</span></li>
                                <li class="flex gap-2"><span class="text-accent-600">•</span><span><strong>Governance:</strong> roles, audit logs, approvals, company scoping, quotas and error tracking.</span></li>
                            </ul>
                        </div>

                        <div class="rounded-xl border border-accent-200 bg-accent-50 p-4 dark:border-accent-900/40 dark:bg-accent-900/10">
                            <p class="text-sm font-semibold text-accent-800 dark:text-accent-300">This guide follows you everywhere</p>
                            <p class="mt-1 text-xs leading-relaxed text-accent-700 dark:text-accent-400">
                                The same launcher appears on every page. The <strong>This page</strong> tab reads the current
                                screen and suggests the RAG pipelines and questions that matter there.
                            </p>
                        </div>
                    </section>

                    {{-- ============================ ALL PIPELINES ============================ --}}
                    <section x-show="tab === 'pipelines'" class="space-y-4">
                        <div>
                            <h3 class="text-base font-bold text-ink-900 dark:text-ink-50">All {{ count($guideDefinitions) }} RAG pipelines</h3>
                            <p class="mt-1 text-xs leading-relaxed text-ink-500 dark:text-ink-400">
                                Six domains, one searchable catalogue. Search by name, question or keyword, or filter to a
                                domain. Every pipeline is company-scoped and returns its sources, confidence and latency.
                            </p>
                        </div>

                        <div>
                            <label for="guide-pipeline-search" class="sr-only">Search RAG pipelines</label>
                            <input id="guide-pipeline-search" type="search" x-model="query" class="input" placeholder="Search 80 pipelines — churn, landed cost, demurrage, duplicate payment…">
                        </div>

                        <div class="flex flex-wrap gap-1.5" role="group" aria-label="Filter pipelines by domain">
                            <button type="button" @click="domain = 'all'"
                                    :class="domain === 'all' ? 'bg-navy-900 text-white dark:bg-accent-600' : 'bg-ink-100 text-ink-600 hover:bg-ink-200 dark:bg-ink-800 dark:text-ink-300 dark:hover:bg-ink-700'"
                                    class="rounded-full px-2.5 py-1 text-[11px] font-semibold transition">All ({{ count($guideDefinitions) }})</button>
                            @foreach ($guideDomainLabels as $domainKey => $domainLabel)
                                <button type="button" @click="domain = @js($domainKey)"
                                        :class="domain === @js($domainKey) ? 'bg-navy-900 text-white dark:bg-accent-600' : 'bg-ink-100 text-ink-600 hover:bg-ink-200 dark:bg-ink-800 dark:text-ink-300 dark:hover:bg-ink-700'"
                                        class="rounded-full px-2.5 py-1 text-[11px] font-semibold transition">{{ $domainLabel }} ({{ $guideCounts[$domainKey] ?? 0 }})</button>
                            @endforeach
                        </div>

                        @foreach ($guideDomainLabels as $domainKey => $domainLabel)
                            <div class="space-y-2">
                                <div class="flex items-center justify-between gap-2 border-b border-ink-200 pb-1.5 dark:border-ink-800">
                                    <h4 class="text-xs font-bold uppercase tracking-wide text-ink-500 dark:text-ink-400">{{ $domainLabel }}</h4>
                                    <span class="text-[11px] font-semibold text-ink-400 dark:text-ink-500">{{ $guideCounts[$domainKey] ?? 0 }} pipelines</span>
                                </div>

                                @foreach ($guideDefinitions as $pipeline)
                                    @continue($pipeline['domain'] !== $domainKey)
                                    @php
                                        $haystack = mb_strtolower(
                                            $pipeline['name'].' '.$pipeline['description'].' '.$pipeline['key'].' '.$domainLabel
                                        );
                                    @endphp
                                    <div class="rounded-lg border border-ink-200 px-3 py-2.5 dark:border-ink-800"
                                         x-show="(domain === 'all' || domain === @js($domainKey)) && (query.trim() === '' || @js($haystack).includes(query.trim().toLowerCase()))">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">{{ $pipeline['name'] }}</p>
                                                <p class="mt-0.5 text-xs leading-relaxed text-ink-500 dark:text-ink-400">{{ $pipeline['description'] }}</p>
                                            </div>
                                            <span class="shrink-0 rounded-full bg-ink-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-ink-500 dark:bg-ink-800 dark:text-ink-400">{{ $pipeline['retrieval_strategy'] }}</span>
                                        </div>
                                        <p class="mt-1.5 font-mono text-[10px] text-ink-400 dark:text-ink-500">{{ $pipeline['key'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                        <div class="rounded-xl border border-ink-200 p-3 dark:border-ink-800">
                            <p class="text-xs leading-relaxed text-ink-500 dark:text-ink-400">
                                Need the full tour? It explains how retrieval works, a live enterprise day, benefits by
                                company type, governance and the API.
                            </p>
                            <a href="{{ route('ai.rag.tour') }}" class="mt-2 inline-flex text-xs font-semibold text-accent-700 hover:underline dark:text-accent-400">Open the 80-pipeline tour →</a>
                        </div>
                    </section>

                    {{-- ============================ THIS PAGE ============================ --}}
                    <section x-show="tab === 'page'" class="space-y-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-widest text-ink-400 dark:text-ink-500">You are on</p>
                            <h3 class="mt-0.5 text-xl font-bold text-ink-900 dark:text-ink-50">{{ $guideContext['page'] }}</h3>
                            <p class="mt-1 text-xs leading-relaxed text-ink-500 dark:text-ink-400">
                                The guide matched this screen to the RAG domains below. The questions are the ones this
                                page usually raises — ask them in the workspace and the answer is grounded in your company's records.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($guideContext['domain_labels'] as $domainKey => $domainLabel)
                                <a href="{{ route('ai.rag', ['domain' => $domainKey]) }}"
                                   class="rounded-full bg-accent-50 px-2.5 py-1 text-[11px] font-semibold text-accent-700 transition hover:bg-accent-100 dark:bg-accent-900/20 dark:text-accent-300 dark:hover:bg-accent-900/30">
                                    {{ $domainLabel }}
                                </a>
                            @endforeach
                        </div>

                        <div class="space-y-2">
                            <h4 class="text-xs font-bold uppercase tracking-wide text-ink-500 dark:text-ink-400">Ask on this page</h4>
                            @foreach ($guideContext['suggestions'] as $question)
                                <div class="flex items-start justify-between gap-3 rounded-lg border border-ink-200 px-3 py-2.5 dark:border-ink-800">
                                    <p class="text-sm leading-relaxed text-ink-700 dark:text-ink-200">{{ $question }}</p>
                                    <button type="button"
                                            @click="navigator.clipboard.writeText(@js($question)); copied = @js($question); setTimeout(() => copied = null, 2000)"
                                            class="shrink-0 rounded-md border border-ink-300 px-2 py-1 text-[11px] font-semibold text-ink-600 transition hover:bg-ink-50 dark:border-ink-700 dark:text-ink-300 dark:hover:bg-ink-800">
                                        <span x-show="copied !== @js($question)">Copy</span>
                                        <span x-show="copied === @js($question)" class="text-emerald-600 dark:text-emerald-400">Copied</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <div class="rounded-xl border border-ink-200 p-3 dark:border-ink-800">
                            <p class="text-xs leading-relaxed text-ink-500 dark:text-ink-400">
                                A pipeline searches only the records it owns — for example, the receivables pipeline reads
                                invoices and payments, never unrelated HR or admin data.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('ai.rag', ['domain' => $guideContext['domains'][0] ?? 'crm']) }}" class="btn-primary text-xs">Ask in the RAG workspace</a>
                            <a href="{{ route('ai.rag.tour') }}" class="btn-secondary text-xs">How the 80 pipelines work</a>
                            <a href="{{ route('getting-started') }}" class="btn-secondary text-xs">Getting Started</a>
                        </div>
                    </section>
                </div>

                <div class="shrink-0 border-t border-ink-200 px-5 py-3 text-[11px] text-ink-400 dark:border-ink-800 dark:text-ink-500">
                    Available on every page · {{ count($guideDefinitions) }} company-scoped pipelines · answers include sources, confidence and latency
                </div>
            </aside>
        </div>
    </div>
@endauth
