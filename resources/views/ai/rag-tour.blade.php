<x-layouts.app
    :title="'RAG Pipeline Tour'"
    :breadcrumbs="[['label' => 'AI Center'], ['label' => 'Enterprise RAG', 'url' => route('ai.rag')], ['label' => 'Pipeline Tour']]">

    @php
        $counts = collect($definitions)->countBy('domain');
        $domainQuestions = [
            'crm' => [
                'Which 10 customers are most likely to churn this quarter?',
                'Which open opportunities are stalled in negotiation?',
                'Which lead source produced the highest revenue this year?',
            ],
            'procurement' => [
                'Which supplier has the worst on-time delivery in the last 90 days?',
                'Where are we paying above benchmark for the same SKU?',
                'What should we replenish next month based on demand and lead time?',
            ],
            'inventory' => [
                'Which SKUs will stock out in the next 14 days?',
                'Which products are dead stock and how much cash is tied up?',
                'Which warehouse is closest to capacity?',
            ],
            'finance' => [
                'Which invoices are 30+ days overdue and who owns the follow-up?',
                'Project our cash position for the next 8 weeks.',
                'Are there duplicate payments to the same supplier?',
            ],
            'logistics' => [
                'Which containers risk demurrage this month?',
                'Which lane has the highest freight cost variance?',
                'Are customs documents complete for next week\'s shipments?',
            ],
            'executive' => [
                'Summarise this month\'s performance for the board.',
                'Which KPIs moved most against target, and why?',
                'What are the top five operational risks right now?',
            ],
        ];
    @endphp

    <div class="mx-auto max-w-6xl space-y-6">

        {{-- Hero --}}
        <section class="overflow-hidden rounded-2xl bg-gradient-to-br from-ink-900 via-navy-900 to-navy-800 px-6 py-10 text-white sm:px-10">
            <span class="badge bg-white/10 text-white">Enterprise AI tour · 12 minutes</span>
            <h1 class="mt-4 text-3xl font-bold sm:text-4xl">84 RAG pipelines, one business brain</h1>
            <p class="mt-3 max-w-3xl text-base text-navy-100 sm:text-lg">
                Retrieval-Augmented Generation (RAG) means the AI answers from <strong class="text-white">your records</strong>,
                not from generic internet knowledge. VentureX ships 84 focused pipelines across seven business domains -
                so a warehouse question searches warehouse data, and a finance question searches the ledger.
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('ai.rag') }}" class="btn bg-white text-navy-800 hover:bg-navy-50">Open the RAG workspace</a>
                <a href="{{ route('getting-started') }}" class="btn border border-white/30 text-white hover:bg-white/10">Product quick start</a>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-xl bg-white/10 px-4 py-3">
                    <p class="text-2xl font-bold">{{ $stats['pipelines'] }}</p>
                    <p class="text-xs text-navy-100">Registered pipelines</p>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-3">
                    <p class="text-2xl font-bold">{{ $stats['active_pipelines'] }}</p>
                    <p class="text-xs text-navy-100">Active right now</p>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-3">
                    <p class="text-2xl font-bold">{{ number_format($stats['documents']) }}</p>
                    <p class="text-xs text-navy-100">Indexed records</p>
                </div>
                <div class="rounded-xl bg-white/10 px-4 py-3">
                    <p class="text-2xl font-bold">{{ number_format($stats['avg_confidence'] * 100, 1) }}%</p>
                    <p class="text-xs text-navy-100">Average confidence</p>
                </div>
            </div>
        </section>

        {{-- How it works --}}
        <section class="card p-6">
            <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">What happens when someone asks a question</h2>
            <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Five steps, executed locally in milliseconds.</p>

            <div class="mt-5 grid gap-3 lg:grid-cols-5">
                @php
                    $flow = [
                        ['1', 'Your records', 'Customers, invoices, POs, stock movements, shipments, journals and audit logs.'],
                        ['2', 'Pipeline indexing', 'The chosen pipeline turns matching records into short, searchable documents.'],
                        ['3', 'Local embeddings', 'Each document becomes a 256-dimension vector stored as JSON — no external vector database.'],
                        ['4', 'Similarity search', 'The question is embedded the same way and compared using cosine similarity.'],
                        ['5', 'Grounded answer', 'Top matches become an answer with sources, confidence and latency. An LLM can polish the wording if configured.'],
                    ];
                @endphp
                @foreach ($flow as [$n, $title, $text])
                    <div class="rounded-xl border border-ink-200 p-4 dark:border-ink-700">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-navy-800 text-xs font-bold text-white">{{ $n }}</span>
                        <p class="mt-2 text-sm font-semibold text-ink-800 dark:text-ink-100">{{ $title }}</p>
                        <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">{{ $text }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 rounded-xl bg-ink-50 p-4 text-sm text-ink-600 dark:bg-ink-800 dark:text-ink-300">
                <strong class="text-ink-800 dark:text-ink-100">Why 84 pipelines instead of one chatbot?</strong>
                A single assistant has to guess which table matters. A focused pipeline already knows its domain, its source models and its
                keywords — so retrieval is more precise, answers are easier to audit, and a warehouse user never receives a finance answer
                built from the wrong records. The assistant still exists for general questions; the 84 pipelines are the specialists.
            </div>
        </section>

        {{-- Pipeline map --}}
        <section class="space-y-3">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">The complete pipeline map</h2>
                    <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Every pipeline, what it answers, and the example questions teams ask most.</p>
                </div>
                <span class="badge badge-blue">{{ count($definitions) }} pipelines · 6 domains</span>
            </div>

            @foreach ($domainLabels as $domainKey => $domainLabel)
                <details class="card overflow-hidden" {{ $loop->first ? 'open' : '' }}>
                    <summary class="flex cursor-pointer flex-wrap items-center gap-3 px-5 py-4">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-navy-50 text-sm font-bold text-navy-700 dark:bg-navy-900 dark:text-navy-200">{{ $counts[$domainKey] ?? 0 }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-ink-900 dark:text-ink-50">{{ $domainLabel }}</p>
                            <p class="text-xs text-ink-500 dark:text-ink-400">{{ $counts[$domainKey] ?? 0 }} focused pipelines</p>
                        </div>
                        <span class="text-xs font-semibold text-navy-600 dark:text-navy-300">Show pipelines</span>
                    </summary>

                    <div class="border-t border-ink-100 px-5 py-4 dark:border-ink-800">
                        <div class="mb-4 grid gap-2 sm:grid-cols-3">
                            @foreach ($domainQuestions[$domainKey] ?? [] as $question)
                                <div class="rounded-lg bg-ink-50 px-3 py-2 text-xs text-ink-600 dark:bg-ink-800 dark:text-ink-300">
                                    “{{ $question }}”
                                </div>
                            @endforeach
                        </div>

                        <div class="table-wrap">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Pipeline</th>
                                        <th>What it answers</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                                    @foreach ($definitions as $pipeline)
                                        @continue($pipeline['domain'] !== $domainKey)
                                        <tr>
                                            <td class="whitespace-nowrap align-top font-semibold text-ink-800 dark:text-ink-100">{{ $pipeline['name'] }}</td>
                                            <td class="text-ink-600 dark:text-ink-300">{{ $pipeline['description'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </details>
            @endforeach
        </section>

        {{-- Live enterprise day --}}
        <section class="card p-6">
            <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">A live enterprise day with RAG</h2>
            <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Six moments where a focused pipeline replaces a manual report.</p>

            <div class="mt-5 space-y-4">
                @php
                    $day = [
                        ['08:30', 'Owner', '“What changed since yesterday?”', 'Company Health Scorecard combines sales, cash, supply and support into one brief.', 'Decide what needs attention before the stand-up.'],
                        ['09:15', 'Sales', '“Which quotations are most likely to convert this week?”', 'Quote Conversion Probability ranks open quotes by history, stage and engagement.', 'Call the right customers first.'],
                        ['11:00', 'Procurement', '“Which supplier is slipping on delivery?”', 'Supplier Reliability and Dynamic Lead Time surface late POs and realistic ETAs.', 'Re-plan replenishment before production is affected.'],
                        ['14:00', 'Warehouse', '“What will stock out in the next 14 days?”', 'Stockout Risk Alerting combines cover, demand and lead time by warehouse.', 'Raise a transfer or PO today, not next week.'],
                        ['16:00', 'Finance', '“What cash is coming in over the next 8 weeks?”', 'Cash Flow Liquidity Projection uses open invoices, payment behaviour and payables.', 'Prioritise collections and time supplier payments.'],
                        ['18:00', 'Logistics', '“Any container at demurrage risk?”', 'Container Demurrage Risk reads shipment milestones, free days and port patterns.', 'Act before detention charges start.'],
                    ];
                @endphp
                @foreach ($day as [$time, $role, $question, $pipeline, $action])
                    <div class="flex flex-col gap-3 rounded-xl border border-ink-200 p-4 sm:flex-row dark:border-ink-700">
                        <div class="flex w-24 shrink-0 flex-col">
                            <span class="text-sm font-bold text-ink-900 dark:text-ink-50">{{ $time }}</span>
                            <span class="text-xs font-semibold uppercase tracking-wide text-navy-600 dark:text-navy-300">{{ $role }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">{{ $question }}</p>
                            <p class="mt-1 text-xs text-ink-500 dark:text-ink-400"><span class="font-semibold text-ink-600 dark:text-ink-300">Pipeline:</span> {{ $pipeline }}</p>
                        </div>
                        <div class="w-full shrink-0 rounded-lg bg-accent-50 px-3 py-2 text-xs text-accent-800 sm:w-64 dark:bg-accent-500/10 dark:text-accent-300">
                            <span class="font-semibold">Outcome:</span> {{ $action }}
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Benefits --}}
        <section class="card">
            <div class="card-header">
                <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">How every kind of company benefits</h2>
                <span class="badge badge-green">Business impact</span>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Company type</th>
                            <th>What changes with RAG</th>
                            <th>Business outcome</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100 dark:divide-ink-800">
                        @php
                            $benefits = [
                                ['Small business (5–50)', 'The owner asks one question instead of opening five screens.', 'No analyst needed; faster decisions.'],
                                ['Mid-market (50–500)', 'Each department gets consistent answers from the same data.', 'Fewer ad-hoc reports and less rework.'],
                                ['Wholesale distributor', 'Stock, supplier and margin questions answered together.', 'Less dead stock, fewer stockouts.'],
                                ['Manufacturer', 'Material forecasts, lead times and landed cost in one view.', 'Better production planning.'],
                                ['Importer / exporter', 'Duty, demurrage, HS codes and compliance screening.', 'Lower landed cost, fewer delays.'],
                                ['Services agency', 'Pipeline, conversion and receivables insight.', 'Healthier cash flow.'],
                                ['Finance & audit', 'Anomaly detection across invoices, payments and journals.', 'Earlier fraud and error detection.'],
                                ['Group / enterprise', 'Company-scoped answers and executive scorecards.', 'Governance without spreadsheet sprawl.'],
                            ];
                        @endphp
                        @foreach ($benefits as [$type, $change, $outcome])
                            <tr>
                                <td class="font-semibold text-ink-800 dark:text-ink-100">{{ $type }}</td>
                                <td class="text-ink-600 dark:text-ink-300">{{ $change }}</td>
                                <td class="text-ink-600 dark:text-ink-300">{{ $outcome }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- How to use --}}
        <section class="grid gap-4 lg:grid-cols-2">
            <div class="card p-6">
                <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Use it in four steps</h2>
                <ol class="mt-3 space-y-3 text-sm text-ink-600 dark:text-ink-300">
                    <li><span class="font-semibold text-ink-800 dark:text-ink-100">1. Index.</span> Open the RAG workspace and choose <em>Index all</em>, or index one pipeline. Nightly self-training keeps it fresh.</li>
                    <li><span class="font-semibold text-ink-800 dark:text-ink-100">2. Pick a pipeline.</span> Choose the domain closest to your question - CRM, procurement, inventory, finance, logistics, executive or HR.</li>
                    <li><span class="font-semibold text-ink-800 dark:text-ink-100">3. Ask specifically.</span> Mention the entity, period and metric: “overdue invoices for Bluewave Retail in October”.</li>
                    <li><span class="font-semibold text-ink-800 dark:text-ink-100">4. Act with sources.</span> Read the answer, check the sources and confidence, then make the change in the module.</li>
                </ol>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('ai.rag') }}" class="btn-primary">Open RAG workspace</a>
                    <a href="{{ route('ai.assistant') }}" class="btn-secondary">General assistant</a>
                </div>
            </div>

            <div class="card p-6">
                <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">For developers and integrators</h2>
                <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">The same pipelines are available over the REST API with the caller's permissions.</p>
                <pre class="mt-3 overflow-x-auto rounded-xl bg-ink-900 p-4 text-xs text-ink-100"><code>GET  /api/rag/pipelines
GET  /api/rag/stats
POST /api/rag/query
POST /api/rag/sync

Authorization: Bearer &lt;token&gt;

{
  "pipeline": "receivables_aging_insight",
  "question": "Which invoices are more than 30 days overdue?",
  "limit": 8
}</code></pre>
                <p class="mt-3 text-xs text-ink-500 dark:text-ink-400">
                    CLI: <code class="rounded bg-ink-100 px-1.5 py-0.5 dark:bg-ink-800">php artisan rag:sync --train</code>
                    indexes every pipeline and runs the training pass.
                </p>
            </div>
        </section>

        {{-- Trust --}}
        <section class="card p-6">
            <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Trust, security and explainability</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @php
                    $trust = [
                        ['Company-scoped', 'Documents and query logs carry a company id; users only retrieve their own company\'s data.'],
                        ['Permission-aware', 'RAG uses the same AI policy as the rest of the product (ai.view / ai.create).'],
                        ['Local-first', 'Embeddings and similarity run on your server; no external vector database is required.'],
                        ['Explainable', 'Every answer returns its sources, confidence and latency, plus a local/AI mode.'],
                        ['Auditable', 'Indexing and querying are written to audit logs; failures go through self-healing.'],
                        ['Self-improving', 'A nightly job analyses query and transaction patterns and adjusts weights and confidence.'],
                    ];
                @endphp
                @foreach ($trust as [$title, $text])
                    <div class="rounded-xl border border-ink-200 p-4 dark:border-ink-700">
                        <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">{{ $title }}</p>
                        <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
            <p class="mt-4 text-xs text-ink-500 dark:text-ink-400">
                Answers are decision support, not autonomous actions. A human still approves purchases, payments and exports.
            </p>
        </section>

        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-navy-200 bg-navy-50 px-6 py-5 dark:border-navy-800 dark:bg-navy-900/40">
            <div>
                <p class="text-sm font-bold text-navy-900 dark:text-navy-100">Ready to ask your first question?</p>
                <p class="text-xs text-navy-700 dark:text-navy-300">Start with the Receivables Aging or Stockout Risk pipeline — they usually produce an immediate win.</p>
            </div>
            <a href="{{ route('ai.rag') }}" class="btn-primary">Open Enterprise RAG</a>
        </div>
    </div>
</x-layouts.app>
